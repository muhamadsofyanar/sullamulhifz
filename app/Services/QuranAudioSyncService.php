<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\QuranAudioSource;
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
    public const JUZ_30_SURAH_START = 78;
    public const JUZ_30_SURAH_END = 114;
    public const JUZ_30_AYAH_COUNT = 564;

    public function syncInstitution(Institution $institution): array
    {
        $source = QuranAudioSource::query()->updateOrCreate(
            ['institution_id' => $institution->id, 'provider' => 'mp3quran', 'external_id' => '5'],
            [
                'name' => 'Murattal Ahmad Al-Ajmi',
                'reciter_name' => 'Ahmad bin Ali Al-Ajmi',
                'rewaya' => 'Hafs dari Asim — Murattal',
                'base_url' => 'https://server10.mp3quran.net/ajm/',
                'metadata' => [
                    'timing_endpoint' => 'https://mp3quran.net/api/v3/ayat_timing',
                    'source_attribution' => 'MP3Quran.net',
                    'sync_scope' => 'Juz 30',
                ],
                'is_default' => true,
                'status' => 'active',
            ]
        );

        QuranAudioSource::query()
            ->where('institution_id', $institution->id)
            ->where('id', '!=', $source->id)
            ->update(['is_default' => false]);

        $surahs = QuranSurah::query()
            ->whereBetween('id', [self::JUZ_30_SURAH_START, self::JUZ_30_SURAH_END])
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        if ($surahs->count() !== 37 || (int) $surahs->sum('verse_count') !== self::JUZ_30_AYAH_COUNT) {
            throw new RuntimeException('Master Juz 30 belum lengkap: dibutuhkan 37 surah dan 564 ayat.');
        }

        $responses = $this->fetchTimings($surahs->keys()->all(), (int) ($source->external_id ?: 5));
        $saved = 0;
        $failed = [];

        DB::transaction(function () use ($responses, $source, $surahs, &$saved, &$failed): void {
            foreach ($surahs as $surahId => $surah) {
                $response = $responses['surah-'.$surahId] ?? null;
                if (! $response instanceof Response || ! $response->successful() || ! is_array($response->json())) {
                    $failed[] = (int) $surahId;
                    continue;
                }

                $rows = collect($response->json())
                    ->filter(fn ($row): bool => is_array($row) && (int) ($row['ayah'] ?? 0) >= 1)
                    ->filter(fn ($row): bool => (int) ($row['ayah'] ?? 0) <= (int) $surah->verse_count)
                    ->keyBy(fn (array $row): int => (int) $row['ayah']);

                if ($rows->count() !== (int) $surah->verse_count) {
                    $failed[] = (int) $surahId;
                }

                foreach ($rows as $row) {
                    $pageUrl = $this->secureUrl($row['page'] ?? null);
                    $startMs = max(0, (int) ($row['start_time'] ?? 0));
                    $endMs = max($startMs + 1, (int) ($row['end_time'] ?? ($startMs + 1)));

                    QuranAyahTiming::query()->updateOrCreate(
                        [
                            'quran_audio_source_id' => $source->id,
                            'surah_id' => $surahId,
                            'verse_number' => (int) $row['ayah'],
                        ],
                        [
                            'start_ms' => $startMs,
                            'end_ms' => $endMs,
                            'page_number' => $this->pageNumber($pageUrl),
                            'page_image_url' => $pageUrl,
                            'audio_url' => rtrim((string) $source->base_url, '/').'/'.str_pad((string) $surahId, 3, '0', STR_PAD_LEFT).'.mp3',
                            'polygon' => is_string($row['polygon'] ?? null) ? $row['polygon'] : null,
                            'x' => filled($row['x'] ?? null) ? (float) $row['x'] : null,
                            'y' => filled($row['y'] ?? null) ? (float) $row['y'] : null,
                        ]
                    );
                    $saved++;
                }
            }
        });

        $this->seedPresets($institution, $source);

        return [
            'source_id' => $source->id,
            'timings' => QuranAyahTiming::query()->where('quran_audio_source_id', $source->id)->whereBetween('surah_id', [78, 114])->count(),
            'pages' => QuranAyahTiming::query()->where('quran_audio_source_id', $source->id)->whereNotNull('page_number')->distinct()->count('page_number'),
            'presets' => QuranPracticePreset::query()->where('institution_id', $institution->id)->where('status', 'active')->count(),
            'failed_surahs' => array_values(array_unique($failed)),
            'saved_operations' => $saved,
        ];
    }

    /** @return array<string, Response|null> */
    private function fetchTimings(array $surahIds, int $readId): array
    {
        $responses = [];

        foreach (array_chunk($surahIds, 8) as $chunk) {
            try {
                $batch = Http::pool(function (Pool $pool) use ($chunk, $readId): array {
                    $requests = [];
                    foreach ($chunk as $surahId) {
                        $key = 'surah-'.$surahId;
                        $requests[] = $pool->as($key)
                            ->acceptJson()
                            ->timeout(20)
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
                // Kegagalan satu batch akan dicoba ulang satu per satu di bawah.
            }
        }

        foreach ($surahIds as $surahId) {
            $key = 'surah-'.$surahId;
            if (($responses[$key] ?? null) instanceof Response && $responses[$key]->successful()) {
                continue;
            }

            try {
                $responses[$key] = Http::acceptJson()
                    ->timeout(20)
                    ->retry(2, 500, throw: false)
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
        QuranSurah::query()->whereBetween('id', [78, 114])->orderByDesc('id')->get()->each(function (QuranSurah $surah) use ($institution, $source): void {
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
                'is_featured' => in_array($surah->id, [114, 113, 112, 101], true),
            ]);
        });

        QuranRubu::query()->where('juz_number', 30)->where('status', 'active')->orderBy('rubu_number')->get()->each(function (QuranRubu $rubu) use ($institution, $source): void {
            $this->upsertPreset($institution, $source, 'rubu-'.$rubu->rubu_number, [
                'title' => $rubu->name,
                'description' => 'Latihan milestone rubu’ sesuai pembagian Sullamul Ḥifẓ.',
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

        QuranAyahTiming::query()
            ->where('quran_audio_source_id', $source->id)
            ->whereNotNull('page_number')
            ->distinct()
            ->orderBy('page_number')
            ->pluck('page_number')
            ->each(function (int $page) use ($institution, $source): void {
                $this->upsertPreset($institution, $source, 'page-'.$page, [
                    'title' => 'Halaman Mushaf '.$page,
                    'description' => 'Latihan seluruh ayat pada halaman Mushaf Madinah '.$page.'.',
                    'mode' => 'page',
                    'page_number' => $page,
                    'repeat_count' => 3,
                    'repeat_scope' => 'whole_selection',
                    'gap_seconds' => 1,
                    'playback_rate' => 1.00,
                    'audience' => 'all',
                    'is_featured' => $page === 583,
                ]);
            });

        $this->upsertPreset($institution, $source, 'featured-annas-1-x10', [
            'title' => 'An-Nās ayat 1 — ulang 10 kali',
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
            'title' => 'Al-Qāri‘ah ayat 1–5 — ulang per ayat',
            'description' => 'Contoh target aktif TPA Al-Insyirah: setiap ayat diulang sepuluh kali sebelum lanjut.',
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

        return preg_match('~/(\d{3})\.svg(?:\?.*)?$~', $url, $matches) ? (int) $matches[1] : null;
    }
}
