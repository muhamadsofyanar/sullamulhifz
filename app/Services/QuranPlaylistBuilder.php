<?php

namespace App\Services;

use App\Models\MemorizationTarget;
use App\Models\QuranAudioSource;
use App\Models\QuranAyah;
use App\Models\QuranAyahTiming;
use App\Models\QuranPracticePreset;
use App\Models\QuranRubu;
use App\Models\QuranSurah;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class QuranPlaylistBuilder
{
    public function fromPreset(QuranPracticePreset $preset, ?int $sourceId = null): array
    {
        return $this->build([
            'institution_id' => $preset->institution_id,
            'source_id' => $sourceId ?: $preset->quran_audio_source_id,
            'mode' => $preset->mode,
            'rubu_id' => $preset->quran_rubu_id,
            'page_number' => $preset->page_number,
            'juz_number' => $preset->juz_number,
            'hizb_quarter' => $preset->hizb_quarter,
            'start_surah_id' => $preset->start_surah_id,
            'end_surah_id' => $preset->end_surah_id,
            'start_verse' => $preset->start_verse,
            'end_verse' => $preset->end_verse,
            'repeat_count' => $preset->repeat_count,
            'repeat_scope' => $preset->repeat_scope,
            'gap_seconds' => $preset->gap_seconds,
            'playback_rate' => $preset->playback_rate,
            'title' => $preset->title,
            'preset_id' => $preset->id,
        ]);
    }

    public function fromTarget(MemorizationTarget $target, ?int $sourceId = null): array
    {
        return $this->build([
            'institution_id' => $target->institution_id,
            'source_id' => $sourceId,
            'mode' => $target->start_verse === $target->end_verse ? 'ayah' : 'range',
            'start_surah_id' => $target->surah_id,
            'end_surah_id' => $target->surah_id,
            'start_verse' => $target->start_verse,
            'end_verse' => $target->end_verse,
            'repeat_count' => 10,
            'repeat_scope' => 'each_item',
            'gap_seconds' => 2,
            'playback_rate' => 0.90,
            'title' => ($target->surah?->name_latin ?? 'Target hafalan').' '.$target->start_verse.'–'.$target->end_verse,
            'target_id' => $target->id,
            'student_id' => $target->student_id,
        ]);
    }

    public function build(array $selection): array
    {
        $institutionId = (int) ($selection['institution_id'] ?? 0);
        if ($institutionId < 1) {
            throw ValidationException::withMessages(['audio' => 'Konteks lembaga tidak tersedia.']);
        }

        if (! \Illuminate\Support\Facades\Schema::hasTable('quran_ayahs') || QuranAyah::query()->count() < QuranCorpusSyncService::AYAH_COUNT) {
            throw ValidationException::withMessages(['quran' => 'Korpus 30 juz belum lengkap. Admin perlu menjalankan sinkronisasi Full Qur’an terlebih dahulu.']);
        }

        $source = $this->source($institutionId, (int) ($selection['source_id'] ?? 0));
        $mode = (string) ($selection['mode'] ?? 'range');
        $ayahs = $this->selectedAyahs($mode, $selection);

        if ($ayahs->isEmpty()) {
            throw ValidationException::withMessages(['quran' => 'Bagian Al-Qur’an yang dipilih tidak ditemukan.']);
        }

        $timings = $this->timingsFor($source, $ayahs);
        $items = $this->items($ayahs, $timings);
        $missingAudio = collect($items)->where('audio_ready', false)->count();

        return [
            'title' => (string) ($selection['title'] ?? $this->selectionTitle($mode, $selection, $ayahs)),
            'mode' => $mode,
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
                'reciter' => $source->reciter_name,
                'rewaya' => $source->rewaya,
                'attribution' => data_get($source->metadata, 'source_attribution', 'MP3Quran.net'),
            ],
            'settings' => [
                'repeat_count' => min(100, max(0, (int) ($selection['repeat_count'] ?? 3))),
                'repeat_scope' => in_array(($selection['repeat_scope'] ?? ''), ['each_item', 'whole_selection'], true)
                    ? $selection['repeat_scope']
                    : 'each_item',
                'gap_seconds' => min(15, max(0, (int) ($selection['gap_seconds'] ?? 1))),
                'playback_rate' => min(1.50, max(0.65, (float) ($selection['playback_rate'] ?? 1.00))),
            ],
            'context' => collect(['preset_id', 'target_id', 'student_id'])
                ->mapWithKeys(fn (string $key): array => [$key => $selection[$key] ?? null])
                ->filter(fn ($value): bool => $value !== null)
                ->all(),
            'items' => $items,
            'audio_complete' => $missingAudio === 0,
            'summary' => [
                'ayah_count' => count($items),
                'surah_count' => collect($items)->pluck('surah_id')->unique()->count(),
                'juz_count' => collect($items)->pluck('juz_number')->filter()->unique()->count(),
                'page_count' => collect($items)->pluck('page_number')->filter()->unique()->count(),
                'missing_audio_count' => $missingAudio,
            ],
        ];
    }

    private function selectedAyahs(string $mode, array $selection): Collection
    {
        $query = QuranAyah::query()->with('surah')->orderBy('global_number');

        if ($mode === 'page') {
            $page = (int) ($selection['page_number'] ?? 0);
            if ($page < 1 || $page > QuranCorpusSyncService::PAGE_COUNT) {
                throw ValidationException::withMessages(['page_number' => 'Pilih halaman Mushaf 1–604.']);
            }
            return $query->where('page_number', $page)->get();
        }

        if ($mode === 'juz') {
            $juz = (int) ($selection['juz_number'] ?? 0);
            if ($juz < 1 || $juz > 30) {
                throw ValidationException::withMessages(['juz_number' => 'Pilih Juz 1–30.']);
            }
            return $query->where('juz_number', $juz)->get();
        }

        if ($mode === 'hizb_quarter') {
            $quarter = (int) ($selection['hizb_quarter'] ?? 0);
            if ($quarter < 1 || $quarter > QuranCorpusSyncService::HIZB_QUARTER_COUNT) {
                throw ValidationException::withMessages(['hizb_quarter' => 'Pilih Rubu‘ al-Hizb 1–240.']);
            }
            return $query->where('hizb_quarter', $quarter)->get();
        }

        // Mode rubu lama adalah delapan milestone Juz 30 khas Sullamul Hifz.
        if ($mode === 'rubu') {
            $rubu = QuranRubu::query()->where('juz_number', 30)->where('status', 'active')->find((int) ($selection['rubu_id'] ?? 0));
            if (! $rubu) {
                throw ValidationException::withMessages(['rubu_id' => 'Pilih milestone rubu‘ Juz 30.']);
            }
            return $query
                ->whereBetween('surah_id', [min($rubu->start_surah_id, $rubu->end_surah_id), max($rubu->start_surah_id, $rubu->end_surah_id)])
                ->get();
        }

        $surahId = (int) ($selection['start_surah_id'] ?? $selection['surah_id'] ?? 0);
        $surah = QuranSurah::query()->find($surahId);
        if (! $surah) {
            throw ValidationException::withMessages(['surah_id' => 'Pilih salah satu dari 114 surah.']);
        }

        $start = $mode === 'surah' ? 1 : max(1, (int) ($selection['start_verse'] ?? 1));
        $end = $mode === 'surah'
            ? (int) $surah->verse_count
            : min((int) $surah->verse_count, max($start, (int) ($selection['end_verse'] ?? $start)));

        return $query->where('surah_id', $surahId)->whereBetween('verse_number', [$start, $end])->get();
    }

    private function timingsFor(QuranAudioSource $source, Collection $ayahs): Collection
    {
        $groups = $ayahs->groupBy('surah_id');
        if ($groups->isEmpty()) {
            return collect();
        }

        return QuranAyahTiming::query()
            ->where('quran_audio_source_id', $source->id)
            ->where(function ($query) use ($groups): void {
                foreach ($groups as $surahId => $rows) {
                    $query->orWhere(function ($part) use ($surahId, $rows): void {
                        $part->where('surah_id', (int) $surahId)
                            ->whereBetween('verse_number', [
                                (int) $rows->min('verse_number'),
                                (int) $rows->max('verse_number'),
                            ]);
                    });
                }
            })
            ->get()
            ->keyBy(fn (QuranAyahTiming $timing): string => $timing->surah_id.':'.$timing->verse_number);
    }

    private function source(int $institutionId, int $sourceId): QuranAudioSource
    {
        $query = QuranAudioSource::query()
            ->where('institution_id', $institutionId)
            ->where('status', 'active');

        $source = $sourceId > 0
            ? (clone $query)->find($sourceId)
            : (clone $query)->where('is_default', true)->first();

        $source ??= (clone $query)->first();

        if (! $source) {
            throw ValidationException::withMessages(['audio' => 'Sumber audio belum dikonfigurasi untuk lembaga ini.']);
        }

        return $source;
    }

    private function items(Collection $ayahs, Collection $timings): array
    {
        return $ayahs->map(function (QuranAyah $ayah) use ($timings): array {
            /** @var QuranAyahTiming|null $timing */
            $timing = $timings->get($ayah->surah_id.':'.$ayah->verse_number);

            return [
                'global_number' => $ayah->global_number,
                'surah_id' => $ayah->surah_id,
                'surah_name' => $ayah->surah?->name_latin,
                'surah_arabic' => $ayah->surah?->name_arabic,
                'verse_number' => $ayah->verse_number,
                'label' => ($ayah->surah?->name_latin ?? 'Surah').' · ayat '.$ayah->verse_number,
                'arabic_text' => $ayah->arabic_text,
                'juz_number' => $ayah->juz_number,
                'hizb_quarter' => $ayah->hizb_quarter,
                'page_number' => $ayah->page_number,
                'sajda' => $ayah->sajda,
                'audio_ready' => (bool) $timing,
                'audio_url' => $timing?->audio_url,
                'start_seconds' => $timing ? round($timing->start_ms / 1000, 3) : null,
                'end_seconds' => $timing ? round($timing->end_ms / 1000, 3) : null,
                'page_image_url' => $timing?->page_image_url,
                'polygon' => $timing?->polygon,
            ];
        })->values()->all();
    }

    private function selectionTitle(string $mode, array $selection, Collection $ayahs): string
    {
        return match ($mode) {
            'juz' => 'Juz '.(int) ($selection['juz_number'] ?? 0),
            'page' => 'Halaman Mushaf '.(int) ($selection['page_number'] ?? 0),
            'hizb_quarter' => 'Rubu‘ al-Hizb '.(int) ($selection['hizb_quarter'] ?? 0),
            default => (($ayahs->first()?->surah?->name_latin) ?? 'Latihan Al-Qur’an'),
        };
    }
}
