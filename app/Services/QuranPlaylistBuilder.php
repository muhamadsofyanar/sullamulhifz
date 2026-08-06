<?php

namespace App\Services;

use App\Models\MemorizationTarget;
use App\Models\QuranAudioSource;
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

        $source = $this->source($institutionId, (int) ($selection['source_id'] ?? 0));
        $mode = (string) ($selection['mode'] ?? 'range');
        $query = QuranAyahTiming::query()
            ->with('surah')
            ->where('quran_audio_source_id', $source->id);

        if ($mode === 'page') {
            $page = (int) ($selection['page_number'] ?? 0);
            if ($page < 1) {
                throw ValidationException::withMessages(['page_number' => 'Pilih halaman Mushaf.']);
            }
            $query->where('page_number', $page)->orderBy('surah_id')->orderBy('verse_number');
        } elseif ($mode === 'rubu') {
            $rubu = QuranRubu::query()->where('juz_number', 30)->where('status', 'active')->find((int) ($selection['rubu_id'] ?? 0));
            if (! $rubu) {
                throw ValidationException::withMessages(['rubu_id' => 'Pilih rubu’ Juz 30.']);
            }
            $query
                ->whereBetween('surah_id', [min($rubu->start_surah_id, $rubu->end_surah_id), max($rubu->start_surah_id, $rubu->end_surah_id)])
                ->orderByDesc('surah_id')
                ->orderBy('verse_number');
        } else {
            $surahId = (int) ($selection['start_surah_id'] ?? $selection['surah_id'] ?? 0);
            $surah = QuranSurah::query()->whereBetween('id', [78, 114])->find($surahId);
            if (! $surah) {
                throw ValidationException::withMessages(['surah_id' => 'Pilih surah Juz 30.']);
            }
            $start = $mode === 'surah' ? 1 : max(1, (int) ($selection['start_verse'] ?? 1));
            $end = $mode === 'surah'
                ? (int) $surah->verse_count
                : min((int) $surah->verse_count, max($start, (int) ($selection['end_verse'] ?? $start)));
            $query->where('surah_id', $surahId)->whereBetween('verse_number', [$start, $end])->orderBy('verse_number');
        }

        $timings = $query->get();
        if ($timings->isEmpty()) {
            throw ValidationException::withMessages(['audio' => 'Timing audio belum tersedia. Admin perlu menjalankan sinkronisasi Juz 30.']);
        }

        $items = $this->items($timings);

        return [
            'title' => (string) ($selection['title'] ?? 'Latihan Al-Qur’an'),
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
            'summary' => [
                'ayah_count' => count($items),
                'surah_count' => collect($items)->pluck('surah_id')->unique()->count(),
                'page_count' => collect($items)->pluck('page_number')->filter()->unique()->count(),
            ],
        ];
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

    private function items(Collection $timings): array
    {
        return $timings->map(fn (QuranAyahTiming $timing): array => [
            'id' => $timing->id,
            'surah_id' => $timing->surah_id,
            'surah_name' => $timing->surah?->name_latin,
            'surah_arabic' => $timing->surah?->name_arabic,
            'verse_number' => $timing->verse_number,
            'label' => ($timing->surah?->name_latin ?? 'Surah').' · ayat '.$timing->verse_number,
            'audio_url' => $timing->audio_url,
            'start_seconds' => round($timing->start_ms / 1000, 3),
            'end_seconds' => round($timing->end_ms / 1000, 3),
            'page_number' => $timing->page_number,
            'page_image_url' => $timing->page_image_url,
            'polygon' => $timing->polygon,
        ])->values()->all();
    }
}
