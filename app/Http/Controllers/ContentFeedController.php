<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\FridayDevelopmentSession;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentFeedController extends Controller
{
    public function announcements(Request $request): View
    {
        $classIds=$this->classIds($request);
        $items=Announcement::with('schoolClass')->where('institution_id',$request->user()->institution_id)
            ->where('status','published')->where(fn($q)=>$q->whereNull('publish_at')->orWhere('publish_at','<=',now()))
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))
            ->where(fn($q)=>$q->whereNull('class_id')->orWhereIn('class_id',$classIds))
            ->latest('publish_at')->paginate(20);
        return view('content.announcements',compact('items'));
    }

    public function friday(Request $request): View
    {
        $classIds=$this->classIds($request);
        $items=FridayDevelopmentSession::with(['schoolClass','surah'])->where('institution_id',$request->user()->institution_id)
            ->where('status','published')->where(fn($q)=>$q->whereNull('class_id')->orWhereIn('class_id',$classIds))
            ->latest('session_date')->paginate(20);
        return view('content.friday',compact('items'));
    }

    private function classIds(Request $request)
    {
        if($request->user()->hasRole('guardian')) return $request->user()->guardian?->students()->with('currentEnrollment')->get()->pluck('currentEnrollment.class_id')->filter()??collect();
        if($request->user()->hasRole('teacher')) return TeacherAssignment::where('teacher_id',$request->user()->teacher?->id)->where('status','active')->pluck('class_id')->filter();
        return \App\Models\SchoolClass::where('institution_id',$request->user()->institution_id)->pluck('id');
    }
}
