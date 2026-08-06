<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\FridayDevelopmentSession;
use App\Models\TeacherAssignment;
use App\Support\StudentPledge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class ContentFeedController extends Controller
{
    public function announcements(Request $request): View
    {
        $classIds = $this->classIds($request);
        if (! Schema::hasColumn('announcements','audience_type')) {
            $items=Announcement::with('schoolClass')->where('institution_id',$request->user()->institution_id)
                ->where('status','published')->where(fn($q)=>$q->whereNull('publish_at')->orWhere('publish_at','<=',now()))
                ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))
                ->where(fn($q)=>$q->whereNull('class_id')->orWhereIn('class_id',$classIds))->latest('publish_at')->paginate(20);
            return view('content.announcements',compact('items'));
        }

        $groupIds = $this->groupIds($request);
        $roleAudience = $request->user()->hasRole('guardian') ? 'guardians' : ($request->user()->hasRole('teacher') ? 'teachers' : 'admins');
        $items = Announcement::with(['schoolClass','learningGroup','reads'=>fn($q)=>$q->where('user_id',$request->user()->id)])
            ->where('institution_id',$request->user()->institution_id)->where('status','published')
            ->where(fn($q)=>$q->whereNull('publish_at')->orWhere('publish_at','<=',now()))
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))
            ->where(function ($query) use ($classIds,$groupIds,$roleAudience):void {
                $query->where('audience_type','all')->orWhere('audience_type',$roleAudience)
                    ->orWhere(fn($q)=>$q->where('audience_type','class')->whereIn('class_id',$classIds))
                    ->orWhere(fn($q)=>$q->where('audience_type','group')->whereIn('learning_group_id',$groupIds));
            })->orderByDesc('is_pinned')->latest('publish_at')->paginate(20);
        foreach($items as $item) AnnouncementRead::firstOrCreate(['announcement_id'=>$item->id,'user_id'=>$request->user()->id],['read_at'=>now()]);
        return view('content.announcements',compact('items'));
    }

    public function acknowledge(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless(Schema::hasTable('announcement_reads'),503,'Fitur konfirmasi sedang disiapkan.');
        abort_unless($announcement->institution_id === $request->user()->institution_id, 404);
        AnnouncementRead::updateOrCreate(
            ['announcement_id'=>$announcement->id,'user_id'=>$request->user()->id],
            ['read_at'=>now(),'acknowledged_at'=>now()]
        );
        return back()->with('success','Pengumuman telah dikonfirmasi dibaca.');
    }

    public function friday(Request $request): View
    {
        $classIds=$this->classIds($request);
        $items=FridayDevelopmentSession::with(['schoolClass','surah'])->where('institution_id',$request->user()->institution_id)
            ->where('status','published')->where(fn($q)=>$q->whereNull('class_id')->orWhereIn('class_id',$classIds))
            ->latest('session_date')->paginate(20);
        return view('content.friday',compact('items'));
    }

    public function pledge(Request $request): View
    {
        return view('content.pledge', [
            'pledge' => StudentPledge::forInstitution($request->user()->institution_id),
        ]);
    }

    private function classIds(Request $request)
    {
        if($request->user()->hasRole('guardian')) return $request->user()->guardian?->students()->with('currentEnrollment')->get()->pluck('currentEnrollment.class_id')->filter()??collect();
        if($request->user()->hasRole('teacher')) return TeacherAssignment::where('teacher_id',$request->user()->teacher?->id)->where('status','active')->pluck('class_id')->filter();
        return \App\Models\SchoolClass::where('institution_id',$request->user()->institution_id)->pluck('id');
    }

    private function groupIds(Request $request)
    {
        if ($request->user()->hasRole('guardian')) {
            return $request->user()->guardian?->students()->with('groupMemberships')->get()->flatMap(fn($student)=>$student->groupMemberships->where('status','active')->pluck('learning_group_id'))->unique() ?? collect();
        }
        if ($request->user()->hasRole('teacher')) {
            return TeacherAssignment::where('teacher_id',$request->user()->teacher?->id)->where('status','active')->pluck('learning_group_id')->filter();
        }
        return \App\Models\LearningGroup::where('institution_id',$request->user()->institution_id)->pluck('id');
    }
}
