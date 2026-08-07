<?php

namespace App\Services;

use App\Models\MemorizationRecord;
use App\Models\MemorizationReviewPlan;
use App\Models\MemorizationTarget;
use App\Models\MurajaahRecord;
use App\Models\QuranLearningErrorItem;
use App\Models\Student;
use App\Models\TahfizhLearningCycle;
use Illuminate\Support\Collection;

class TahfizhProgressService
{
    /** @return array<string, mixed> */
    public function student(Student $student): array
    {
        $activeTargets = MemorizationTarget::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['active','in_progress','strengthening','paused'])
            ->count();

        $dueReviews = MemorizationReviewPlan::query()
            ->where('student_id', $student->id)
            ->where('status', 'scheduled')
            ->whereDate('review_date', '<=', today())
            ->count();

        $nextReview = MemorizationReviewPlan::query()
            ->with('surah')
            ->where('student_id', $student->id)
            ->where('status', 'scheduled')
            ->orderBy('review_date')
            ->first();

        $latestMemorization = MemorizationRecord::with('surah')
            ->where('student_id', $student->id)
            ->latest('recorded_at')->first();
        $latestMurajaah = MurajaahRecord::with('surah')
            ->where('student_id', $student->id)
            ->latest('recorded_at')->first();

        $openErrors = QuranLearningErrorItem::query()
            ->where('student_id', $student->id)
            ->whereNull('resolved_at')
            ->count();
        $errorFocus = QuranLearningErrorItem::query()
            ->where('student_id', $student->id)
            ->whereNull('resolved_at')
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        $activeCycles = TahfizhLearningCycle::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['preparing','ready','submitted','strengthening','paused'])
            ->count();

        $needsFollowUp = $dueReviews > 0
            || $openErrors > 0
            || MemorizationTarget::query()->where('student_id', $student->id)->where('status', 'strengthening')->exists();

        return compact(
            'activeTargets','dueReviews','nextReview','latestMemorization','latestMurajaah',
            'openErrors','errorFocus','activeCycles','needsFollowUp'
        );
    }

    /** @param Collection<int, Student> $students @return array<string, mixed> */
    public function teacherDashboard(Collection $students): array
    {
        $ids = $students->pluck('id');
        $dueReviews = MemorizationReviewPlan::query()
            ->with(['student','surah'])
            ->whereIn('student_id', $ids)
            ->where('status', 'scheduled')
            ->whereDate('review_date', '<=', today())
            ->orderBy('review_date')
            ->limit(30)
            ->get();

        $upcomingReviews = MemorizationReviewPlan::query()
            ->with(['student','surah'])
            ->whereIn('student_id', $ids)
            ->where('status', 'scheduled')
            ->whereDate('review_date', '>', today())
            ->orderBy('review_date')
            ->limit(20)
            ->get();

        $activeCycles = TahfizhLearningCycle::query()
            ->with(['student','target.surah'])
            ->whereIn('student_id', $ids)
            ->whereIn('status', ['preparing','ready','submitted','strengthening','paused'])
            ->latest()
            ->limit(30)
            ->get();

        $recentMemorization = MemorizationRecord::query()
            ->with(['student','surah'])
            ->whereIn('student_id', $ids)
            ->latest('recorded_at')->limit(20)->get();

        $attentionIds = collect($dueReviews->pluck('student_id'))
            ->merge(QuranLearningErrorItem::query()->whereIn('student_id', $ids)->whereNull('resolved_at')->pluck('student_id'))
            ->merge(MemorizationTarget::query()->whereIn('student_id', $ids)->where('status', 'strengthening')->pluck('student_id'))
            ->unique();

        return [
            'dueReviews' => $dueReviews,
            'upcomingReviews' => $upcomingReviews,
            'activeCycles' => $activeCycles,
            'recentMemorization' => $recentMemorization,
            'attentionStudents' => $students->whereIn('id', $attentionIds)->values(),
        ];
    }
}
