<?php

namespace App\Services;

/** @phase 6.0 Family-safe memorization summary */

use App\Models\MemorizationRecord;
use App\Models\MemorizationReviewPlan;
use App\Models\MurajaahRecord;
use App\Models\Student;
use App\Models\StudentMemorizationFocus;
use Carbon\CarbonInterface;

class MemorizationSummaryService
{
    /** @return array<string, mixed> */
    public function forStudent(Student $student, CarbonInterface $from, CarbonInterface $to): array
    {
        $periodFrom = $from->copy();
        $periodTo = $to->copy();
        $memorization = MemorizationRecord::query()
            ->with('surah')
            ->where('institution_id', $student->institution_id)
            ->where('student_id', $student->id)
            ->whereBetween('recorded_at', [$periodFrom->copy()->startOfDay(), $periodTo->copy()->endOfDay()])
            ->latest('recorded_at')->get();
        $murajaah = MurajaahRecord::query()
            ->with('surah')
            ->where('institution_id', $student->institution_id)
            ->where('student_id', $student->id)
            ->whereBetween('recorded_at', [$periodFrom->copy()->startOfDay(), $periodTo->copy()->endOfDay()])
            ->latest('recorded_at')->get();
        $decisions = $memorization->pluck('daily_decision')
            ->merge($murajaah->pluck('daily_decision'))
            ->filter()->countBy();
        $latest = $memorization->concat($murajaah)->sortByDesc('recorded_at')->first();

        return [
            'period_from' => $periodFrom->toDateString(),
            'period_to' => $periodTo->toDateString(),
            'submission_count' => $memorization->count(),
            'murajaah_count' => $murajaah->count(),
            'decisions' => [
                'lanjut' => (int) $decisions->get('lanjut', 0),
                'kuatkan' => (int) $decisions->get('kuatkan', 0),
                'ulang' => (int) $decisions->get('ulang', 0),
            ],
            'latest' => $latest,
            'latest_note' => $latest?->short_note,
            'focus' => StudentMemorizationFocus::activeFor((int) $student->institution_id, (int) $student->id),
            'next_review' => MemorizationReviewPlan::query()->with('surah')
                ->where('institution_id', $student->institution_id)
                ->where('student_id', $student->id)
                ->where('status', 'scheduled')
                ->orderBy('review_date')->first(),
        ];
    }
}
