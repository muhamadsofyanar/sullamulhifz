<?php

namespace App\Services;

use App\Models\QuranAyah;
use App\Models\QuranSurah;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class QuranCorpusSyncService
{
    public const SURAH_COUNT = 114;
    public const AYAH_COUNT = 6236;
    public const JUZ_COUNT = 30;
    public const PAGE_COUNT = 604;
    public const HIZB_QUARTER_COUNT = 240;

    public const SOURCE_URL = 'https://api.alquran.cloud/v1/quran/quran-uthmani';

    public function isComplete(): bool
    {
        if (! DB::getSchemaBuilder()->hasTable('quran_ayahs')) {
            return false;
        }

        return QuranSurah::query()->count() >= self::SURAH_COUNT
            && QuranAyah::query()->count() >= self::AYAH_COUNT
            && QuranAyah::query()->whereNotNull('juz_number')->distinct()->count('juz_number') >= self::JUZ_COUNT
            && QuranAyah::query()->whereNotNull('page_number')->distinct()->count('page_number') >= self::PAGE_COUNT
            && QuranAyah::query()->whereNotNull('hizb_quarter')->distinct()->count('hizb_quarter') >= self::HIZB_QUARTER_COUNT;
    }

    /** @return array<string, int|string|bool> */
    public function sync(bool $force = false): array
    {
        if (! $force && $this->isComplete()) {
            return $this->status() + ['changed' => false];
        }

        $response = $this->fetch();
        $payload = $response->json();
        $surahs = data_get($payload, 'data.surahs');

        if (! is_array($surahs) || count($surahs) !== self::SURAH_COUNT) {
            throw new RuntimeException('Sumber korpus Al-Qur’an tidak mengembalikan 114 surah. Sinkronisasi dibatalkan agar data tidak parsial.');
        }

        $surahRows = [];
        $ayahRows = [];
        $globalNumbers = [];

        foreach ($surahs as $surah) {
            if (! is_array($surah)) {
                continue;
            }

            $surahId = (int) ($surah['number'] ?? 0);
            $ayahs = $surah['ayahs'] ?? [];
            if ($surahId < 1 || $surahId > 114 || ! is_array($ayahs)) {
                continue;
            }

            $surahRows[] = [
                'id' => $surahId,
                'name_arabic' => trim((string) ($surah['name'] ?? '')),
                'name_latin' => trim((string) ($surah['englishName'] ?? ('Surah '.$surahId))),
                'revelation_place' => $this->revelationPlace((string) ($surah['revelationType'] ?? '')),
                'verse_count' => count($ayahs),
                'sequence' => $surahId,
            ];

            foreach ($ayahs as $ayah) {
                if (! is_array($ayah)) {
                    continue;
                }
                $global = (int) ($ayah['number'] ?? 0);
                $verse = (int) ($ayah['numberInSurah'] ?? 0);
                if ($global < 1 || $verse < 1) {
                    continue;
                }

                $globalNumbers[$global] = true;
                $ayahRows[] = [
                    'global_number' => $global,
                    'surah_id' => $surahId,
                    'verse_number' => $verse,
                    'arabic_text' => (string) ($ayah['text'] ?? ''),
                    'juz_number' => $this->nullablePositiveInt($ayah['juz'] ?? null),
                    'hizb_quarter' => $this->nullablePositiveInt($ayah['hizbQuarter'] ?? null),
                    'page_number' => $this->nullablePositiveInt($ayah['page'] ?? null),
                    'ruku_number' => $this->nullablePositiveInt($ayah['ruku'] ?? null),
                    'manzil_number' => $this->nullablePositiveInt($ayah['manzil'] ?? null),
                    'sajda' => $this->sajda($ayah['sajda'] ?? false),
                    'metadata' => json_encode([
                        'source' => 'AlQuran.cloud',
                        'edition' => data_get($payload, 'data.edition.identifier', 'quran-uthmani'),
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (count($surahRows) !== self::SURAH_COUNT || count($ayahRows) !== self::AYAH_COUNT || count($globalNumbers) !== self::AYAH_COUNT) {
            throw new RuntimeException('Korpus yang diterima tidak lengkap: dibutuhkan 114 surah dan 6.236 ayat unik. Tidak ada data yang diubah.');
        }

        DB::transaction(function () use ($surahRows, $ayahRows): void {
            DB::table('quran_surahs')->upsert(
                $surahRows,
                ['id'],
                ['name_arabic', 'name_latin', 'revelation_place', 'verse_count', 'sequence']
            );

            foreach (array_chunk($ayahRows, 500) as $chunk) {
                DB::table('quran_ayahs')->upsert(
                    $chunk,
                    ['global_number'],
                    [
                        'surah_id', 'verse_number', 'arabic_text', 'juz_number', 'hizb_quarter',
                        'page_number', 'ruku_number', 'manzil_number', 'sajda', 'metadata', 'updated_at',
                    ]
                );
            }
        });

        return $this->status() + ['changed' => true];
    }

    /** @return array<string, int|string|bool> */
    public function status(): array
    {
        $ayahCount = DB::getSchemaBuilder()->hasTable('quran_ayahs') ? QuranAyah::query()->count() : 0;
        $surahCount = QuranSurah::query()->count();
        $juzCount = DB::getSchemaBuilder()->hasTable('quran_ayahs')
            ? QuranAyah::query()->whereNotNull('juz_number')->distinct()->count('juz_number') : 0;
        $pageCount = DB::getSchemaBuilder()->hasTable('quran_ayahs')
            ? QuranAyah::query()->whereNotNull('page_number')->distinct()->count('page_number') : 0;
        $rubuCount = DB::getSchemaBuilder()->hasTable('quran_ayahs')
            ? QuranAyah::query()->whereNotNull('hizb_quarter')->distinct()->count('hizb_quarter') : 0;

        return [
            'surahs' => $surahCount,
            'ayahs' => $ayahCount,
            'juz' => $juzCount,
            'pages' => $pageCount,
            'rubus' => $rubuCount,
            'complete' => $surahCount >= self::SURAH_COUNT
                && $ayahCount >= self::AYAH_COUNT
                && $juzCount >= self::JUZ_COUNT
                && $pageCount >= self::PAGE_COUNT
                && $rubuCount >= self::HIZB_QUARTER_COUNT,
            'source' => 'AlQuran.cloud · Uthmani',
        ];
    }

    private function fetch(): Response
    {
        try {
            $response = Http::acceptJson()
                ->connectTimeout(10)
                ->timeout(90)
                ->retry(3, 1200, throw: false)
                ->get(self::SOURCE_URL);
        } catch (Throwable $exception) {
            throw new RuntimeException('Tidak dapat terhubung ke sumber korpus Al-Qur’an: '.$exception->getMessage(), 0, $exception);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Sumber korpus Al-Qur’an merespons HTTP '.$response->status().'.');
        }

        return $response;
    }

    private function nullablePositiveInt(mixed $value): ?int
    {
        $number = (int) $value;
        return $number > 0 ? $number : null;
    }

    private function revelationPlace(string $value): ?string
    {
        return match (strtolower(trim($value))) {
            'meccan', 'makkiyah', 'makki' => 'Makkiyah',
            'medinan', 'madaniyah', 'madani' => 'Madaniyah',
            default => null,
        };
    }

    private function sajda(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_array($value)) {
            return (bool) ($value['recommended'] ?? $value['obligatory'] ?? true);
        }
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
