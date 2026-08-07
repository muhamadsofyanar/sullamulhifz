<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AssignmentRecipient;
use App\Models\Meeting;
use App\Models\Schedule;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyOperationsController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403, 'Profil guru belum terhubung.');

        $assignments = TeacherAssignment::with(['schoolClass', 'learningGroup', 'program'])
            ->where('institution_id', $request->user()->institution_id)
            ->where('teacher_id', $teacher->id)
            ->currentlyActive()
            ->get();

        $targetClassIds = $assignments->pluck('class_id')->filter();
        $targetGroupIds = $assignments->pluck('learning_group_id')->filter();

        $todayMeetings = Meeting::with(['schoolClass', 'learningGroup', 'attendanceRecords'])
            ->where('institution_id', $request->user()->institution_id)
            ->where('teacher_id', $teacher->id)
            ->whereDate('meeting_date', today())
            ->latest('started_at')
            ->get();

        $openMeetings = Meeting::with(['schoolClass', 'learningGroup'])
            ->where('institution_id', $request->user()->institution_id)
            ->where('teacher_id', $teacher->id)
            ->whereIn('status', ['draft', 'ongoing'])
            ->latest('meeting_date')
            ->limit(10)
            ->get();

        $todaySchedules = Schedule::with(['schoolClass', 'learningGroup', 'program'])
            ->where('institution_id', $teacher->institution_id)
            ->where('status', 'active')
            ->where('day_of_week', now()->dayOfWeekIso)
            ->where(function ($query) use ($targetClassIds, $targetGroupIds): void {
                $query->whereIn('class_id', $targetClassIds)
                    ->orWhereIn('learning_group_id', $targetGroupIds);
            })
            ->orderBy('start_time')
            ->get();

        $pendingSubmissions = AssignmentRecipient::with(['assignment', 'student'])
            ->whereHas('assignment', fn ($query) => $query
                ->where('institution_id', $request->user()->institution_id)
                ->where('created_by_teacher_id', $teacher->id))
            ->whereIn('status', ['submitted', 'reviewing', 'revision_needed'])
            ->latest()
            ->limit(8)
            ->get();

        return view('teacher.daily.index', compact(
            'teacher', 'assignments', 'todayMeetings', 'openMeetings', 'todaySchedules', 'pendingSubmissions'
        ));
    }
}
