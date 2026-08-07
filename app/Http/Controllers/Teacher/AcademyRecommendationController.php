<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademyLesson;
use App\Models\AcademyRecommendation;
use App\Models\ClassEnrollment;
use App\Models\GroupMembership;
use App\Models\Student;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademyRecommendationController extends Controller
{
    public function index(Request $request): View
    {
        $teacher=$request->user()->teacher; abort_unless($teacher,403);
        $students=$this->students($teacher->id,$request->user()->institution_id);
        $lessons=AcademyLesson::with('module.program')
            ->where('status','published')
            ->whereHas('module.program',fn($q)=>$q->where('institution_id',$request->user()->institution_id)->where('status','published')->whereIn('audience',['guardian','all']))
            ->orderBy('title')->get();
        $recommendations=AcademyRecommendation::with(['student','lesson.module.program'])
            ->where('institution_id',$request->user()->institution_id)->where('created_by_user_id',$request->user()->id)
            ->latest('recommended_at')->limit(20)->get();
        return view('teacher.academy.index',compact('students','lessons','recommendations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher=$request->user()->teacher; abort_unless($teacher,403);
        $data=$request->validate(['student_id'=>['required','integer','exists:students,id'],'academy_lesson_id'=>['required','integer','exists:academy_lessons,id'],'message'=>['nullable','string','max:1000']]);
        $students=$this->students($teacher->id,$request->user()->institution_id); abort_unless($students->contains('id',(int)$data['student_id']),403);
        $lesson=AcademyLesson::with('module.program')->findOrFail($data['academy_lesson_id']);
        abort_unless((int)$lesson->module->program->institution_id===(int)$request->user()->institution_id && in_array($lesson->module->program->audience,['guardian','all'],true),403);
        AcademyRecommendation::create([...$data,'institution_id'=>$request->user()->institution_id,'created_by_user_id'=>$request->user()->id,'status'=>'active','recommended_at'=>now()]);
        return back()->with('success','Materi Academy sudah direkomendasikan kepada wali santri.');
    }

    private function students(int $teacherId,int $institutionId)
    {
        $assignments=TeacherAssignment::where('teacher_id',$teacherId)->where('status','active')->get();
        $ids=ClassEnrollment::whereIn('class_id',$assignments->pluck('class_id')->filter())->where('status','active')->pluck('student_id')
            ->merge(GroupMembership::whereIn('learning_group_id',$assignments->pluck('learning_group_id')->filter())->where('status','active')->pluck('student_id'))->unique();
        return Student::where('institution_id',$institutionId)->whereIn('id',$ids)->where('status','active')->orderBy('full_name')->get();
    }
}
