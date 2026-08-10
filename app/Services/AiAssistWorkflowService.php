<?php

namespace App\Services;

/** @phase 5.2 Smart Assistant cross-workspace human review guardrail */

use App\Models\ActivityLog;
use App\Models\AiAssistDraft;
use App\Models\AiAssistReview;
use App\Models\Student;
use App\Models\User;
use App\Models\UserRelationship;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AiAssistWorkflowService
{
    public function storeDraft(User $actor, ?Student $student, array $payload): AiAssistDraft
    {
        if ($student) {
            abort_unless((int) $student->institution_id === (int) $actor->institution_id, 403);
        }

        return AiAssistDraft::create([
            'institution_id' => $actor->institution_id,
            'student_id' => $student?->id,
            'created_by_user_id' => $actor->id,
            'purpose' => $payload['purpose'],
            'evidence_snapshot' => $payload['evidence_snapshot'] ?? [],
            'draft_text' => $payload['draft_text'],
            'provider' => $payload['provider'] ?? null,
            'model' => $payload['model'] ?? null,
            'status' => 'pending_review',
            'generated_at' => $payload['generated_at'] ?? now(),
        ]);
    }

    public function review(AiAssistDraft $draft, User $reviewer, string $decision, ?string $finalText = null, ?string $note = null): AiAssistReview
    {
        $sameInstitution = (int) $draft->institution_id === (int) $reviewer->institution_id;
        $learnerUserId = (int) data_get($draft->evidence_snapshot, 'learner_user_id', 0);
        $consentedMentorReview = $draft->purpose === 'personal_learning_guidance'
            && $learnerUserId > 0
            && (int) $draft->created_by_user_id === $learnerUserId
            && UserRelationship::query()
                ->where('relationship_type', 'mentor_learner')
                ->where('status', 'accepted')
                ->where('from_user_id', $learnerUserId)
                ->where('to_user_id', $reviewer->id)
                ->exists();
        abort_unless($sameInstitution || $consentedMentorReview, 403);

        if (! in_array($decision, ['accepted', 'modified', 'rejected'], true)) {
            throw ValidationException::withMessages(['decision' => 'Keputusan review tidak dikenal.']);
        }
        if ($decision === 'modified' && blank($finalText)) {
            throw ValidationException::withMessages(['final_text' => 'Teks final wajib diisi ketika draft diubah.']);
        }

        return DB::transaction(function () use ($draft, $reviewer, $decision, $finalText, $note): AiAssistReview {
            $review = AiAssistReview::updateOrCreate(
                ['ai_assist_draft_id' => $draft->id],
                [
                    'institution_id' => $draft->institution_id,
                    'reviewer_user_id' => $reviewer->id,
                    'decision' => $decision,
                    'final_text' => $decision === 'accepted' ? $draft->draft_text : ($decision === 'modified' ? $finalText : null),
                    'review_note' => $note,
                    'reviewed_at' => now(),
                ],
            );

            $draft->update(['status' => match ($decision) {
                'accepted', 'modified' => 'approved',
                'rejected' => 'rejected',
            }]);

            ActivityLog::create([
                'institution_id' => $draft->institution_id,
                'user_id' => $reviewer->id,
                'action' => 'ai_assist.reviewed',
                'subject_type' => 'ai_assist_draft',
                'subject_id' => $draft->id,
                'new_values' => ['decision' => $decision],
                'reason' => $note,
                'created_at' => now(),
            ]);

            return $review;
        });
    }
}
