<?php

namespace App\Http\Controllers;

use App\Models\GuidedQuranEnrollment;
use App\Models\GuidedQuranProgram;
use App\Models\MediaAsset;
use App\Models\QuranGuidedSubmission;
use App\Models\QuranSurah;
use App\Services\MediaStorageService;
use App\Services\PersonalModuleAccessService;
use App\Services\QuranAudioSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class PersonalGuidedLearningController extends Controller
{
    public function __construct(
        private readonly MediaStorageService $media,
        private readonly PersonalModuleAccessService $personalModules,
    ) {}

    public function index(Request $request, QuranAudioSyncService $audio): View
    {
        $profile = $request->user()->personalProfile()->firstOrFail();
        $enrollments = GuidedQuranEnrollment::query()
            ->with(['program.provider', 'program.academyProgram', 'submissions.surah', 'submissions.audioAsset', 'submissions.reviews.reviewer', 'submissions.reviews.feedbackAudio'])
            ->where('learner_institution_id', $request->user()->institution_id)
            ->where('learner_user_id', $request->user()->id)
            ->latest('enrolled_at')
            ->get();

        $enrolledProgramIds = $enrollments->pluck('guided_quran_program_id');
        $programs = GuidedQuranProgram::query()
            ->with(['provider', 'academyProgram'])
            ->where('is_public', true)
            ->where('status', 'published')
            ->whereIn('delivery_mode', ['online', 'hybrid'])
            ->where(function ($query): void {
                $query->whereNull('enrollment_opens_at')->orWhere('enrollment_opens_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('enrollment_closes_at')->orWhere('enrollment_closes_at', '>=', now());
            })
            ->orderBy('title')
            ->get();

        return view('personal.guided-learning', [
            'profile' => $profile,
            'programs' => $programs,
            'enrollments' => $enrollments,
            'enrolledProgramIds' => $enrolledProgramIds,
            'surahs' => QuranSurah::query()->orderBy('id')->get(['id', 'name_latin', 'verse_count']),
            'audioSources' => collect($audio->sourceDefinitions()),
        ]);
    }

    public function enroll(Request $request, GuidedQuranProgram $program): RedirectResponse
    {
        abort_unless($program->is_public && $program->status === 'published' && in_array($program->delivery_mode, ['online', 'hybrid'], true), 404);
        abort_if($program->enrollment_opens_at && $program->enrollment_opens_at->isFuture(), 422, 'Pendaftaran program belum dibuka.');
        abort_if($program->enrollment_closes_at && $program->enrollment_closes_at->isPast(), 422, 'Pendaftaran program sudah ditutup.');

        $profile = $request->user()->personalProfile()->firstOrFail();
        GuidedQuranEnrollment::query()->updateOrCreate(
            ['guided_quran_program_id' => $program->id, 'student_id' => $profile->student_id],
            [
                'learner_institution_id' => $request->user()->institution_id,
                'learner_user_id' => $request->user()->id,
                'learner_mode' => 'personal_online',
                'status' => 'active',
                'enrolled_at' => now(),
                'completed_at' => null,
                'metadata' => ['joined_via' => 'personal_learning_hub'],
            ],
        );

        $this->personalModules->rememberDerivedEnrollment($request->user(), 'guided_learning', 'guided_program_enrollment');

        return back()->with('success', 'Program berhasil diikuti. Materi dan jalur setoran sekarang tersedia di ruang Personal Anda.');
    }

    public function submit(Request $request, GuidedQuranEnrollment $enrollment): RedirectResponse
    {
        $this->authorizeEnrollment($request, $enrollment);
        $enrollment->loadMissing('program');
        abort_unless($enrollment->status === 'active', 422, 'Program ini tidak sedang aktif.');

        $maxKb = min((int) config('sullam.upload_max_kb', 25600), 25600);
        $data = $request->validate([
            'submission_type' => ['required', Rule::in(['memorization', 'reading', 'tahsin', 'murajaah'])],
            'surah_id' => ['required', 'integer', 'exists:quran_surahs,id'],
            'start_verse' => ['required', 'integer', 'min:1'],
            'end_verse' => ['required', 'integer', 'gte:start_verse'],
            'evidence_text' => ['nullable', 'string', 'max:5000'],
            'learner_notes' => ['nullable', 'string', 'max:2000'],
            'audio_evidence' => ['nullable', File::types(['mp3', 'm4a', 'wav', 'ogg', 'opus', 'webm'])->max($maxKb)],
        ]);

        $surah = QuranSurah::query()->findOrFail($data['surah_id']);
        abort_if((int) $data['end_verse'] > (int) $surah->verse_count, 422, 'Rentang ayat melebihi jumlah ayat surah.');
        abort_if($request->hasFile('audio_evidence') && ! $enrollment->program->accepts_audio, 422, 'Program ini tidak menerima setoran audio.');
        abort_if(filled($data['evidence_text'] ?? null) && ! $enrollment->program->accepts_text, 422, 'Program ini tidak menerima setoran teks.');
        abort_unless($request->hasFile('audio_evidence') || filled($data['evidence_text'] ?? null), 422, 'Tambahkan voice note/audio atau bukti teks untuk diperiksa.');

        $asset = $request->hasFile('audio_evidence')
            ? $this->media->store(
                $request->file('audio_evidence'),
                $request->user(),
                'guided-quran/submissions/'.$enrollment->id,
                'private',
                (int) config('sullam.media_retention_days', 180),
            )
            : null;

        try {
            DB::transaction(function () use ($request, $enrollment, $data, $asset): void {
                $attempt = (int) $enrollment->submissions()->max('attempt_number') + 1;
                $submission = QuranGuidedSubmission::query()->create([
                    'guided_quran_enrollment_id' => $enrollment->id,
                    'learner_institution_id' => $request->user()->institution_id,
                    'learner_user_id' => $request->user()->id,
                    'submitted_by_user_id' => $request->user()->id,
                    'student_id' => $enrollment->student_id,
                    'surah_id' => $data['surah_id'],
                    'audio_media_asset_id' => $asset?->id,
                    'submission_type' => $data['submission_type'],
                    'start_verse' => $data['start_verse'],
                    'end_verse' => $data['end_verse'],
                    'attempt_number' => $attempt,
                    'evidence_text' => $data['evidence_text'] ?? null,
                    'learner_notes' => $data['learner_notes'] ?? null,
                    'review_status' => 'pending',
                    'submitted_at' => now(),
                ]);
                if ($asset) $this->media->link($asset, $submission, 'guided_quran_submission');
            });
        } catch (\Throwable $exception) {
            if ($asset instanceof MediaAsset) $this->media->delete($asset);
            throw $exception;
        }

        return back()->with('success', 'Setoran terkirim untuk diperiksa. Jurnal pribadi Anda tetap privat; reviewer hanya menerima bukti yang Anda kirim pada program ini.');
    }

    private function authorizeEnrollment(Request $request, GuidedQuranEnrollment $enrollment): void
    {
        abort_unless(
            (int) $enrollment->learner_institution_id === (int) $request->user()->institution_id
            && (int) $enrollment->learner_user_id === (int) $request->user()->id,
            404,
        );
    }
}
