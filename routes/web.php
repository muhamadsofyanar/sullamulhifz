<?php

use App\Http\Controllers\Admin\AcademicController;
use App\Http\Controllers\Admin\AcademicCoreController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\ReportCardController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentPledgeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentFeedController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guardian\PortalController;
use App\Http\Controllers\LiaisonController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\ClassroomController;
use App\Http\Controllers\Teacher\MeetingController;
use App\Http\Controllers\Teacher\LearningPlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');
Route::get('/tentang', fn () => app(PublicSiteController::class)->page('tentang'))->name('public.about');
Route::get('/program', fn () => app(PublicSiteController::class)->page('program'))->name('public.programs');
Route::get('/tpa', fn () => app(PublicSiteController::class)->page('tpa'))->name('public.tpa');
Route::get('/academy', fn () => app(PublicSiteController::class)->page('academy'))->name('public.academy');
Route::get('/kontak', fn () => app(PublicSiteController::class)->page('kontak'))->name('public.contact');
Route::get('/privasi', fn () => app(PublicSiteController::class)->page('privasi'))->name('public.privacy');
Route::get('/syarat-ketentuan', fn () => app(PublicSiteController::class)->page('syarat-ketentuan'))->name('public.terms');
Route::get('/ikrar-santri', [PublicSiteController::class, 'pledge'])->name('public.pledge');
Route::get('/lembaga/tpa-al-insyirah', [PublicSiteController::class, 'institutionShowcase'])->name('public.institution.showcase');
Route::get('/referensi-lembaga', [PublicSiteController::class, 'institutionReference'])->name('public.institution.reference');
Route::get('/artikel', [PublicSiteController::class, 'articles'])->name('public.articles');
Route::get('/artikel/{article}', [PublicSiteController::class, 'article'])->name('public.article');
Route::get('/pendaftaran', [PublicSiteController::class, 'registration'])->name('public.registration');
Route::post('/pendaftaran', [PublicSiteController::class, 'storeRegistration'])->middleware('throttle:5,10')->name('public.registration.store');

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
    Route::post('/pengumuman/{announcement}/konfirmasi', [ContentFeedController::class, 'acknowledge'])->name('feed.announcements.acknowledge');
    Route::get('/pembinaan-jumat', [ContentFeedController::class, 'friday'])->name('feed.friday');
    Route::get('/nilai/ikrar-santri', [ContentFeedController::class, 'pledge'])->name('feed.pledge');
    Route::get('/media/submission/{submission}', [MediaController::class, 'submission'])->name('media.submission');
    Route::get('/media/liaison/{message}', [MediaController::class, 'liaison'])->name('media.liaison');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::middleware('role:superadmin,institution_admin')->group(function (): void {
            Route::resource('students', StudentController::class)->except(['destroy']);
            Route::post('/students/{student}/guardians', [StudentController::class, 'addGuardian'])->name('students.guardians.store');
            Route::resource('guardians', GuardianController::class)->only(['index','show','update']);
            Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
            Route::get('/imports/template', [ImportController::class, 'template'])->name('imports.template');
            Route::post('/imports/preview', [ImportController::class, 'preview'])->name('imports.preview');
            Route::get('/imports/{batch}', [ImportController::class, 'show'])->name('imports.show');
            Route::post('/imports/{batch}/commit', [ImportController::class, 'commit'])->name('imports.commit');
            Route::resource('teachers', TeacherController::class)->only(['index','create','store']);
            Route::put('/accounts/{user}/password', [AccountController::class, 'resetPassword'])->name('accounts.password');

            Route::get('/institution', [InstitutionController::class, 'edit'])->name('institution.edit');
            Route::put('/institution', [InstitutionController::class, 'update'])->name('institution.update');

            Route::get('/academic-core', [AcademicCoreController::class, 'index'])->name('academic-core.index');
            Route::put('/academic-core/years/{year}', [AcademicCoreController::class, 'updateYear'])->name('academic-core.year.update');
            Route::post('/academic-core/targets', [AcademicCoreController::class, 'storeTarget'])->name('academic-core.targets.store');
            Route::put('/academic-core/targets/{target}', [AcademicCoreController::class, 'updateTarget'])->name('academic-core.targets.update');

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

            Route::get('/ikrar-santri', [StudentPledgeController::class, 'edit'])->name('student-pledge.edit');
            Route::put('/ikrar-santri', [StudentPledgeController::class, 'update'])->name('student-pledge.update');
            Route::delete('/ikrar-santri', [StudentPledgeController::class, 'reset'])->name('student-pledge.reset');

            Route::get('/website', [WebsiteController::class, 'index'])->name('website.index');
            Route::post('/website/pages', [WebsiteController::class, 'storePage'])->name('website.pages.store');
            Route::put('/website/pages/{page}', [WebsiteController::class, 'updatePage'])->name('website.pages.update');
            Route::post('/website/articles', [WebsiteController::class, 'storeArticle'])->name('website.articles.store');
            Route::put('/website/articles/{article}', [WebsiteController::class, 'updateArticle'])->name('website.articles.update');
            Route::put('/website/registrations/{registration}', [WebsiteController::class, 'updateRegistration'])->name('website.registrations.update');

            Route::get('/report-cards', [ReportCardController::class, 'index'])->name('report-cards.index');
            Route::post('/report-cards', [ReportCardController::class, 'store'])->name('report-cards.store');
            Route::get('/report-cards/{reportCard}', [ReportCardController::class, 'show'])->name('report-cards.show');
            Route::put('/report-cards/{reportCard}', [ReportCardController::class, 'update'])->name('report-cards.update');
            Route::post('/report-cards/{reportCard}/publish', [ReportCardController::class, 'publish'])->name('report-cards.publish');
            Route::get('/report-cards/{reportCard}/print', [ReportCardController::class, 'print'])->name('report-cards.print');

            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/students.csv', [ReportController::class, 'studentsCsv'])->name('reports.students.csv');
            Route::get('/reports/attendance.csv', [ReportController::class, 'attendanceCsv'])->name('reports.attendance.csv');
            Route::get('/reports/guardians.csv', [ReportController::class, 'guardiansCsv'])->name('reports.guardians.csv');
            Route::get('/reports/tahsin.csv', [ReportController::class, 'tahsinCsv'])->name('reports.tahsin.csv');
            Route::get('/reports/memorization.csv', [ReportController::class, 'memorizationCsv'])->name('reports.memorization.csv');
            Route::get('/reports/murajaah.csv', [ReportController::class, 'murajaahCsv'])->name('reports.murajaah.csv');
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

        Route::get('/learning-plan', [LearningPlanController::class, 'index'])->name('learning-plan.index');
        Route::post('/learning-plan/targets', [LearningPlanController::class, 'storeTarget'])->name('learning-plan.targets.store');
        Route::put('/learning-plan/targets/{target}', [LearningPlanController::class, 'updateTarget'])->name('learning-plan.targets.update');
        Route::post('/learning-plan/observations', [LearningPlanController::class, 'storeObservation'])->name('learning-plan.observations.store');

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
