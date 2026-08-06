<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentRecipient;
use App\Models\AssignmentSubmission;
use App\Models\LearningGroup;
use App\Models\QuranSurah;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $teacher=$request->user()->teacher;
        $assignments=Assignment::with(['schoolClass','learningGroup','recipients'])
            ->where('created_by_teacher_id',$teacher->id)->latest()->paginate(15);
        return view('teacher.assignments.index',compact('assignments'));
    }

    public function create(Request $request): View
    {
        $teacher=$request->user()->teacher;
        $teaching=TeacherAssignment::with(['schoolClass','learningGroup'])->where('teacher_id',$teacher->id)->where('status','active')->get();
        return view('teacher.assignments.create',['teaching'=>$teaching,'surahs'=>QuranSurah::orderBy('id')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher=$request->user()->teacher;
        $data=$request->validate([
            'target_type'=>['required',Rule::in(['class','group'])],
            'target_id'=>['required','integer'],
            'title'=>['required','string','max:190'],
            'target_text'=>['required','string'],
            'surah_id'=>['nullable','exists:quran_surahs,id'],
            'start_verse'=>['nullable','integer','min:1'],
            'end_verse'=>['nullable','integer','gte:start_verse'],
            'learning_method'=>['required','string','max:100'],
            'instructions'=>['required','string'],
            'evidence_types'=>['nullable','array'],
            'evidence_types.*'=>['string',Rule::in(['video','audio','photo','text','guardian_checklist','none'])],
            'due_at'=>['nullable','date','after:now'],
            'allow_resubmission'=>['nullable','boolean'],
        ]);
        [$target,$teacherAssignment]=$this->resolveTarget($request,$data['target_type'],$data['target_id']);
        $year=AcademicYear::where('institution_id',$request->user()->institution_id)->where('is_active',true)->firstOrFail();
        $assignment=DB::transaction(function() use($data,$target,$teacherAssignment,$teacher,$year):Assignment{
            $assignment=Assignment::create([
                'institution_id'=>$teacher->institution_id,'academic_year_id'=>$year->id,'created_by_teacher_id'=>$teacher->id,
                'class_id'=>$data['target_type']==='class'?$target->id:null,'learning_group_id'=>$data['target_type']==='group'?$target->id:null,
                'title'=>$data['title'],'target_text'=>$data['target_text'],'surah_id'=>$data['surah_id']??null,'start_verse'=>$data['start_verse']??null,'end_verse'=>$data['end_verse']??null,
                'learning_method'=>$data['learning_method'],'instructions'=>$data['instructions'],'evidence_types'=>$data['evidence_types']??['none'],'assigned_at'=>now(),'due_at'=>$data['due_at']??null,
                'allow_resubmission'=>(bool)($data['allow_resubmission']??true),'status'=>'published',
            ]);
            $studentIds=$data['target_type']==='class'?$target->activeEnrollments()->pluck('student_id'):$target->activeMemberships()->pluck('student_id');
            foreach($studentIds as $studentId){AssignmentRecipient::create(['assignment_id'=>$assignment->id,'student_id'=>$studentId,'recipient_source'=>$data['target_type'],'status'=>'assigned']);}
            return $assignment;
        });
        return redirect()->route('teacher.assignments.show',$assignment)->with('success','Tugas berhasil diterbitkan kepada '.$assignment->recipients()->count().' santri.');
    }

    public function show(Request $request,Assignment $assignment): View
    {
        $this->authorizeAssignment($request,$assignment);
        $assignment->load(['schoolClass','learningGroup','surah','recipients.student','recipients.submissions']);
        return view('teacher.assignments.show',compact('assignment'));
    }

    public function review(Request $request,AssignmentSubmission $submission): RedirectResponse
    {
        $submission->load('recipient.assignment');
        $this->authorizeAssignment($request,$submission->recipient->assignment);
        $data=$request->validate(['review_status'=>['required',Rule::in(['accepted','revision_needed','completed'])],'teacher_feedback'=>['required','string']]);
        $submission->update([...$data,'reviewed_by_teacher_id'=>$request->user()->teacher->id,'reviewed_at'=>now()]);
        $recipientStatus=$data['review_status']==='accepted'?'accepted':$data['review_status'];
        $submission->recipient->update(['status'=>$recipientStatus,'completed_at'=>$data['review_status']==='completed'?now():null]);
        return back()->with('success','Tanggapan guru berhasil dikirim.');
    }

    private function resolveTarget(Request $request,string $type,int $id): array
    {
        $teacher=$request->user()->teacher;
        if($type==='class'){
            $target=SchoolClass::findOrFail($id);
            $assignment=TeacherAssignment::where('teacher_id',$teacher->id)->where('class_id',$id)->where('status','active')->firstOrFail();
            return [$target,$assignment];
        }
        $target=LearningGroup::findOrFail($id);
        $assignment=TeacherAssignment::where('teacher_id',$teacher->id)->where('learning_group_id',$id)->where('status','active')->firstOrFail();
        return [$target,$assignment];
    }

    private function authorizeAssignment(Request $request,Assignment $assignment): void
    {
        abort_unless($request->user()->teacher && $assignment->created_by_teacher_id===$request->user()->teacher->id,403);
    }
}
