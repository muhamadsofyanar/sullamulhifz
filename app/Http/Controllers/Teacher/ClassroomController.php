<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningGroup;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Request $request): View
    {
        $teacher=$request->user()->teacher;
        abort_unless($teacher,403);
        $assignments=TeacherAssignment::with(['schoolClass.level','schoolClass.activeEnrollments','learningGroup.program','learningGroup.activeMemberships','program'])
            ->where('teacher_id',$teacher->id)->where('status','active')->get();
        return view('teacher.classrooms.index',compact('assignments','teacher'));
    }

    public function showClass(Request $request, SchoolClass $class): View
    {
        $assignment=$this->classAssignment($request,$class);
        $class->load(['level','activeEnrollments.student.guardians','meetings'=>fn($q)=>$q->latest('meeting_date')->limit(10)]);
        return view('teacher.classrooms.show',['target'=>$class,'targetType'=>'class','assignment'=>$assignment,'students'=>$class->activeEnrollments->pluck('student')]);
    }

    public function showGroup(Request $request, LearningGroup $group): View
    {
        $assignment=$this->groupAssignment($request,$group);
        $group->load(['program','activeMemberships.student.currentEnrollment.schoolClass']);
        return view('teacher.classrooms.show',['target'=>$group,'targetType'=>'group','assignment'=>$assignment,'students'=>$group->activeMemberships->pluck('student')]);
    }

    public static function classAssignment(Request $request, SchoolClass $class): TeacherAssignment
    {
        $teacher=$request->user()->teacher;
        abort_unless($teacher && $class->institution_id===$request->user()->institution_id,404);
        return TeacherAssignment::where('teacher_id',$teacher->id)->where('class_id',$class->id)->where('status','active')->firstOrFail();
    }

    public static function groupAssignment(Request $request, LearningGroup $group): TeacherAssignment
    {
        $teacher=$request->user()->teacher;
        abort_unless($teacher && $group->institution_id===$request->user()->institution_id,404);
        return TeacherAssignment::where('teacher_id',$teacher->id)->where('learning_group_id',$group->id)->where('status','active')->firstOrFail();
    }
}
