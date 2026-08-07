<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\MemorizationRecord;
use App\Models\MemorizationReviewPlan;
use App\Models\MemorizationTarget;
use App\Models\MurajaahRecord;
use App\Models\QuranLearningErrorItem;
use App\Models\TahfizhLearningCycle;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class TahfizhLearningService
{
    /**
     * A learning cycle is the bridge between a target, preparation, submission,
     * strengthening and preservation. It does not create scores or rankings.
     */
    public function resolveCycle(
        int $institutionId,
        int $studentId,
        ?Teacher $teacher,
        ?MemorizationTarget $target,
        string $cycleType,
        string $preparationMethod = 'talaqqi',
    ): TahfizhLearningCycle {
        $query = TahfizhLearningCycle::query()
            ->where('institution_id', $institutionId)
            ->where('student_id', $studentId)
            ->whereIn('status', ['preparing','ready','submitted','strengthening','paused']);

        if ($target) {
            $query->where('memorization_target_id', $target->id);
        } else {
            $query->whereNull('memorization_target_id')->where('cycle_type', $cycleType);
        }

        $cycle = $query->latest()->first();
        if ($cycle) {
            if (! $cycle->teacher_id && $teacher) {
                $cycle->update(['teacher_id' => $teacher->id]);
            }
            return $cycle;
        }

        $year = AcademicYear::query()
            ->where('institution_id', $institutionId)
            ->where('is_active', true)
            ->first();

        return TahfizhLearningCycle::create([
            'institution_id' => $institutionId,
            'academic_year_id' => $year?->id,
            'student_id' => $studentId,
            'teacher_id' => $teacher?->id,
            'memorization_target_id' => $target?->id,
            'cycle_type' => $cycleType,
            'preparation_method' => $preparationMethod,
            'status' => 'preparing',
            'started_at' => now(),
        ]);
    }

    public function applyMemorizationOutcome(TahfizhLearningCycle $cycle, MemorizationRecord $record): void
    {
        $status = match ($record->result) {
            'fluent' => 'completed',
            'fair', 'repeat_needed' => 'strengthening',
            default => 'preparing',
        };

        $cycle->update([
            'status' => $status,
            'ready_at' => $cycle->ready_at ?: now(),
            'completed_at' => $status === 'completed' ? now() : null,
        ]);
    }

    public function applyMurajaahOutcome(?TahfizhLearningCycle $cycle, MurajaahRecord $record): void
    {
        if (! $cycle) {
            return;
        }

        $status = match ($record->result) {
            'maintained' => 'completed',
            'strengthening_needed' => 'strengthening',
            default => 'preparing',
        };
        $cycle->update([
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
        ]);
    }

    public function scheduleReviewFromMemorization(MemorizationRecord $record, ?Teacher $teacher): ?MemorizationReviewPlan
    {
        if (! $record->next_review_date) {
            return null;
        }

        return MemorizationReviewPlan::updateOrCreate(
            [
                'institution_id' => $record->institution_id,
                'student_id' => $record->student_id,
                'source_memorization_record_id' => $record->id,
                'review_date' => $record->next_review_date,
            ],
            [
                'created_by_teacher_id' => $teacher?->id,
                'memorization_target_id' => $record->memorization_target_id,
                'surah_id' => $record->surah_id,
                'start_verse' => $record->start_verse,
                'end_verse' => $record->end_verse,
                'review_type' => 'scheduled',
                'priority' => in_array($record->result, ['fair','repeat_needed'], true) ? 'strengthen' : 'normal',
                'status' => 'scheduled',
                'notes' => $record->review_recommendation ?: $record->follow_up,
            ]
        );
    }

    public function scheduleNextReview(MurajaahRecord $record, ?Teacher $teacher): ?MemorizationReviewPlan
    {
        if (! $record->next_review_date) {
            return null;
        }

        return MemorizationReviewPlan::updateOrCreate(
            [
                'institution_id' => $record->institution_id,
                'student_id' => $record->student_id,
                'surah_id' => $record->surah_id,
                'start_verse' => $record->start_verse,
                'end_verse' => $record->end_verse,
                'review_date' => $record->next_review_date,
                'status' => 'scheduled',
            ],
            [
                'created_by_teacher_id' => $teacher?->id,
                'review_type' => 'scheduled',
                'priority' => match ($record->result) {
                    'reactivation_needed' => 'recall',
                    'strengthening_needed' => 'strengthen',
                    default => 'normal',
                },
                'notes' => $record->review_recommendation ?: $record->teacher_notes,
            ]
        );
    }

    public function completeReviewPlan(?MemorizationReviewPlan $plan, MurajaahRecord $record): void
    {
        if (! $plan) {
            return;
        }
        $plan->update([
            'status' => 'completed',
            'completed_by_murajaah_record_id' => $record->id,
        ]);
    }

    /** @param array<int, string> $categories */
    public function recordErrors(
        string $recordType,
        int $recordId,
        int $institutionId,
        int $studentId,
        ?int $teacherId,
        ?int $meetingId,
        array $categories,
        ?int $ayahNumber = null,
        ?string $note = null,
    ): void {
        $allowed = ['makhraj','tajwid','mad','ghunnah','waqf_ibtida','fluency','hesitation','omission','substitution','sequence','prompt_dependency','other'];
        $categories = array_values(array_unique(array_intersect($categories, $allowed)));
        if ($categories === []) {
            return;
        }

        DB::transaction(function () use ($recordType, $recordId, $institutionId, $studentId, $teacherId, $meetingId, $categories, $ayahNumber, $note): void {
            foreach ($categories as $category) {
                QuranLearningErrorItem::create([
                    'institution_id' => $institutionId,
                    'student_id' => $studentId,
                    'teacher_id' => $teacherId,
                    'meeting_id' => $meetingId,
                    'record_type' => $recordType,
                    'record_id' => $recordId,
                    'category' => $category,
                    'severity' => 'attention',
                    'ayah_number' => $ayahNumber,
                    'note' => $note,
                ]);
            }
        });
    }
}
