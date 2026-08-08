<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AcademyProgram;
use App\Models\AcademyRecommendation;
use App\Models\AdmissionRegistration;
use App\Models\AssignmentRecipient;
use App\Models\AttendanceRecord;
use App\Models\Guardian;
use App\Models\LearningGroup;
use App\Models\FridayDevelopmentSession;
use App\Models\MemorizationTarget;
use App\Models\QuranPracticeSession;
use App\Models\Schedule;
use App\Models\LiaisonThread;
use App\Models\Meeting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Services\ContentAudienceService;
use App\Support\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ContentAudienceService $audience,
    ) {
    }

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load('roles', 'teacher', 'guardian.students.currentEnrollment.schoolClass');

        if ($user->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            return $this->admin($request);
        }

        if ($user->hasRole('teacher')) {
            return $this->teacher($request);
        }

        if ($user->hasRole('guardian')) {
            return $this->guardian($request);
        }

        abort(403, 'Peran akun belum dikonfigurasi.');
    }

    private function admin(Request $request): View
    {
        $institutionId = $request->user()->institution_id;
        return view('dashboard.admin', [
            'studentCount' => Student::where('institution_id', $institutionId)->where('status', 'active')->count(),
            'teacherCount' => Teacher::where('institution_id', $institutionId)->where('status', 'active')->count(),
            'classCount' => SchoolClass::where('institution_id', $institutionId)->where('status', 'active')->count(),
            'todayMeetings' => Meeting::where('institution_id', $institutionId)->whereDate('meeting_date', today())->count(),
            'guardianCount' => Guardian::where('institution_id', $institutionId)->where('status','active')->count(),
            'groupCount' => LearningGroup::where('institution_id', $institutionId)->where('status','active')->count(),
            'todayAttendance' => AttendanceRecord::whereHas('meeting', fn($q)=>$q->where('institution_id',$institutionId)->whereDate('meeting_date',today()))->count(),
            'openThreads' => LiaisonThread::where('institution_id',$institutionId)->whereIn('status',['new','active','waiting'])->count(),
            'newRegistrations' => Feature::enabled('admissions', $institutionId, true) && Schema::hasTable('admission_registrations')
                ? AdmissionRegistration::where('institution_id',$institutionId)->where('status','new')->count()
                : 0,
            'recentAnnouncements' => Announcement::where('institution_id', $institutionId)->latest('publish_at')->limit(5)->get(),
        ]);
    }

    private function teacher(Request $request): View
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403, 'Profil guru belum terhubung.');

        $assignments = TeacherAssignment::with(['schoolClass', 'learningGroup', 'program'])
            ->where('institution_id', $request->user()->institution_id)
            ->where('teacher_id', $teacher->id)
            ->currentlyActive()
            ->get();

        $academyEnabled = Feature::enabled('parent_academy', $request->user()->institution_id, true);
        $quranEnabled = Feature::enabled('quran_audio', $request->user()->institution_id, true);

        return view('dashboard.teacher', [
            'teacher' => $teacher,
            'academyEnabled' => $academyEnabled,
            'quranEnabled' => $quranEnabled,
            'teachingAssignments' => $assignments,
            'todayMeetings' => Meeting::with(['schoolClass','learningGroup','attendanceRecords'])
                ->where('institution_id', $request->user()->institution_id)
                ->where('teacher_id',$teacher->id)->whereDate('meeting_date',today())->latest('started_at')->get(),
            'openMeetings' => Meeting::with(['schoolClass','learningGroup'])
                ->where('institution_id', $request->user()->institution_id)
                ->where('teacher_id',$teacher->id)->whereIn('status',['draft','ongoing'])->latest('meeting_date')->limit(6)->get(),
            'activeTargets' => MemorizationTarget::where('institution_id', $request->user()->institution_id)
                ->where('assigned_by_teacher_id',$teacher->id)
                ->whereIn('status',['active','in_progress','strengthening'])->count(),
            'pendingSubmissions' => AssignmentRecipient::whereHas('assignment', fn ($q) => $q
                ->where('institution_id', $request->user()->institution_id)
                ->where('created_by_teacher_id', $teacher->id))
                ->whereIn('status', ['submitted', 'reviewing','revision_needed'])->count(),
            'activeThreads' => LiaisonThread::where('institution_id', $request->user()->institution_id)
                ->where('assigned_teacher_id', $teacher->id)->whereIn('status', ['new', 'active', 'waiting'])->count(),
            'academyProgramCount' => $academyEnabled
                ? AcademyProgram::where('institution_id',$request->user()->institution_id)->where('status','published')->whereIn('audience',['teacher','all'])->count()
                : 0,
        ]);
    }

    private function guardian(Request $request): View
    {
        $guardian = $request->user()->guardian;
        abort_unless($guardian, 403, 'Profil wali belum terhubung.');
        $students = $guardian->students()->with('currentEnrollment.schoolClass')->get();
        $studentIds = $students->pluck('id');

        $todayRecords = AttendanceRecord::with(['student','meeting.schoolClass','meeting.learningGroup'])
            ->whereIn('student_id',$studentIds)
            ->whereHas('meeting',fn($q)=>$q->whereDate('meeting_date',today())->whereNotNull('summary_published_at'))
            ->get();

        $institutionId = (int) $request->user()->institution_id;
        $announcements = Announcement::query()
            ->with('targets')
            ->where('institution_id', $institutionId)
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('publish_at')
            ->limit(30)
            ->get()
            ->filter(fn (Announcement $announcement): bool => $this->audience->announcementVisibleTo($announcement, $request->user()))
            ->take(5)
            ->values();

        $fridaySessions = FridayDevelopmentSession::query()
            ->with('targets')
            ->where('institution_id', $institutionId)
            ->where('status', 'published')
            ->latest('session_date')
            ->limit(30)
            ->get()
            ->filter(fn (FridayDevelopmentSession $session): bool => $this->audience->fridayVisibleTo($session, $request->user()))
            ->take(3)
            ->values();

        $academyEnabled = Feature::enabled('parent_academy', $institutionId, true);
        $quranEnabled = Feature::enabled('quran_audio', $institutionId, true);

        return view('dashboard.guardian', [
            'guardian' => $guardian,
            'students' => $students,
            'todayRecords' => $todayRecords,
            'activeTasks' => AssignmentRecipient::with(['assignment', 'student'])
                ->whereIn('student_id', $studentIds)
                ->whereIn('status', ['assigned', 'submitted', 'revision_needed'])
                ->latest()->limit(8)->get(),
            'announcements' => $announcements,
            'fridaySessions' => $fridaySessions,
            'academyEnabled' => $academyEnabled,
            'quranEnabled' => $quranEnabled,
            'academyRecommendations' => $academyEnabled
                ? AcademyRecommendation::with(['student','lesson.module.program'])
                    ->where('institution_id',$institutionId)->whereIn('student_id',$studentIds)->where('status','active')->latest('recommended_at')->limit(4)->get()
                : collect(),
            'academyFeatured' => $academyEnabled
                ? AcademyProgram::where('institution_id',$institutionId)->where('status','published')->whereIn('audience',['guardian','all'])->orderByDesc('is_featured')->orderBy('sort_order')->first()
                : null,
        ]);
    }

}
