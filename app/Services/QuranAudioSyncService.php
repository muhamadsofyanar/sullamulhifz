<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\QuranAudioSource;
use App\Models\QuranAyah;
use App\Models\QuranAyahTiming;
use App\Models\QuranPracticePreset;
use App\Models\QuranRubu;
use App\Models\QuranSurah;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class QuranAudioSyncService
{
    public const FULL_QURAN_SURAH_START = 1;
    public const FULL_QURAN_SURAH_END = 114;
    public const FULL_QURAN_AYAH_COUNT = 6236;

    // Alias lama dipertahankan agar kode upgrade dari v1.6.x tidak patah.
    public const JUZ_30_SURAH_START = 78;
    public const JUZ_30_SURAH_END = 114;
    public const JUZ_30_AYAH_COUNT = 564;

    /** @return array<int, array<string, mixed>> */
    public function sourceDefinitions(): array
    {
        return [
            [
                'external_id' => '118',
                'name' => 'Murattal Mahmoud Khalil Al-Husary',
                'reciter_name' => 'Mahmoud Khalil Al-Husary',
                'rewaya' => 'Hafs dari Asim — Murattal',
                'base_url' => 'https://server13.mp3quran.net/husr/',
                'is_default' => true,
                'metadata' => [
                    'timing_endpoint' => 'https://mp3quran.net/api/v3/ayat_timing',
                    'source_attribution' => 'MP3Quran.net',
                    'sync_scope' => 'Al-Qur’an lengkap · 30 juz',
                    'learning_role' => 'Standar utama tahfizh',
                    'description' => 'Bacaan presisi, tempo terukur, dan tajwid kuat untuk talaqqi serta penguatan hafalan.',
                ],
            ],
            [
                'external_id' => '112',
                'name' => 'Murattal Muhammad Siddiq Al-Minshawi',
                'reciter_name' => 'Muhammad Siddiq Al-Minshawi',
                'rewaya' => 'Hafs dari Asim — Murattal',
                'base_url' => 'https://server10.mp3quran.net/minsh/',
                'is_default' => false,
                'metadata' => [
                    'timing_endpoint' => 'https://mp3quran.net/api/v3/ayat_timing',
                    'source_attribution' => 'MP3Quran.net',
                    'sync_scope' => 'Al-Qur’an lengkap · 30 juz',
                    'learning_role' => 'Pilihan murāja‘ah dan tadabbur',
                    'description' => 'Tempo tenang dan bacaan menyentuh untuk murāja‘ah, menyimak, dan tadabbur.',
                ],
            ],
        ];
    }

    public function syncInstitution(Institution $institution): array
    {
        $surahs = QuranSurah::query()
            ->whereBetween('id', [self::FULL_QURAN_SURAH_START, self::FULL_QURAN_SURAH_END])
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        if ($surahs->count() !== 114 || (int) $surahs->sum('verse_count') !== self::FULL_QURAN_AYAH_COUNT) {
            throw new RuntimeException('Master Al-Qur’an belum lengkap: dibutuhkan 114 surah dan 6.236 ayat. Jalankan sinkronisasi korpus terlebih dahulu.');
        }

        QuranAudioSource::query()
            ->where('institution_id', $institution->id)
            ->where('provider', 'mp3quran')
            ->update(['is_default' => false]);

        QuranAudioSource::query()
            ->where('institution_id', $institution->id)
            ->where('provider', 'mp3quran')
            ->where('external_id', '5')
            ->update(['is_default' => false, 'status' => 'inactive']);

        $sourceResults = [];
        $defaultSource = null;

        foreach ($this->sourceDefinitions() as $definition) {
            $source = QuranAudioSource::query()->updateOrCreate(
                [
                    'institution_id' => $institution->id,
                    'provider' => 'mp3quran',
                    'external_id' => $definition['external_id'],
                ],
                [
                    'name' => $definition['name'],
                    'reciter_name' => $definition['reciter_name'],
                    'rewaya' => $definition['rewaya'],
                    'base_url' => $definition['base_url'],
                    'metadata' => $definition['metadata'],
                    'is_default' => $definition['is_default'],
                    'status' => 'active',
                ]
            );

            if ($definition['is_default']) {
                $defaultSource = $source;
            }

            $sourceResults[] = $this->syncSource($source, $surahs);
        }

        $defaultSource ??= QuranAudioSource::query()
            ->where('institution_id', $institution->id)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->firstOrFail();

        $this->seedPresets($institution, $defaultSource);

        $failed = collect($sourceResults)
            ->flatMap(function (array $result): array {
                return collect($result['failed_surahs'])
                    ->map(fn (int $surahId): string => $result['reciter_name'].' — surah '.$surahId)
                    ->all();
            })
            ->values()
            ->all();

        return [
            'source_id' => $defaultSource->id,
            'source_count' => count($sourceResults),
            'sources' => $sourceResults,
            'timings' => QuranAyahTiming::query()->where('quran_audio_source_id', $defaultSource->id)->count(),
            'total_timings' => collect($sourceResults)->sum('timings'),
            'expected_timings' => self::FULL_QURAN_AYAH_COUNT * count($sourceResults),
            'pages' => QuranAyahTiming::query()
                ->where('quran_audio_source_id', $defaultSource->id)
                ->whereNotNull('page_number')
                ->distinct()
                ->count('page_number'),
            'presets' => QuranPracticePreset::query()
                ->where('institution_id', $institution->id)
                ->where('status', 'active')
                ->count(),
            'failed_surahs' => $failed,
            'saved_operations' => collect($sourceResults)->sum('saved_operations'),
        ];
    }

    /** @param \Illuminate\Support\Collection<int, QuranSurah> $surahs */
    private function syncSource(QuranAudioSource $source, \Illuminate\Support\Collection $surahs): array
    {
        $existingCounts = QuranAyahTiming::query()
            ->where('quran_audio_source_id', $source->id)
            ->selectRaw('surah_id, COUNT(*) as aggregate')
            ->groupBy('surah_id')
            ->pluck('aggregate', 'surah_id');

        $pending = $surahs->filter(function (QuranSurah $surah, int $surahId) use ($existingCounts): bool {
            return (int) ($existingCounts[$surahId] ?? 0) !== (int) $surah->verse_count;
        });

        $responses = $pending->isEmpty()
            ? []
            : $this->fetchTimings($pending->keys()->map(fn ($id) => (int) $id)->values()->all(), (int) $source->external_id);

        $saved = 0;
        $failed = [];

        if ($pending->isNotEmpty()) {
            DB::transaction(function () use ($responses, $source, $pending, &$saved, &$failed): void {
                foreach ($pending as $surahId => $surah) {
                    $response = $responses['surah-'.$surahId] ?? null;
                    if (! $response instanceof Response || ! $response->successful() || ! is_array($response->json())) {
                        $failed[] = (int) $surahId;
                        continue;
                    }

                    $rows = collect($response->json())
                        ->filter(fn ($row): bool => is_array($row) && (int) ($row['ayah'] ?? 0) >= 1)
                        ->filter(fn ($row): bool => (int) ($row['ayah'] ?? 0) <= (int) $surah->verse_count)
                        ->keyBy(fn (array $row): int => (int) $row['ayah']);

                    // Sebagian read MP3Quran mempunyai anomali timing pada ayat terakhir
                    // (contoh Al-Fatihah Al-Husary: endpoint saat ini hanya menyediakan 1–6).
                    // Jangan membuang timing satu surah penuh hanya karena penanda akhir hilang.
                    // Jika tepat ayat terakhir yang hilang, lanjutkan dari end_time ayat sebelumnya
                    // sampai file audio berakhir. Event `ended` pada browser menjadi batas final sebenarnya.
                    if ($rows->count() !== (int) $surah->verse_count) {
                        $rows = $this->repairTrailingTimingGap($rows, $surah);
                    }

                    if ($rows->count() !== (int) $surah->verse_count) {
                        $failed[] = (int) $surahId;
                        continue;
                    }

                    // Hanya ganti satu surah setelah responsnya terbukti lengkap.
                    QuranAyahTiming::query()
                        ->where('quran_audio_source_id', $source->id)
                        ->where('surah_id', $surahId)
                        ->delete();

                    foreach ($rows as $row) {
                        $pageUrl = $this->secureUrl($row['page'] ?? null);
                        $startMs = max(0, (int) ($row['start_time'] ?? 0));
                        $endMs = max($startMs + 1, (int) ($row['end_time'] ?? ($startMs + 1)));

                        QuranAyahTiming::query()->create([
                            'quran_audio_source_id' => $source->id,
                            'surah_id' => $surahId,
                            'verse_number' => (int) $row['ayah'],
                            'start_ms' => $startMs,
                            'end_ms' => $endMs,
                            'page_number' => $this->pageNumber($pageUrl),
                            'page_image_url' => $pageUrl,
                            'audio_url' => rtrim((string) $source->base_url, '/').'/'.str_pad((string) $surahId, 3, '0', STR_PAD_LEFT).'.mp3',
                            'polygon' => is_string($row['polygon'] ?? null) ? $row['polygon'] : null,
                            'x' => filled($row['x'] ?? null) ? (float) $row['x'] : null,
                            'y' => filled($row['y'] ?? null) ? (float) $row['y'] : null,
                        ]);
                        $saved++;
                    }
                }
            });
        }

        $timingCount = QuranAyahTiming::query()
            ->where('quran_audio_source_id', $source->id)
            ->whereBetween('surah_id', [1, 114])
            ->count();

        return [
            'source_id' => $source->id,
            'external_id' => $source->external_id,
            'reciter_name' => $source->reciter_name,
            'is_default' => $source->is_default,
            'timings' => $timingCount,
            'complete' => $timingCount >= self::FULL_QURAN_AYAH_COUNT,
            'failed_surahs' => array_values(array_unique($failed)),
            'saved_operations' => $saved,
        ];
    }


    /**
     * MP3Quran timing data can occasionally omit only the final ayah marker while
     * the surah MP3 itself is complete. In that narrow case we can safely start
     * the final ayah where the preceding timing ends and let the HTML5 `ended`
     * event determine the actual end of the surah file.
     */
    private function repairTrailingTimingGap(\Illuminate\Support\Collection $rows, QuranSurah $surah): \Illuminate\Support\Collection
    {
        $expected = (int) $surah->verse_count;
        if ($expected < 2 || $rows->count() !== $expected - 1 || $rows->has($expected)) {
            return $rows;
        }

        foreach (range(1, $expected - 1) as $ayahNumber) {
            if (! $rows->has($ayahNumber)) {
                return $rows;
            }
        }

        $previous = $rows->get($expected - 1);
        if (! is_array($previous)) {
            return $rows;
        }

        $startMs = max(0, (int) ($previous['end_time'] ?? 0));
        if ($startMs < 1) {
            return $rows;
        }

        $rows->put($expected, [
            'ayah' => $expected,
            'start_time' => $startMs,
            // Deliberately beyond realistic ayah duration. Browser `ended`
            // closes the segment at the true end of the MP3 file.
            'end_time' => $startMs + 180000,
            'page' => $previous['page'] ?? null,
            'polygon' => null,
            'x' => null,
            'y' => null,
            '_sullam_fallback' => 'trailing_ayah_until_audio_end',
        ]);

        return $rows->sortKeys();
    }

    /** @return array<string, Response|null> */
    private function fetchTimings(array $surahIds, int $readId): array
    {
        $responses = [];

        foreach (array_chunk($surahIds, 6) as $chunk) {
            try {
                $batch = Http::pool(function (Pool $pool) use ($chunk, $readId): array {
                    $requests = [];
                    foreach ($chunk as $surahId) {
                        $requests[] = $pool->as('surah-'.$surahId)
                            ->acceptJson()
                            ->connectTimeout(8)
                            ->timeout(25)
                            ->get('https://mp3quran.net/api/v3/ayat_timing', [
                                'surah' => $surahId,
                                'read' => $readId,
                            ]);
                    }
                    return $requests;
                });
                foreach ($batch as $key => $response) {
                    $responses[(string) $key] = $response;
                }
            } catch (Throwable) {
                // Kegagalan batch dicoba ulang satu per satu di bawah.
            }
        }

        foreach ($surahIds as $surahId) {
            $key = 'surah-'.$surahId;
            if (($responses[$key] ?? null) instanceof Response && $responses[$key]->successful()) {
                continue;
            }

            try {
                $responses[$key] = Http::acceptJson()
                    ->connectTimeout(8)
                    ->timeout(25)
                    ->retry(2, 700, throw: false)
                    ->get('https://mp3quran.net/api/v3/ayat_timing', [
                        'surah' => $surahId,
                        'read' => $readId,
                    ]);
            } catch (Throwable) {
                $responses[$key] = null;
            }
        }

        return $responses;
    }

    public function seedPresets(Institution $institution, QuranAudioSource $source): void
    {
        // Preset per-surah membantu pemula, tetapi tidak membuat preset per halaman agar katalog tetap ringan.
        QuranSurah::query()->orderBy('id')->get()->each(function (QuranSurah $surah) use ($institution, $source): void {
            $this->upsertPreset($institution, $source, 'surah-'.$surah->id, [
                'title' => 'Surah '.$surah->name_latin.' lengkap',
                'description' => 'Latihan satu surah penuh dengan pengulangan yang dapat diubah pengguna.',
                'mode' => 'surah',
                'start_surah_id' => $surah->id,
                'end_surah_id' => $surah->id,
                'start_verse' => 1,
                'end_verse' => $surah->verse_count,
                'repeat_count' => 3,
                'repeat_scope' => 'whole_selection',
                'gap_seconds' => 1,
                'playback_rate' => 1.00,
                'audience' => 'all',
                'is_featured' => in_array($surah->id, [1, 18, 36, 55, 67, 78, 101, 112, 113, 114], true),
            ]);
        });

        if (DB::getSchemaBuilder()->hasTable('quran_ayahs')) {
            foreach (range(1, 30) as $juz) {
                $this->upsertPreset($institution, $source, 'juz-'.$juz, [
                    'title' => 'Juz '.$juz.' lengkap',
                    'description' => 'Dengar dan murāja‘ah seluruh Juz '.$juz.'.',
                    'mode' => 'juz',
                    'juz_number' => $juz,
                    'repeat_count' => 1,
                    'repeat_scope' => 'whole_selection',
                    'gap_seconds' => 1,
                    'playback_rate' => 1.00,
                    'audience' => 'all',
                    'is_featured' => in_array($juz, [1, 30], true),
                ]);
            }
        }

        // Delapan rubu' Juz 30 adalah milestone khas Sullamul Hifz, berbeda dari 240 Rubu' al-Hizb standar.
        QuranRubu::query()->where('juz_number', 30)->where('status', 'active')->orderBy('rubu_number')->get()->each(function (QuranRubu $rubu) use ($institution, $source): void {
            $this->upsertPreset($institution, $source, 'rubu-sullam-'.$rubu->rubu_number, [
                'title' => $rubu->name,
                'description' => 'Milestone penjagaan Juz 30 versi Sullamul Ḥifẓ.',
                'mode' => 'rubu',
                'quran_rubu_id' => $rubu->id,
                'start_surah_id' => $rubu->start_surah_id,
                'end_surah_id' => $rubu->end_surah_id,
                'repeat_count' => 1,
                'repeat_scope' => 'whole_selection',
                'gap_seconds' => 1,
                'playback_rate' => 1.00,
                'audience' => 'all',
                'is_featured' => false,
            ]);
        });

        $this->upsertPreset($institution, $source, 'featured-fatihah', [
            'title' => 'Al-Faatiha — dengar dan tirukan',
            'description' => 'Latihan pembuka Al-Qur’an dengan pengulangan per ayat.',
            'mode' => 'surah',
            'start_surah_id' => 1,
            'end_surah_id' => 1,
            'start_verse' => 1,
            'end_verse' => 7,
            'repeat_count' => 5,
            'repeat_scope' => 'each_item',
            'gap_seconds' => 2,
            'playback_rate' => 0.90,
            'audience' => 'all',
            'is_featured' => true,
        ]);

        $this->upsertPreset($institution, $source, 'featured-annas-1-x10', [
            'title' => 'An-Naas ayat 1 — ulang 10 kali',
            'description' => 'Latihan fokus satu ayat untuk talaqqi dan penguatan bunyi.',
            'mode' => 'ayah',
            'start_surah_id' => 114,
            'end_surah_id' => 114,
            'start_verse' => 1,
            'end_verse' => 1,
            'repeat_count' => 10,
            'repeat_scope' => 'each_item',
            'gap_seconds' => 2,
            'playback_rate' => 0.90,
            'audience' => 'all',
            'is_featured' => true,
        ]);

        $this->upsertPreset($institution, $source, 'featured-qariah-1-5-x10', [
            'title' => 'Al-Qaari‘ah ayat 1–5 — ulang per ayat',
            'description' => 'Contoh latihan TPA: setiap ayat diulang sepuluh kali sebelum lanjut.',
            'mode' => 'range',
            'start_surah_id' => 101,
            'end_surah_id' => 101,
            'start_verse' => 1,
            'end_verse' => 5,
            'repeat_count' => 10,
            'repeat_scope' => 'each_item',
            'gap_seconds' => 2,
            'playback_rate' => 0.90,
            'audience' => 'all',
            'is_featured' => true,
        ]);
    }

    private function upsertPreset(Institution $institution, QuranAudioSource $source, string $key, array $data): void
    {
        $preset = QuranPracticePreset::query()
            ->where('institution_id', $institution->id)
            ->where('metadata->seed_key', $key)
            ->first();

        $values = [
            ...$data,
            'quran_audio_source_id' => $source->id,
            'status' => 'active',
            'metadata' => ['seed_key' => $key, 'reference_content' => true],
        ];

        if ($preset) {
            $preset->update($values);
            return;
        }

        QuranPracticePreset::query()->create([
            ...$values,
            'institution_id' => $institution->id,
        ]);
    }

    private function secureUrl(mixed $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        return str_starts_with($url, 'http://') ? 'https://'.substr($url, 7) : $url;
    }

    private function pageNumber(?string $url): ?int
    {
        if (! $url) {
            return null;
        }

        return preg_match('~/([0-9]{3})\\.svg(?:\\?.*)?$~', $url, $matches) ? (int) $matches[1] : null;
    }
}
