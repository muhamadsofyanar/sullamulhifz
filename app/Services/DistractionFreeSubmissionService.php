<?php

namespace App\Services;

/** @phase 6.0 Distraction-free submission transaction boundary */

use App\Models\Meeting;
use App\Models\MemorizationRecord;
use App\Models\MemorizationReviewPlan;
use App\Models\MemorizationTarget;
use App\Models\MurajaahRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DistractionFreeSubmissionService
{
    public function __construct(
        private readonly TahfizhLearningService $learning,
        private readonly QuranJourneyService $journey,
    ) {}

    /** @param array<string, mixed> $data */
    public function recordMemorization(User $actor, Student $student, array $data, ?Meeting $meeting = null): MemorizationRecord
    {
        return DB::transaction(function () use ($actor, $student, $data, $meeting): MemorizationRecord {
            Student::query()
                ->where('institution_id', $actor->institution_id)
                ->lockForUpdate()
                ->findOrFail($student->id);
            $existing = MemorizationRecord::query()
                ->where('institution_id', $actor->institution_id)
                ->where('student_id', $student->id)
                ->where('submission_key', $data['submission_key'])
                ->first();
            if ($existing) {
                return $existing;
            }

            $teacher = $actor->teacher;
            abort_unless($teacher, 403);
            $target = $this->target($actor, $student, $data);
            $recordType = $target && in_array($target->target_type, [
                'new_memorization', 'initial_repetition', 'tasmi', 'exam',
            ], true) ? $target->target_type : 'class_submission';

            $cycle = $this->learning->resolveCycle(
                (int) $actor->institution_id,
                (int) $student->id,
                $teacher,
                $target,
                in_array($recordType, ['tasmi', 'exam'], true) ? $recordType : 'new_memorization',
                'reading_repetition',
            );

            $decision = (string) $data['daily_decision'];
            $note = $this->note($data['short_note'] ?? null);
            $record = MemorizationRecord::create([
                'institution_id' => $actor->institution_id,
                'meeting_id' => $meeting?->id,
                'memorization_target_id' => $target?->id,
                'learning_cycle_id' => $cycle->id,
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'marhalah_type_id' => $target?->marhalah_type_id,
                'record_type' => $recordType,
                'delivery_mode' => 'individual_submission',
                'surah_id' => $data['surah_id'],
                'start_verse' => $data['start_verse'],
                'end_verse' => $data['end_verse'],
                'result' => match ($decision) {
                    'lanjut' => 'fluent',
                    'kuatkan' => 'fair',
                    default => 'repeat_needed',
                },
                'daily_decision' => $decision,
                'short_note' => $note,
                'submission_key' => $data['submission_key'],
                'assistance_level' => 'none',
                'follow_up' => $note,
                'review_recommendation' => $note,
                'next_review_date' => $this->nextReviewDate($decision),
                'teacher_notes' => $note,
                'recorded_at' => now(),
            ]);

            $this->learning->applyMemorizationOutcome($cycle, $record);
            $this->learning->scheduleReviewFromMemorization($record, $teacher);

            if ($target) {
                $status = match ($decision) {
                    'lanjut' => 'completed',
                    'kuatkan' => 'in_progress',
                    default => 'strengthening',
                };
                $target->update([
                    'status' => $status,
                    'completed_at' => $status === 'completed' ? now() : null,
                ]);
                $this->journey->refreshPortionForTarget($target->fresh());
            }

            return $record;
        }, 3);
    }

    /** @param array<string, mixed> $data */
    public function recordMurajaah(User $actor, Student $student, array $data, ?Meeting $meeting = null): MurajaahRecord
    {
        return DB::transaction(function () use ($actor, $student, $data, $meeting): MurajaahRecord {
            Student::query()
                ->where('institution_id', $actor->institution_id)
                ->lockForUpdate()
                ->findOrFail($student->id);
            $existing = MurajaahRecord::query()
                ->where('institution_id', $actor->institution_id)
                ->where('student_id', $student->id)
                ->where('submission_key', $data['submission_key'])
                ->first();
            if ($existing) {
                return $existing;
            }

            $teacher = $actor->teacher;
            abort_unless($teacher, 403);
            $reviewPlan = null;
            if (! empty($data['review_plan_id'])) {
                $reviewPlan = MemorizationReviewPlan::query()
                    ->where('institution_id', $actor->institution_id)
                    ->where('student_id', $student->id)
                    ->where('status', 'scheduled')
                    ->findOrFail($data['review_plan_id']);
                abort_if(
                    (int) $reviewPlan->surah_id !== (int) $data['surah_id']
                    || (int) $reviewPlan->start_verse !== (int) $data['start_verse']
                    || (int) $reviewPlan->end_verse !== (int) $data['end_verse'],
                    422,
                    'Jadwal Murāja‘ah tidak sama dengan bagian yang dicatat.',
                );
            }

            $target = MemorizationTarget::query()
                ->where('institution_id', $actor->institution_id)
                ->where('student_id', $student->id)
                ->where('surah_id', $data['surah_id'])
                ->where('start_verse', $data['start_verse'])
                ->where('end_verse', $data['end_verse'])
                ->whereIn('status', ['active', 'in_progress', 'strengthening', 'paused', 'completed'])
                ->latest()
                ->first();
            $cycle = $this->learning->resolveCycle(
                (int) $actor->institution_id,
                (int) $student->id,
                $teacher,
                $target,
                'murajaah',
                'reading_repetition',
            );

            $decision = (string) $data['daily_decision'];
            $note = $this->note($data['short_note'] ?? null);
            $record = MurajaahRecord::create([
                'institution_id' => $actor->institution_id,
                'meeting_id' => $meeting?->id,
                'learning_cycle_id' => $cycle->id,
                'review_plan_id' => $reviewPlan?->id,
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'murajaah_type' => $reviewPlan?->review_type ?: 'scheduled',
                'surah_id' => $data['surah_id'],
                'start_verse' => $data['start_verse'],
                'end_verse' => $data['end_verse'],
                'result' => match ($decision) {
                    'lanjut' => 'maintained',
                    'kuatkan' => 'strengthening_needed',
                    default => 'reactivation_needed',
                },
                'daily_decision' => $decision,
                'short_note' => $note,
                'submission_key' => $data['submission_key'],
                'assistance_level' => 'none',
                'next_review_date' => $this->nextReviewDate($decision),
                'review_recommendation' => $note,
                'teacher_notes' => $note,
                'recorded_at' => now(),
            ]);

            $this->learning->completeReviewPlan($reviewPlan, $record);
            $this->learning->scheduleNextReview($record, $teacher);
            $this->learning->applyMurajaahOutcome($cycle, $record);

            if ($target) {
                $target->update([
                    'status' => $decision === 'lanjut' ? 'completed' : 'strengthening',
                    'completed_at' => $decision === 'lanjut' ? ($target->completed_at ?: now()) : null,
                ]);
                $this->journey->refreshPortionForTarget($target->fresh());
            }

            return $record;
        }, 3);
    }

    /** @param array<string, mixed> $data */
    private function target(User $actor, Student $student, array $data): ?MemorizationTarget
    {
        $target = null;
        if (! empty($data['memorization_target_id'])) {
            $target = MemorizationTarget::query()
                ->where('institution_id', $actor->institution_id)
                ->where('student_id', $student->id)
                ->findOrFail($data['memorization_target_id']);
            abort_if(
                (int) $target->surah_id !== (int) $data['surah_id']
                || (int) $target->start_verse !== (int) $data['start_verse']
                || (int) $target->end_verse !== (int) $data['end_verse'],
                422,
                'Target tidak sama dengan bagian yang dicatat.',
            );
        }

        return $target ?: MemorizationTarget::query()
            ->where('institution_id', $actor->institution_id)
            ->where('student_id', $student->id)
            ->where('surah_id', $data['surah_id'])
            ->where('start_verse', $data['start_verse'])
            ->where('end_verse', $data['end_verse'])
            ->whereIn('status', ['active', 'in_progress', 'strengthening', 'paused'])
            ->latest()
            ->first();
    }

    private function nextReviewDate(string $decision): string
    {
        return match ($decision) {
            'lanjut' => today()->addDays(7)->toDateString(),
            'kuatkan' => today()->addDays(2)->toDateString(),
            default => today()->addDay()->toDateString(),
        };
    }

    private function note(mixed $value): ?string
    {
        $note = trim((string) $value);

        return $note === '' ? null : $note;
    }
}
