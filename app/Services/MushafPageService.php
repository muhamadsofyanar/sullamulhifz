<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\MarhalahType;
use App\Models\MemorizationTarget;
use App\Models\QuranAyah;
use App\Models\QuranJourneyPortion;
use App\Models\QuranJourneyProfile;
use App\Models\QuranMushafLine;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MushafPageService
{
    public function __construct(private readonly MushafLineService $lines)
    {
    }

    /** @return Collection<int,int> */
    public function pagesForJuz(int $juz): Collection
    {
        return $this->lines->pagesForJuz($juz);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function optionsForStage(int $juz, float $portionValue, int $page): array
    {
        $pages = $this->pagesForJuz($juz);
        if (! $pages->contains($page)) {
            return [];
        }

        if ($portionValue === 0.5) {
            $this->lines->syncPage($page);
            return [
                $this->buildOption($juz, [[$page, 1, 8]], '½ halaman atas', 'half-top'),
                $this->buildOption($juz, [[$page, 9, 15]], '½ halaman bawah', 'half-bottom'),
            ];
        }

        if ($portionValue === 1.0) {
            $this->lines->syncPage($page);
            return [
                $this->buildOption($juz, [[$page, 1, 15]], '1 halaman', 'page'),
            ];
        }

        if ($portionValue === 2.0) {
            $nextPage = $page + 1;
            $this->lines->syncPage($page);
            if ($nextPage <= MushafLineService::TOTAL_PAGES) {
                $this->lines->syncPage($nextPage);
            }
            return [
                $this->buildOption($juz, [[$page, 1, 15], [$nextPage, 1, 15]], '2 halaman', 'two-pages'),
            ];
        }

        throw new \InvalidArgumentException('Mushaf Page Engine hanya mendukung ½, 1, atau 2 halaman.');
    }

    /**
     * @param array<int,array{0:int,1:int,2:int}> $segments
     * @return array<string,mixed>
     */
    private function buildOption(int $expectedJuz, array $segments, string $label, string $variant): array
    {
        $slotRows = collect();
        $slotsComplete = true;
        foreach ($segments as [$page, $startLine, $endLine]) {
            if ($page < 1 || $page > MushafLineService::TOTAL_PAGES) {
                $slotsComplete = false;
                continue;
            }
            $pageLines = $this->lines->linesForPage($page)->keyBy('line_number');
            foreach (range($startLine, $endLine) as $lineNo) {
                $line = $pageLines->get($lineNo);
                if (! $line) {
                    $slotsComplete = false;
                }
                $slotRows->push([
                    'page'=>$page,
                    'line_number'=>$lineNo,
                    'line'=>$line,
                ]);
            }
        }

        $ayahLines = $slotRows->filter(fn(array $row): bool => $row['line'] && $row['line']->line_type === 'ayah');
        $firstLine = $ayahLines->first()['line'] ?? null;
        $lastLine = $ayahLines->last()['line'] ?? null;

        $firstAny = $this->ayahFromBoundary($firstLine, true);
        $lastAny = $this->ayahFromBoundary($lastLine, false);
        $expectedAyahs = collect();
        $crossesJuz = false;

        if ($firstAny && $lastAny) {
            $range = QuranAyah::query()
                ->whereBetween('global_number', [(int)$firstAny->global_number, (int)$lastAny->global_number])
                ->orderBy('global_number')
                ->get();
            $crossesJuz = $range->contains(fn(QuranAyah $ayah): bool => (int)$ayah->juz_number !== $expectedJuz);
            $expectedAyahs = $range->filter(fn(QuranAyah $ayah): bool => (int)$ayah->juz_number === $expectedJuz)->values();
        }

        $startAyah = $expectedAyahs->first();
        $endAyah = $expectedAyahs->last();
        $available = $slotsComplete && $startAyah && $endAyah;

        $firstExact = $firstAny && $startAyah && (int)$firstAny->global_number === (int)$startAyah->global_number;
        $lastExact = $lastAny && $endAyah && (int)$lastAny->global_number === (int)$endAyah->global_number;

        $segmentText = collect($segments)->map(fn(array $s): string => $s[0].':'.$s[1].'-'.$s[2])->implode('|');
        $firstPage = $segments[0][0];
        $lastSegment = $segments[count($segments)-1];
        $lastPage = $lastSegment[0];

        return [
            'key'=>'page:'.$variant.':'.$segmentText,
            'variant'=>$variant,
            'label'=>$label,
            'segments'=>$segments,
            'start_page'=>$firstPage,
            'end_page'=>$lastPage,
            'start_line'=>$segments[0][1],
            'end_line'=>$lastSegment[2],
            'lines'=>$slotRows->map(function (array $row): array {
                /** @var QuranMushafLine|null $line */
                $line = $row['line'];
                return [
                    'page'=>$row['page'],
                    'line_number'=>$row['line_number'],
                    'type'=>$line?->line_type ?? 'missing',
                    'text'=>$line?->text ?: $line?->qpc_v2,
                    'verse_range'=>$line?->verse_range,
                ];
            })->values()->all(),
            'available'=>(bool)$available,
            'crosses_juz'=>$crossesJuz,
            'boundary_adjusted'=>(bool)($available && $crossesJuz),
            'start_global_number'=>$startAyah?->global_number,
            'end_global_number'=>$endAyah?->global_number,
            'start_surah_id'=>$startAyah?->surah_id,
            'start_verse'=>$startAyah?->verse_number,
            'end_surah_id'=>$endAyah?->surah_id,
            'end_verse'=>$endAyah?->verse_number,
            'first_word_location'=>$firstExact ? $firstLine?->first_word_location : null,
            'last_word_location'=>$lastExact ? $lastLine?->last_word_location : null,
            'has_special_lines'=>$slotRows->contains(fn(array $row): bool => $row['line'] && $row['line']->line_type !== 'ayah'),
            'has_missing_slots'=>! $slotsComplete,
        ];
    }

    private function ayahFromBoundary(?QuranMushafLine $line, bool $first): ?QuranAyah
    {
        if (! $line) return null;
        $surah = $first ? $line->first_surah_id : $line->last_surah_id;
        $verse = $first ? $line->first_verse : $line->last_verse;
        if (! $surah || ! $verse) return null;
        return QuranAyah::query()->where('surah_id', $surah)->where('verse_number', $verse)->first();
    }

    public function createPagePortion(
        Student $student,
        Teacher $teacher,
        int $page,
        string $variant,
        ?string $scheduledFor = null,
        ?string $dueDate = null,
        ?string $notes = null,
    ): QuranJourneyPortion {
        $profile = QuranJourneyProfile::query()
            ->where('institution_id', $student->institution_id)
            ->where('student_id', $student->id)
            ->firstOrFail();
        $juz = (int)$profile->current_juz_number;
        $rule = app(QuranJourneyService::class)->ruleForJuz($juz);
        if (($rule['unit'] ?? null) !== 'page') {
            throw ValidationException::withMessages(['page_portion'=>'Marhalah aktif bukan porsi berbasis halaman.']);
        }

        $value = (float)$rule['value'];
        $allowedVariant = match (true) {
            abs($value - 0.5) < 0.001 => ['half-top','half-bottom'],
            abs($value - 1.0) < 0.001 => ['page'],
            abs($value - 2.0) < 0.001 => ['two-pages'],
            default => [],
        };
        if (! in_array($variant, $allowedVariant, true)) {
            throw ValidationException::withMessages(['page_portion'=>'Pilihan halaman tidak sesuai Marhalah aktif.']);
        }

        $option = collect($this->optionsForStage($juz, $value, $page))->firstWhere('variant', $variant);
        if (! $option || ! $option['available']) {
            throw ValidationException::withMessages(['page_portion'=>'Porsi halaman ini belum memiliki pemetaan Mushaf yang dapat digunakan.']);
        }

        $startAyah = QuranAyah::query()->where('global_number', $option['start_global_number'])->firstOrFail();
        $endAyah = QuranAyah::query()->where('global_number', $option['end_global_number'])->firstOrFail();
        $marhalah = app(QuranJourneyService::class)->marhalahForJuz($juz);
        if (! $marhalah instanceof MarhalahType) {
            throw ValidationException::withMessages(['page_portion'=>'Master Marhalah belum tersedia.']);
        }
        $activeYear = AcademicYear::query()->where('institution_id',$student->institution_id)->where('is_active',true)->first();
        if (! $activeYear) {
            throw ValidationException::withMessages(['page_portion'=>'Tahun ajaran aktif belum tersedia untuk membuat target setoran.']);
        }

        return DB::transaction(function () use ($student,$teacher,$juz,$rule,$marhalah,$activeYear,$option,$startAyah,$endAyah,$scheduledFor,$dueDate,$notes): QuranJourneyPortion {
            $selectionSource = $option['boundary_adjusted'] ? 'mushaf_page_engine_boundary' : 'mushaf_page_engine';
            $portion = QuranJourneyPortion::query()->create([
                'institution_id'=>$student->institution_id,
                'student_id'=>$student->id,
                'marhalah_type_id'=>$marhalah->id,
                'assigned_by_teacher_id'=>$teacher->id,
                'journey_juz_number'=>$juz,
                'portion_unit'=>'page',
                'portion_value'=>$rule['value'],
                'portion_label'=>$rule['portion'],
                'start_global_number'=>$startAyah->global_number,
                'end_global_number'=>$endAyah->global_number,
                'start_surah_id'=>$startAyah->surah_id,
                'start_verse'=>$startAyah->verse_number,
                'end_surah_id'=>$endAyah->surah_id,
                'end_verse'=>$endAyah->verse_number,
                'start_page_number'=>$option['start_page'],
                'end_page_number'=>$option['end_page'],
                'mushaf_layout_code'=>$this->lines->layoutCode(),
                'start_line_number'=>$option['start_line'],
                'end_line_number'=>$option['end_line'],
                'start_word_location'=>$option['first_word_location'],
                'end_word_location'=>$option['last_word_location'],
                'line_block_key'=>$option['key'],
                'selection_source'=>$selectionSource,
                'teacher_confirmed'=>true,
                'status'=>'planned',
                'scheduled_for'=>$scheduledFor,
                'due_date'=>$dueDate,
                'notes'=>$notes,
            ]);

            $ayahs = QuranAyah::query()->whereBetween('global_number',[$startAyah->global_number,$endAyah->global_number])
                ->where('juz_number',$juz)
                ->orderBy('global_number')->get()->groupBy('surah_id');
            foreach ($ayahs as $surahId => $surahAyahs) {
                MemorizationTarget::query()->create([
                    'institution_id'=>$student->institution_id,
                    'academic_year_id'=>$activeYear->id,
                    'student_id'=>$student->id,
                    'assigned_by_teacher_id'=>$teacher->id,
                    'quran_journey_portion_id'=>$portion->id,
                    'surah_id'=>(int)$surahId,
                    'start_verse'=>(int)$surahAyahs->first()->verse_number,
                    'end_verse'=>(int)$surahAyahs->last()->verse_number,
                    'marhalah_type_id'=>$marhalah->id,
                    'journey_juz_number'=>$juz,
                    'portion_confirmed'=>true,
                    'portion_note'=>$this->targetNote($option,$rule),
                    'mushaf_page_number'=>$option['start_page'],
                    'mushaf_end_page_number'=>$option['end_page'],
                    'mushaf_start_line'=>$option['start_line'],
                    'mushaf_end_line'=>$option['end_line'],
                    'start_word_location'=>$option['first_word_location'],
                    'end_word_location'=>$option['last_word_location'],
                    'target_type'=>'new_memorization',
                    'status'=>'active',
                    'target_date'=>$scheduledFor,
                    'due_date'=>$dueDate,
                    'notes'=>$notes,
                ]);
            }

            return $portion->fresh(['marhalah','startSurah','endSurah','targets.surah']);
        });
    }

    /** @param array<string,mixed> $option @param array<string,mixed> $rule */
    private function targetNote(array $option, array $rule): string
    {
        $pages = $option['start_page'] === $option['end_page']
            ? 'halaman '.$option['start_page']
            : 'halaman '.$option['start_page'].'–'.$option['end_page'];
        $boundary = $option['boundary_adjusted'] ? ' · porsi batas Juz dipotong pada batas Juz' : '';
        $words = $option['first_word_location'] && $option['last_word_location']
            ? ' · batas kata '.$option['first_word_location'].' → '.$option['last_word_location']
            : '';
        return 'Mushaf Madinah '.$pages.' · '.$rule['portion'].' · slot '.$option['start_line'].'–'.$option['end_line'].$boundary.$words.'.';
    }
}
