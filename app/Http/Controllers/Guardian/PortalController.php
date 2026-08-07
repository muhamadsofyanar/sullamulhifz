<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\AssignmentRecipient;
use App\Models\AssignmentSubmission;
use App\Models\AcademyRecommendation;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function child(Request $request, Student $student): View
    {
        $this->authorizeChild($request, $student);
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $student->load([
            'currentEnrollment.schoolClass',
            'attendanceRecords'=>fn($q)=>$q->with('meeting')->latest()->limit(30),
            'tahsinRecords'=>fn($q)=>$q->with('surah')->latest()->limit(20),
            'memorizationRecords'=>fn($q)=>$q->with(['surah','target'])->latest('recorded_at')->limit(20),
            'memorizationTargets'=>fn($q)=>$q->with(['rubu','surah','marhalah','academicYear'])->whereNotIn('status',['cancelled'])->latest()->limit(20),
            'murajaahRecords'=>fn($q)=>$q->with('surah')->latest('recorded_at')->limit(20),
            'reportCards'=>fn($q)=>$q->with('academicYear','items')->where('status','published')->latest('published_at'),
        ]);

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

        $academyRecommendations = AcademyRecommendation::query()
            ->with(['lesson.module.program','creator'])
            ->where('institution_id', $request->user()->institution_id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->latest('recommended_at')
            ->limit(6)
            ->get();

        return view('guardian.child',compact('student','publishedMeetings','monthlySummary','academyRecommendations'));
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
        $this->authorizeRecipient($request,$recipient);
        $recipient->loadMissing('assignment');
        abort_if(in_array($recipient->status,['completed']),422,'Tugas sudah selesai.');
        abort_if(! $recipient->assignment->allow_resubmission && $recipient->submissions()->exists(),422,'Tugas ini tidak menerima pengiriman ulang.');
        $maxKb=config('sullam.upload_max_kb',25600);
        $data=$request->validate([
            'guardian_notes'=>['nullable','string','max:2000'],
            'evidence'=>['nullable','file','max:'.$maxKb,'mimetypes:video/mp4,video/quicktime,audio/mpeg,audio/mp4,audio/wav,image/jpeg,image/png,image/webp'],
            'text_evidence'=>['nullable','string','max:5000'],
            'guardian_checklist_completed'=>['nullable','boolean'],
        ]);

        $requested=collect($recipient->assignment->evidence_types ?: ['none']);
        $fileType=null;
        if($request->hasFile('evidence')){
            $mime=(string)$request->file('evidence')->getMimeType();
            $fileType=str_starts_with($mime,'video/')?'video':(str_starts_with($mime,'audio/')?'audio':(str_starts_with($mime,'image/')?'photo':null));
            abort_unless($fileType && $requested->contains($fileType),422,'Jenis file tidak sesuai dengan bukti yang diminta guru.');
        }
        $hasMatchingEvidence=$fileType
            || ($requested->contains('text') && filled($data['text_evidence']??null))
            || ($requested->contains('guardian_checklist') && ((bool)($data['guardian_checklist_completed']??false) || filled($data['guardian_notes']??null)))
            || $requested->contains('none');
        abort_unless($hasMatchingEvidence,422,'Tambahkan jenis bukti yang diminta pada tugas ini.');

        $attempt=(int)$recipient->submissions()->max('attempt_number')+1;
        $fileData=['file_path'=>null,'original_name'=>null,'mime_type'=>null,'file_size'=>null];
        if($request->hasFile('evidence')){
            $file=$request->file('evidence');
            $path=$file->store('assignments/'.$recipient->assignment_id.'/'.$recipient->student_id,'local');
            $fileData=['file_path'=>$path,'original_name'=>$file->getClientOriginalName(),'mime_type'=>$file->getMimeType(),'file_size'=>$file->getSize()];
        }
        $notes=trim(($data['guardian_notes']??'').(filled($data['text_evidence']??null)?"\n\nBukti teks:\n".$data['text_evidence']:''));
        AssignmentSubmission::create([...$fileData,'assignment_recipient_id'=>$recipient->id,'submitted_by_user_id'=>$request->user()->id,'attempt_number'=>$attempt,'guardian_notes'=>$notes,'guardian_checklist_completed'=>(bool)($data['guardian_checklist_completed']??false),'submitted_at'=>now(),'review_status'=>'pending']);
        $recipient->update(['status'=>'submitted']);
        return back()->with('success','Bukti tugas berhasil dikirim.');
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
