<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\AssignmentRecipient;
use App\Models\AssignmentSubmission;
use App\Models\AcademyRecommendation;
use App\Models\Student;
use App\Models\MediaAsset;
use App\Services\MediaStorageService;
use App\Support\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __construct(private readonly MediaStorageService $media)
    {
    }
    public function child(Request $request, Student $student): View
    {
        $this->authorizeChild($request, $student);
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $institutionId = (int) $request->user()->institution_id;
        $academyEnabled = Feature::enabled('parent_academy', $institutionId, true);
        $quranEnabled = Feature::enabled('quran_audio', $institutionId, true);
        $reportCardsEnabled = Feature::enabled('report_cards', $institutionId, true);

        $relations = [
            'currentEnrollment.schoolClass',
            'attendanceRecords'=>fn($q)=>$q->with('meeting')->latest()->limit(30),
            'tahsinRecords'=>fn($q)=>$q->with('surah')->latest()->limit(20),
            'memorizationRecords'=>fn($q)=>$q->with(['surah','target'])->latest('recorded_at')->limit(20),
            'memorizationTargets'=>fn($q)=>$q->with(['rubu','surah','marhalah','academicYear'])->whereNotIn('status',['cancelled'])->latest()->limit(20),
            'murajaahRecords'=>fn($q)=>$q->with('surah')->latest('recorded_at')->limit(20),
        ];
        if ($reportCardsEnabled) {
            $relations['reportCards'] = fn($q) => $q->with('academicYear','items')->where('status','published')->latest('published_at');
        }
        $student->load($relations);

        $monthAttendance = $student->attendanceRecords()
            ->whereHas('meeting', fn($q)=>$q->whereBetween('meeting_date',[$monthStart,$monthEnd]))
            ->get();
        $presentCount = $monthAttendance->whereIn('status',['present','late'])->count();
        $attendancePct = $monthAttendance->count() > 0 ? (int) round(($presentCount / $monthAttendance->count()) * 100) : null;

        $publishedMeetings = \App\Models\Meeting::query()
            ->with(['schoolClass','learningGroup'])
            ->where('institution_id',$request->user()->institution_id)
            ->whereNotNull('summary_published_at')
            ->where(function($query) use($student): void {
                $classId = $student->currentEnrollment?->class_id;
                $groupIds = $student->groupMemberships()->where('status','active')->pluck('learning_group_id');
                if (! $classId && $groupIds->isEmpty()) {
                    $query->whereRaw('1 = 0');
                    return;
                }
                if ($classId) $query->where('class_id',$classId);
                if ($groupIds->isNotEmpty()) $query->orWhereIn('learning_group_id',$groupIds);
            })
            ->latest('meeting_date')->limit(10)->get();

        $monthlySummary = [
            'meetings' => $monthAttendance->pluck('meeting_id')->unique()->count(),
            'attendance_percent' => $attendancePct,
            'tahsin' => $student->tahsinRecords()->whereBetween('created_at',[$monthStart,$monthEnd])->count(),
            'memorization' => $student->memorizationRecords()->whereBetween('recorded_at',[$monthStart,$monthEnd])->count(),
            'murajaah' => $student->murajaahRecords()->whereBetween('recorded_at',[$monthStart,$monthEnd])->count(),
            'completed_tasks' => \App\Models\AssignmentRecipient::where('student_id',$student->id)->where('status','completed')->whereBetween('completed_at',[$monthStart,$monthEnd])->count(),
        ];

        $academyRecommendations = $academyEnabled
            ? AcademyRecommendation::query()
                ->with(['lesson.module.program','creator'])
                ->where('institution_id', $institutionId)
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->latest('recommended_at')
                ->limit(6)
                ->get()
            : collect();

        return view('guardian.child', compact(
            'student',
            'publishedMeetings',
            'monthlySummary',
            'academyRecommendations',
            'academyEnabled',
            'quranEnabled',
            'reportCardsEnabled',
        ));
    }

    public function tasks(Request $request): View
    {
        $studentIds=$this->studentIds($request);
        $recipients=AssignmentRecipient::with(['assignment.schoolClass','assignment.learningGroup','student','submissions'])
            ->whereIn('student_id',$studentIds)->latest()->paginate(20);
        return view('guardian.tasks.index',compact('recipients'));
    }

    public function task(Request $request, AssignmentRecipient $recipient): View
    {
        $this->authorizeRecipient($request,$recipient);
        if(!$recipient->first_viewed_at){$recipient->update(['first_viewed_at'=>now()]);}
        $recipient->load(['assignment.surah','student','submissions']);
        return view('guardian.tasks.show',compact('recipient'));
    }

    public function submit(Request $request, AssignmentRecipient $recipient): RedirectResponse
    {
        $this->authorizeRecipient($request, $recipient);
        $recipient->loadMissing('assignment');
        abort_if(in_array($recipient->status, ['completed'], true), 422, 'Tugas sudah selesai.');
        abort_if(! $recipient->assignment->allow_resubmission && $recipient->submissions()->exists(), 422, 'Tugas ini tidak menerima pengiriman ulang.');

        $maxKb = (int) config('sullam.upload_max_kb', 25600);
        $data = $request->validate([
            'guardian_notes' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp', 'mp3', 'm4a', 'wav', 'mp4', 'mov'])->max($maxKb)],
            'text_evidence' => ['nullable', 'string', 'max:5000'],
            'guardian_checklist_completed' => ['nullable', 'boolean'],
        ]);

        $requested = collect($recipient->assignment->evidence_types ?: ['none']);
        $fileType = null;
        if ($request->hasFile('evidence')) {
            $mime = (string) $request->file('evidence')->getMimeType();
            $fileType = str_starts_with($mime, 'video/')
                ? 'video'
                : (str_starts_with($mime, 'audio/') ? 'audio' : (str_starts_with($mime, 'image/') ? 'photo' : null));
            abort_unless($fileType && $requested->contains($fileType), 422, 'Jenis file tidak sesuai dengan bukti yang diminta guru.');
        }

        $hasMatchingEvidence = $fileType
            || ($requested->contains('text') && filled($data['text_evidence'] ?? null))
            || ($requested->contains('guardian_checklist') && ((bool) ($data['guardian_checklist_completed'] ?? false) || filled($data['guardian_notes'] ?? null)))
            || $requested->contains('none');
        abort_unless($hasMatchingEvidence, 422, 'Tambahkan jenis bukti yang diminta pada tugas ini.');

        $asset = $request->hasFile('evidence')
            ? $this->media->store(
                $request->file('evidence'),
                $request->user(),
                'assignments/'.$recipient->assignment_id.'/'.$recipient->student_id,
                'private',
                (int) config('sullam.media_retention_days', 180),
            )
            : null;
        $attempt = (int) $recipient->submissions()->max('attempt_number') + 1;
        $notes = trim(($data['guardian_notes'] ?? '').(filled($data['text_evidence'] ?? null) ? "

Bukti teks:
".$data['text_evidence'] : ''));

        try {
            DB::transaction(function () use ($request, $recipient, $data, $asset, $attempt, $notes): void {
                $submission = AssignmentSubmission::create([
                    'assignment_recipient_id' => $recipient->id,
                    'submitted_by_user_id' => $request->user()->id,
                    'attempt_number' => $attempt,
                    'guardian_notes' => $notes,
                    'guardian_checklist_completed' => (bool) ($data['guardian_checklist_completed'] ?? false),
                    'submitted_at' => now(),
                    'review_status' => 'pending',
                    'media_asset_id' => $asset?->id,
                    'file_path' => null,
                    'original_name' => $asset?->original_name,
                    'mime_type' => $asset?->mime_type,
                    'file_size' => $asset?->file_size,
                ]);
                if ($asset) {
                    $this->media->link($asset, $submission, 'evidence');
                }
                $recipient->update(['status' => 'submitted']);
            });
        } catch (\Throwable $exception) {
            if ($asset instanceof MediaAsset) {
                $this->media->delete($asset);
            }
            throw $exception;
        }

        return back()->with('success', 'Bukti tugas berhasil dikirim.');
    }

    private function authorizeChild(Request $request,Student $student): void
    {
        abort_unless($this->studentIds($request)->contains($student->id),403);
    }

    private function authorizeRecipient(Request $request,AssignmentRecipient $recipient): void
    {
        abort_unless($this->studentIds($request)->contains($recipient->student_id),403);
    }

    private function studentIds(Request $request)
    {
        $guardian=$request->user()->guardian;
        abort_unless($guardian,403);
        return $guardian->students()->pluck('students.id');
    }
}
