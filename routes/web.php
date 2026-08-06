<?php

use App\Http\Controllers\Admin\AcademicController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentFeedController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guardian\PortalController;
use App\Http\Controllers\LiaisonController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\ClassroomController;
use App\Http\Controllers\Teacher\MeetingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->getHost() === config('sullam.portal_host')) {
        return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
    }

    return view('public.home');
})->name('public.home');

Route::view('/tentang', 'public.about')->name('public.about');
Route::view('/program', 'public.programs')->name('public.programs');
Route::view('/tpa', 'public.tpa')->name('public.tpa');
Route::view('/academy', 'public.academy')->name('public.academy');
Route::view('/artikel', 'public.articles')->name('public.articles');
Route::view('/kontak', 'public.contact')->name('public.contact');
Route::view('/privasi', 'public.privacy')->name('public.privacy');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
});

Route::middleware(['auth', 'password.changed'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/buku-penghubung', [LiaisonController::class, 'index'])->name('liaison.index');
    Route::get('/buku-penghubung/buat', [LiaisonController::class, 'create'])->name('liaison.create');
    Route::post('/buku-penghubung', [LiaisonController::class, 'store'])->name('liaison.store');
    Route::get('/buku-penghubung/{thread}', [LiaisonController::class, 'show'])->name('liaison.show');
    Route::post('/buku-penghubung/{thread}/balas', [LiaisonController::class, 'reply'])->name('liaison.reply');

    Route::get('/pengumuman', [ContentFeedController::class, 'announcements'])->name('feed.announcements');
    Route::get('/pembinaan-jumat', [ContentFeedController::class, 'friday'])->name('feed.friday');
    Route::get('/media/submission/{submission}', [MediaController::class, 'submission'])->name('media.submission');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::middleware('role:superadmin,institution_admin')->group(function (): void {
            Route::resource('students', StudentController::class)->except(['destroy']);
            Route::post('/students/{student}/guardians', [StudentController::class, 'addGuardian'])->name('students.guardians.store');
            Route::resource('teachers', TeacherController::class)->only(['index','create','store']);
            Route::put('/accounts/{user}/password', [AccountController::class, 'resetPassword'])->name('accounts.password');

            Route::get('/academic', [AcademicController::class, 'index'])->name('academic.index');
            Route::post('/academic/years', [AcademicController::class, 'storeYear'])->name('academic.years.store');
            Route::post('/academic/levels', [AcademicController::class, 'storeLevel'])->name('academic.levels.store');
            Route::post('/academic/programs', [AcademicController::class, 'storeProgram'])->name('academic.programs.store');
            Route::post('/academic/classes', [AcademicController::class, 'storeClass'])->name('academic.classes.store');
            Route::post('/academic/groups', [AcademicController::class, 'storeGroup'])->name('academic.groups.store');
            Route::get('/academic/groups/{group}', [AcademicController::class, 'group'])->name('academic.groups.show');
            Route::post('/academic/groups/{group}/members', [AcademicController::class, 'addGroupMember'])->name('academic.groups.members.store');
            Route::post('/academic/teacher-assignments', [AcademicController::class, 'storeTeacherAssignment'])->name('academic.teacher-assignments.store');
            Route::post('/academic/schedules', [AcademicController::class, 'storeSchedule'])->name('academic.schedules.store');
        });

        Route::middleware('role:superadmin,institution_admin,head')->group(function (): void {
            Route::get('/content', [AdminContentController::class, 'index'])->name('content.index');
            Route::post('/content/announcements', [AdminContentController::class, 'storeAnnouncement'])->name('content.announcements.store');
            Route::post('/content/friday', [AdminContentController::class, 'storeFriday'])->name('content.friday.store');

            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/students.csv', [ReportController::class, 'studentsCsv'])->name('reports.students.csv');
            Route::get('/reports/attendance.csv', [ReportController::class, 'attendanceCsv'])->name('reports.attendance.csv');
        });
    });

    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function (): void {
        Route::get('/classes', [ClassroomController::class, 'index'])->name('classrooms.index');
        Route::get('/classes/{class}', [ClassroomController::class, 'showClass'])->name('classrooms.class');
        Route::get('/groups/{group}', [ClassroomController::class, 'showGroup'])->name('classrooms.group');

        Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
        Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
        Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
        Route::get('/meetings/{meeting}/attendance', [MeetingController::class, 'attendance'])->name('meetings.attendance');
        Route::put('/meetings/{meeting}/attendance', [MeetingController::class, 'storeAttendance'])->name('meetings.attendance.store');
        Route::post('/meetings/{meeting}/tahsin', [MeetingController::class, 'storeTahsin'])->name('meetings.tahsin.store');
        Route::post('/meetings/{meeting}/memorization', [MeetingController::class, 'storeMemorization'])->name('meetings.memorization.store');
        Route::post('/meetings/{meeting}/murajaah', [MeetingController::class, 'storeMurajaah'])->name('meetings.murajaah.store');
        Route::put('/meetings/{meeting}/finish', [MeetingController::class, 'finish'])->name('meetings.finish');

        Route::resource('assignments', AssignmentController::class)->only(['index','create','store','show']);
        Route::put('/submissions/{submission}/review', [AssignmentController::class, 'review'])->name('submissions.review');
    });

    Route::prefix('guardian')->name('guardian.')->middleware('role:guardian')->group(function (): void {
        Route::get('/children/{student}', [PortalController::class, 'child'])->name('children.show');
        Route::get('/tasks', [PortalController::class, 'tasks'])->name('tasks.index');
        Route::get('/tasks/{recipient}', [PortalController::class, 'task'])->name('tasks.show');
        Route::post('/tasks/{recipient}/submit', [PortalController::class, 'submit'])->name('tasks.submit');
    });
});
