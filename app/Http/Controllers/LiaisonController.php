<?php

namespace App\Http\Controllers;

use App\Models\LiaisonMessage;
use App\Models\LiaisonThread;
use App\Models\Student;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LiaisonController extends Controller
{
    public function index(Request $request): View
    {
        $query=LiaisonThread::with(['student','messages'=>fn($q)=>$q->latest()->limit(1)])
            ->where('institution_id',$request->user()->institution_id);
        if($request->user()->hasRole('teacher')){
            $query->where('assigned_teacher_id',$request->user()->teacher?->id);
        }elseif($request->user()->hasRole('guardian')){
            $studentIds=$request->user()->guardian?->students()->pluck('students.id')??collect();
            $query->whereIn('student_id',$studentIds);
        }
        return view('liaison.index',['threads'=>$query->latest('last_message_at')->paginate(20)]);
    }

    public function create(Request $request): View
    {
        if($request->user()->hasRole('teacher')){
            $teacherId=$request->user()->teacher?->id;
            $classIds=TeacherAssignment::where('teacher_id',$teacherId)->where('institution_id',$request->user()->institution_id)->where('status','active')->whereNotNull('class_id')->pluck('class_id');
            $groupIds=TeacherAssignment::where('teacher_id',$teacherId)->where('institution_id',$request->user()->institution_id)->where('status','active')->whereNotNull('learning_group_id')->pluck('learning_group_id');
            $students=Student::where('institution_id',$request->user()->institution_id)->where(function($query) use($classIds,$groupIds):void {
                $query->whereHas('enrollments',fn($q)=>$q->whereIn('class_id',$classIds)->where('status','active'))
                    ->orWhereHas('groupMemberships',fn($q)=>$q->whereIn('learning_group_id',$groupIds)->where('status','active'));
            })->orderBy('full_name')->get();
        }else{
            $students=$request->user()->guardian?->students()->orderBy('full_name')->get()??collect();
        }
        return view('liaison.create',compact('students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'student_id'=>['required','exists:students,id'],
            'category'=>['required',Rule::in(['learning','tahsin','tahfizh','murajaah','character','health','administration','consultation'])],
            'subject'=>['required','string','max:190'],
            'message'=>['required','string','max:5000'],
        ]);
        $student=Student::findOrFail($data['student_id']);
        abort_unless($student->institution_id===$request->user()->institution_id,403);
        $teacherId=null;
        if($request->user()->hasRole('teacher')){
            $teacherId=$request->user()->teacher?->id;
            abort_unless($this->teacherCanAccessStudent($teacherId,$student),403);
        }else{
            abort_unless($request->user()->guardian?->students()->whereKey($student->id)->exists(),403);
            $classId=$student->currentEnrollment?->class_id;
            $teacherId=TeacherAssignment::where('class_id',$classId)->where('status','active')->value('teacher_id');
        }

        $thread=DB::transaction(function() use($request,$data,$student,$teacherId):LiaisonThread{
            $thread=LiaisonThread::create(['institution_id'=>$student->institution_id,'student_id'=>$student->id,'class_id'=>$student->currentEnrollment?->class_id,'category'=>$data['category'],'subject'=>$data['subject'],'created_by_user_id'=>$request->user()->id,'assigned_teacher_id'=>$teacherId,'status'=>'active','last_message_at'=>now()]);
            LiaisonMessage::create(['liaison_thread_id'=>$thread->id,'sender_user_id'=>$request->user()->id,'message'=>$data['message'],'message_type'=>'text']);
            $participants=collect([$request->user()->id]);
            if($teacherId){$participants->push(optional(\App\Models\Teacher::find($teacherId))->user_id);}
            foreach($student->guardians as $guardian){$participants->push($guardian->user_id);}
            foreach($participants->filter()->unique() as $userId){DB::table('liaison_participants')->insertOrIgnore(['liaison_thread_id'=>$thread->id,'user_id'=>$userId,'participant_role'=>$userId===$request->user()->id?'creator':'participant','joined_at'=>now()]);}
            return $thread;
        });
        return redirect()->route('liaison.show',$thread)->with('success','Catatan buku penghubung berhasil dikirim.');
    }

    public function show(Request $request,LiaisonThread $thread): View
    {
        $this->authorizeThread($request,$thread);
        $thread->load(['student','messages.sender']);
        DB::table('liaison_participants')->where('liaison_thread_id',$thread->id)->where('user_id',$request->user()->id)->update(['last_read_at'=>now()]);
        return view('liaison.show',compact('thread'));
    }

    public function reply(Request $request,LiaisonThread $thread): RedirectResponse
    {
        $this->authorizeThread($request,$thread);
        $data=$request->validate(['message'=>['required','string','max:5000']]);
        LiaisonMessage::create(['liaison_thread_id'=>$thread->id,'sender_user_id'=>$request->user()->id,'message'=>$data['message'],'message_type'=>'text']);
        $thread->update(['last_message_at'=>now(),'status'=>'active']);
        return back()->with('success','Tanggapan berhasil dikirim.');
    }

    private function authorizeThread(Request $request,LiaisonThread $thread): void
    {
        abort_unless($thread->institution_id===$request->user()->institution_id,404);
        if($request->user()->hasAnyRole(['institution_admin','head','superadmin'])) return;
        if($request->user()->hasRole('teacher')) abort_unless($thread->assigned_teacher_id===$request->user()->teacher?->id,403);
        if($request->user()->hasRole('guardian')) abort_unless($request->user()->guardian?->students()->whereKey($thread->student_id)->exists(),403);
    }

    private function teacherCanAccessStudent(?int $teacherId,Student $student): bool
    {
        if(!$teacherId) return false;
        $classIds=TeacherAssignment::where('teacher_id',$teacherId)->where('institution_id',$student->institution_id)->where('status','active')->whereNotNull('class_id')->pluck('class_id');
        $groupIds=TeacherAssignment::where('teacher_id',$teacherId)->where('institution_id',$student->institution_id)->where('status','active')->whereNotNull('learning_group_id')->pluck('learning_group_id');
        return $student->enrollments()->whereIn('class_id',$classIds)->where('status','active')->exists()
            || $student->groupMemberships()->whereIn('learning_group_id',$groupIds)->where('status','active')->exists();
    }
}
