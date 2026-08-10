<?php

namespace App\Http\Controllers\Teacher;

/** @phase 5.2 Smart Assistant with human review */

use App\Http\Controllers\Controller;
use App\Models\AiAssistDraft;
use App\Models\UserRelationship;
use App\Services\AiAssistWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmartAssistantReviewController extends Controller
{
    public function __construct(private readonly AiAssistWorkflowService $workflow) {}

    public function index(Request $request): View
    {
        $teacher = $request->user();
        $learnerIds = UserRelationship::query()
            ->where('relationship_type', 'mentor_learner')
            ->where('status', 'accepted')
            ->where('to_user_id', $teacher->id)
            ->pluck('from_user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $drafts = AiAssistDraft::query()->with(['creator', 'review'])
            ->where('purpose', 'personal_learning_guidance')
            ->whereIn('created_by_user_id', $learnerIds)
            ->latest()->limit(100)->get()
            ->filter(function (AiAssistDraft $draft): bool {
                return (int) data_get($draft->evidence_snapshot, 'learner_user_id', 0) === (int) $draft->created_by_user_id;
            })->values();

        return view('teacher.smart-assistant.index', ['drafts' => $drafts]);
    }

    public function review(Request $request, AiAssistDraft $draft): RedirectResponse
    {
        $teacher = $request->user();
        abort_unless($draft->purpose === 'personal_learning_guidance', 404);
        $learnerId = (int) data_get($draft->evidence_snapshot, 'learner_user_id', 0);
        abort_unless($learnerId > 0 && (int) $draft->created_by_user_id === $learnerId, 403);
        abort_unless(UserRelationship::query()
            ->where('relationship_type', 'mentor_learner')
            ->where('status', 'accepted')
            ->where('from_user_id', $learnerId)
            ->where('to_user_id', $teacher->id)
            ->exists(), 403);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['accepted', 'modified', 'rejected'])],
            'final_text' => ['nullable', 'string', 'max:10000'],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->workflow->review(
            $draft,
            $teacher,
            $data['decision'],
            $data['final_text'] ?? null,
            $data['review_note'] ?? null,
        );

        return back()->with('success', 'Keputusan review tersimpan dan tercatat di audit.');
    }
}
