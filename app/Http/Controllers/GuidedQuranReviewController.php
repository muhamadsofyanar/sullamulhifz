<?php

namespace App\Http\Controllers;

use App\Models\GuidedQuranProgram;
use App\Models\GuidedQuranProgramReviewer;
use App\Models\MediaAsset;
use App\Models\QuranGuidedSubmission;
use App\Models\QuranGuidedSubmissionReview;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class GuidedQuranReviewController extends Controller
{
    public function __construct(private readonly MediaStorageService $media) {}

    public function index(Request $request): View
    {
        $programIds = $this->reviewableProgramIds($request);
        $submissions = QuranGuidedSubmission::query()
            ->with(['enrollment.program.provider', 'student', 'surah', 'learner', 'audioAsset', 'reviews.reviewer', 'reviews.feedbackAudio'])
            ->whereHas('enrollment', fn ($q) => $q->whereIn('guided_quran_program_id', $programIds))
            ->orderByRaw("CASE review_status WHEN 'pending' THEN 0 WHEN 'revision' THEN 1 ELSE 2 END")
            ->latest('submitted_at')
            ->paginate(30);

        return view('guided-review.index', compact('submissions'));
    }

    public function review(Request $request, QuranGuidedSubmission $submission): RedirectResponse
    {
        $submission->loadMissing('enrollment.program');
        abort_unless($this->reviewableProgramIds($request)->contains($submission->enrollment->guided_quran_program_id), 404);

        $maxKb = min((int) config('sullam.upload_max_kb', 25600), 25600);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['verified', 'revision', 'rejected'])],
            'feedback_text' => ['nullable', 'string', 'max:5000'],
            'feedback_audio' => ['nullable', File::types(['mp3', 'm4a', 'wav', 'ogg', 'opus', 'webm'])->max($maxKb)],
        ]);
        abort_unless(filled($data['feedback_text'] ?? null) || $request->hasFile('feedback_audio'), 422, 'Berikan catatan teks atau voice note agar keputusan review dapat dipahami santri.');

        $asset = $request->hasFile('feedback_audio')
            ? $this->media->store(
                $request->file('feedback_audio'),
                $request->user(),
                'guided-quran/feedback/'.$submission->id,
                'private',
                (int) config('sullam.media_retention_days', 180),
            )
            : null;

        try {
            DB::transaction(function () use ($request, $submission, $data, $asset): void {
                $review = QuranGuidedSubmissionReview::query()->create([
                    'quran_guided_submission_id' => $submission->id,
                    'reviewer_user_id' => $request->user()->id,
                    'reviewer_teacher_id' => $request->user()->teacher?->id,
                    'feedback_audio_media_asset_id' => $asset?->id,
                    'decision' => $data['decision'],
                    'feedback_text' => $data['feedback_text'] ?? null,
                ]);
                if ($asset) $this->media->link($asset, $review, 'guided_quran_feedback');
                $submission->update(['review_status' => $data['decision'], 'last_reviewed_at' => now()]);
            });
        } catch (\Throwable $exception) {
            if ($asset instanceof MediaAsset) $this->media->delete($asset);
            throw $exception;
        }

        return back()->with('success', $data['decision'] === 'verified'
            ? 'Setoran diverifikasi. Feedback tersimpan sebagai bagian riwayat pembelajaran.'
            : 'Review tersimpan. Santri dapat melihat koreksi dan mengirim setoran baru.');
    }

    private function reviewableProgramIds(Request $request)
    {
        $user = $request->user();
        if ($user->hasRole('superadmin') && $user->institution_id === null) {
            return GuidedQuranProgram::query()->where('status', 'published')->pluck('id');
        }

        $ids = GuidedQuranProgramReviewer::query()
            ->where('reviewer_user_id', $user->id)
            ->where('status', 'active')
            ->pluck('guided_quran_program_id');

        if ($user->hasAnyRole(['institution_admin', 'head'])) {
            $owned = GuidedQuranProgram::query()->where('provider_institution_id', $user->institution_id)->pluck('id');
            $ids = $ids->merge($owned);
        }

        return $ids->unique()->values();
    }
}
