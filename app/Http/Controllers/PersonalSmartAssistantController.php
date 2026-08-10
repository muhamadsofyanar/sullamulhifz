<?php

namespace App\Http\Controllers;

/** @phase 5.2 Smart Assistant with human review */

use App\Models\AiAssistDraft;
use App\Models\UserRelationship;
use App\Services\AiAssistWorkflowService;
use App\Services\PersonalSmartAssistantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalSmartAssistantController extends Controller
{
    public function __construct(
        private readonly PersonalSmartAssistantService $assistant,
        private readonly AiAssistWorkflowService $workflow,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $hasActiveMentor = UserRelationship::query()
            ->where('relationship_type', 'mentor_learner')
            ->where('status', 'accepted')
            ->where('from_user_id', $user->id)
            ->exists();

        return view('personal.smart-assistant', [
            'snapshot' => $this->assistant->snapshot($user),
            'hasActiveMentor' => $hasActiveMentor,
            'drafts' => AiAssistDraft::query()->with('review')
                ->where('created_by_user_id', $user->id)
                ->where('purpose', 'personal_learning_guidance')
                ->latest()->limit(10)->get(),
        ]);
    }

    public function requestReview(Request $request): RedirectResponse
    {
        $user = $request->user();
        $hasActiveMentor = UserRelationship::query()
            ->where('relationship_type', 'mentor_learner')
            ->where('status', 'accepted')
            ->where('from_user_id', $user->id)
            ->exists();
        if (! $hasActiveMentor) {
            return back()->with('error', 'Hubungkan dan aktifkan Ustadz Privat terlebih dahulu sebelum meminta human review.');
        }

        $snapshot = $this->assistant->snapshot($user);

        $alreadyPending = AiAssistDraft::query()
            ->where('created_by_user_id', $user->id)
            ->where('purpose', 'personal_learning_guidance')
            ->where('status', 'pending_review')
            ->exists();
        if ($alreadyPending) {
            return back()->with('success', 'Masih ada draft yang menunggu review Ustadz.');
        }

        $this->workflow->storeDraft($user, null, [
            'purpose' => 'personal_learning_guidance',
            'evidence_snapshot' => [
                'learner_user_id' => $user->id,
                'practice_sessions' => $snapshot['practice_sessions'],
                'practice_minutes' => $snapshot['practice_minutes'],
                'active_goal_ids' => $snapshot['goals']->pluck('id')->all(),
                'active_modules' => $snapshot['modules']->all(),
            ],
            'draft_text' => $this->assistant->draftText($snapshot),
            'provider' => 'sullam-local',
            'model' => 'guidance-v1',
            'generated_at' => now(),
        ]);

        return back()->with('success', 'Draft pendampingan dibuat dan menunggu review Ustadz.');
    }
}
