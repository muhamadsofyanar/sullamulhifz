<?php

use App\Http\Controllers\Admin\AcademicController;
use App\Http\Controllers\Admin\AcademicCoreController;
use App\Http\Controllers\Admin\AcademyController as AdminAcademyController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\QuranLibraryController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\LaunchReadinessController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Admin\ReportCardController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentPledgeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountInvitationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AcademyController;
use App\Http\Controllers\AcademyExperienceController;
use App\Http\Controllers\AcademyQuranController;
use App\Http\Controllers\ContentFeedController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guardian\PortalController;
use App\Http\Controllers\LiaisonController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuranPracticeController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\AcademyRecommendationController;
use App\Http\Controllers\Teacher\ClassroomController;
use App\Http\Controllers\Teacher\DailyOperationsController;
use App\Http\Controllers\Teacher\MeetingController;
use App\Http\Controllers\Teacher\LearningPlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::domain((string) config('sullam.api_host'))->get('/', fn () => response()->json([
    'product' => 'Sullamul Hifz API',
    'tagline' => 'Bukan Sekadar Hafal, Tapi KUAT',
    'status' => 'ready',
    'version' => 'v1',
    'endpoints' => [
        'health' => '/api/health',
        'meta' => '/api/v1/meta',
        'academy_preview' => '/api/v1/academy-preview',
    ],
    'note' => 'Endpoint publik hanya memuat metadata non-pribadi. API data pengguna membutuhkan autentikasi pada fase berikutnya.',
]))->name('api.root');

Route::domain((string) config('sullam.staging_host'))->get('/', fn () => view('staging.index'))->name('staging.home');
Route::domain((string) config('sullam.staging_host'))->get('/status', fn () => response()->json([
    'product' => 'Sullamul Hifz Staging',
    'status' => config('sullam.staging_enabled') ? 'enabled' : 'disabled',
    'release' => trim((string) @file_get_contents(base_path('RELEASE'))) ?: 'unknown',
]))->name('staging.status');

// Academy memakai resource Laravel yang sama, tetapi memiliki portal dan navigasi
// mandiri pada academy.sullamulhifz.or.id. Route domain diletakkan sebelum
// route publik generik agar path '/' Academy tidak tertangkap website utama.
Route::domain((string) config('sullam.academy_host'))
    ->middleware(['auth', 'password.changed', 'permission:academy.view', 'feature:academy_portal'])
    ->name('academy.portal.')
    ->group(function (): void {
        Route::get('/', [AcademyController::class, 'index'])->name('index');
        Route::get('/program', [AcademyController::class, 'programs'])->name('programs');
        Route::get('/kelas-saya', [AcademyController::class, 'classes'])->name('classes');
        Route::get('/modul', [AcademyController::class, 'modules'])->name('modules');
        Route::get('/materi', [AcademyController::class, 'materials'])->name('materials');
        Route::get('/video', [AcademyController::class, 'videos'])->name('videos');
        Route::get('/audio', [AcademyQuranController::class, 'index'])->middleware('feature:quran_audio')->name('audio');
        Route::get('/audio/playlist', [AcademyQuranController::class, 'playlist'])->middleware('feature:quran_audio')->name('audio.playlist');
        Route::post('/audio/sesi', [AcademyQuranController::class, 'startSession'])->middleware('feature:quran_audio')->name('audio.sessions.start');
        Route::put('/audio/sesi/{session}/selesai', [AcademyQuranController::class, 'completeSession'])->middleware('feature:quran_audio')->name('audio.sessions.complete');
        Route::get('/jalur-belajar', [AcademyExperienceController::class, 'paths'])->middleware('feature:learning_paths')->name('paths');
        Route::get('/jalur-belajar/{path}', [AcademyExperienceController::class, 'path'])->middleware('feature:learning_paths')->name('path');
        Route::get('/tersimpan', [AcademyExperienceController::class, 'bookmarks'])->name('bookmarks');
        Route::post('/materi/{lesson}/simpan', [AcademyExperienceController::class, 'toggleBookmark'])->name('lesson.bookmark');
        Route::post('/audio/preset/{preset}/simpan', [AcademyExperienceController::class, 'togglePresetBookmark'])->middleware('feature:quran_audio')->name('audio.preset.bookmark');
        Route::post('/materi/{lesson}/refleksi', [AcademyExperienceController::class, 'storeReflection'])->middleware('feature:academy_reflections')->name('lesson.reflection');
        Route::get('/ekosistem', [AcademyExperienceController::class, 'ecosystem'])->name('ecosystem');
        Route::get('/artikel', [AcademyController::class, 'articles'])->name('articles');
        Route::get('/progres', [AcademyController::class, 'progress'])->name('progress');
        Route::get('/rekomendasi', [AcademyController::class, 'recommendations'])->name('recommendations');
        Route::get('/profil', [AcademyController::class, 'profile'])->name('profile');
        Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::get('/program/{program}', [AcademyController::class, 'program'])->name('program');
        Route::get('/materi/{lesson}', [AcademyController::class, 'lesson'])->name('lesson');
        Route::post('/materi/{lesson}/selesai', [AcademyController::class, 'complete'])->name('lesson.complete');
    });

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
    Route::get('/lupa-kata-sandi', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/lupa-kata-sandi', [PasswordResetController::class, 'sendLink'])->middleware('throttle:3,10')->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [PasswordResetController::class, 'reset'])->middleware('throttle:5,10')->name('password.update');
    Route::get('/aktivasi/{token}', [AccountInvitationController::class, 'show'])->name('activation.show');
    Route::post('/aktivasi/{token}', [AccountInvitationController::class, 'activate'])->middleware('throttle:5,10')->name('activation.store');
});

Route::middleware(['auth', 'password.changed'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/buku-penghubung', [LiaisonController::class, 'index'])->middleware('permission:liaison.view')->name('liaison.index');
    Route::get('/buku-penghubung/buat', [LiaisonController::class, 'create'])->middleware('permission:liaison.manage')->name('liaison.create');
    Route::post('/buku-penghubung', [LiaisonController::class, 'store'])->middleware('permission:liaison.manage')->name('liaison.store');
    Route::get('/buku-penghubung/{thread}', [LiaisonController::class, 'show'])->middleware('permission:liaison.view')->name('liaison.show');
    Route::post('/buku-penghubung/{thread}/balas', [LiaisonController::class, 'reply'])->middleware('permission:liaison.manage')->name('liaison.reply');

    Route::get('/pengumuman', [ContentFeedController::class, 'announcements'])->middleware('permission:announcements.view')->name('feed.announcements');
    Route::post('/pengumuman/{announcement}/konfirmasi', [ContentFeedController::class, 'acknowledge'])->middleware('permission:announcements.view')->name('feed.announcements.acknowledge');
    Route::get('/pembinaan-jumat', [ContentFeedController::class, 'friday'])->middleware('permission:friday.view')->name('feed.friday');
    Route::get('/nilai/ikrar-santri', [ContentFeedController::class, 'pledge'])->name('feed.pledge');
    Route::get('/academy/belajar', [AcademyController::class, 'index'])->middleware(['permission:academy.view','feature:academy_portal'])->name('academy.index');
    Route::get('/academy/program/{program}', [AcademyController::class, 'program'])->middleware(['permission:academy.view','feature:academy_portal'])->name('academy.program');
    Route::get('/academy/materi/{lesson}', [AcademyController::class, 'lesson'])->middleware(['permission:academy.view','feature:academy_portal'])->name('academy.lesson');
    Route::post('/academy/materi/{lesson}/selesai', [AcademyController::class, 'complete'])->middleware(['permission:academy.view','feature:academy_portal'])->name('academy.lesson.complete');

    Route::get('/latihan-quran', [QuranPracticeController::class, 'index'])->middleware(['permission:quran.view','feature:quran_audio'])->name('quran-practice.index');
    Route::get('/latihan-quran/playlist', [QuranPracticeController::class, 'playlist'])->middleware(['permission:quran.view','feature:quran_audio'])->name('quran-practice.playlist');
    Route::get('/latihan-quran/target/{target}', [QuranPracticeController::class, 'target'])->middleware(['permission:quran.view','feature:quran_audio'])->name('quran-practice.target');
    Route::post('/latihan-quran/sesi', [QuranPracticeController::class, 'startSession'])->middleware(['permission:quran.view','feature:quran_audio'])->name('quran-practice.sessions.start');
    Route::put('/latihan-quran/sesi/{session}/selesai', [QuranPracticeController::class, 'completeSession'])->middleware(['permission:quran.view','feature:quran_audio'])->name('quran-practice.sessions.complete');
    Route::get('/media/submission/{submission}', [MediaController::class, 'submission'])->middleware('permission:media.view')->name('media.submission');
    Route::get('/media/liaison/{message}', [MediaController::class, 'liaison'])->middleware('permission:media.view')->name('media.liaison');
    Route::get('/media/announcement/{announcement}', [MediaController::class, 'announcement'])->middleware('permission:media.view')->name('media.announcement');
    Route::get('/media/friday/{session}', [MediaController::class, 'friday'])->middleware('permission:media.view')->name('media.friday');
    Route::get('/media/assets/{mediaAsset}', [MediaController::class, 'adminAsset'])->middleware('permission:media.manage')->name('media.asset');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::middleware('role:superadmin,institution_admin')->group(function (): void {
            Route::resource('students', StudentController::class)->except(['destroy'])->middleware('permission:students.manage');
            Route::post('/students/{student}/guardians', [StudentController::class, 'addGuardian'])->middleware('permission:guardians.manage')->name('students.guardians.store');
            Route::resource('guardians', GuardianController::class)->only(['index','show','update'])->middleware('permission:guardians.manage');
            Route::get('/imports', [ImportController::class, 'index'])->middleware('permission:students.manage')->name('imports.index');
            Route::get('/imports/template', [ImportController::class, 'template'])->middleware('permission:students.manage')->name('imports.template');
            Route::post('/imports/preview', [ImportController::class, 'preview'])->middleware('permission:students.manage')->name('imports.preview');
            Route::get('/imports/{batch}', [ImportController::class, 'show'])->middleware('permission:students.manage')->name('imports.show');
            Route::post('/imports/{batch}/commit', [ImportController::class, 'commit'])->middleware('permission:students.manage')->name('imports.commit');
            Route::resource('teachers', TeacherController::class)->only(['index','create','store'])->middleware('permission:teachers.manage');
            Route::put('/accounts/{user}/password', [AccountController::class, 'resetPassword'])->middleware('permission:permissions.manage')->name('accounts.password');
            Route::post('/accounts/{user}/invitation', [AccountController::class, 'invite'])->middleware('permission:permissions.manage')->name('accounts.invitation');

            Route::get('/institution', [InstitutionController::class, 'edit'])->middleware('permission:institution.manage')->name('institution.edit');
            Route::put('/institution', [InstitutionController::class, 'update'])->middleware('permission:institution.manage')->name('institution.update');

            Route::get('/platform', [PlatformController::class, 'index'])->middleware('permission:features.manage')->name('platform.index');
            Route::post('/platform/branches', [PlatformController::class, 'storeBranch'])->middleware('permission:features.manage')->name('platform.branches.store');
            Route::put('/platform/branches/{branch}', [PlatformController::class, 'updateBranch'])->middleware('permission:features.manage')->name('platform.branches.update');
            Route::post('/platform/periods', [PlatformController::class, 'storePeriod'])->middleware('permission:academic.manage')->name('platform.periods.store');
            Route::put('/platform/features/{featureFlag}', [PlatformController::class, 'updateFeature'])->middleware('permission:features.manage')->name('platform.features.update');

            Route::get('/academic-core', [AcademicCoreController::class, 'index'])->middleware('permission:academic.manage')->name('academic-core.index');
            Route::put('/academic-core/years/{year}', [AcademicCoreController::class, 'updateYear'])->middleware('permission:academic.manage')->name('academic-core.year.update');
            Route::post('/academic-core/targets', [AcademicCoreController::class, 'storeTarget'])->middleware('permission:academic.manage')->name('academic-core.targets.store');
            Route::put('/academic-core/targets/{target}', [AcademicCoreController::class, 'updateTarget'])->middleware('permission:academic.manage')->name('academic-core.targets.update');

            Route::get('/academic', [AcademicController::class, 'index'])->middleware('permission:academic.manage')->name('academic.index');
            Route::post('/academic/years', [AcademicController::class, 'storeYear'])->middleware('permission:academic.manage')->name('academic.years.store');
            Route::post('/academic/levels', [AcademicController::class, 'storeLevel'])->middleware('permission:academic.manage')->name('academic.levels.store');
            Route::post('/academic/programs', [AcademicController::class, 'storeProgram'])->middleware('permission:academic.manage')->name('academic.programs.store');
            Route::post('/academic/classes', [AcademicController::class, 'storeClass'])->middleware('permission:academic.manage')->name('academic.classes.store');
            Route::post('/academic/groups', [AcademicController::class, 'storeGroup'])->middleware('permission:academic.manage')->name('academic.groups.store');
            Route::get('/academic/groups/{group}', [AcademicController::class, 'group'])->middleware('permission:academic.manage')->name('academic.groups.show');
            Route::post('/academic/groups/{group}/members', [AcademicController::class, 'addGroupMember'])->middleware('permission:academic.manage')->name('academic.groups.members.store');
            Route::post('/academic/teacher-assignments', [AcademicController::class, 'storeTeacherAssignment'])->middleware('permission:academic.manage')->name('academic.teacher-assignments.store');
            Route::post('/academic/schedules', [AcademicController::class, 'storeSchedule'])->middleware('permission:academic.manage')->name('academic.schedules.store');
        });

        Route::middleware('role:superadmin,institution_admin,head')->group(function (): void {
            Route::get('/academy', [AdminAcademyController::class, 'index'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.index');
            Route::post('/academy/programs', [AdminAcademyController::class, 'storeProgram'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.programs.store');
            Route::put('/academy/programs/{program}', [AdminAcademyController::class, 'updateProgram'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.programs.update');
            Route::post('/academy/modules', [AdminAcademyController::class, 'storeModule'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.modules.store');
            Route::post('/academy/lessons', [AdminAcademyController::class, 'storeLesson'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.lessons.store');
            Route::put('/academy/lessons/{lesson}', [AdminAcademyController::class, 'updateLesson'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.lessons.update');
            Route::post('/academy/paths', [AdminAcademyController::class, 'storePath'])->middleware(['permission:academy.manage','feature:learning_paths'])->name('academy.paths.store');
            Route::put('/academy/paths/{path}', [AdminAcademyController::class, 'updatePath'])->middleware(['permission:academy.manage','feature:learning_paths'])->name('academy.paths.update');
            Route::post('/academy/path-items', [AdminAcademyController::class, 'storePathItem'])->middleware(['permission:academy.manage','feature:learning_paths'])->name('academy.path-items.store');
            Route::delete('/academy/path-items/{item}', [AdminAcademyController::class, 'destroyPathItem'])->middleware(['permission:academy.manage','feature:learning_paths'])->name('academy.path-items.destroy');

            Route::get('/quran-library', [QuranLibraryController::class, 'index'])->middleware(['permission:quran.manage','feature:quran_audio'])->name('quran-library.index');
            Route::post('/quran-library/sync', [QuranLibraryController::class, 'sync'])->middleware(['permission:quran.manage','feature:quran_audio'])->name('quran-library.sync');
            Route::post('/quran-library/videos', [QuranLibraryController::class, 'storeVideo'])->middleware(['permission:quran.manage','feature:quran_audio'])->name('quran-library.videos.store');
            Route::put('/quran-library/videos/{video}', [QuranLibraryController::class, 'updateVideo'])->middleware(['permission:quran.manage','feature:quran_audio'])->name('quran-library.videos.update');

            Route::get('/launch-readiness', [LaunchReadinessController::class, 'index'])->middleware('permission:features.manage')->name('launch-readiness.index');
            Route::put('/launch-readiness/{launchCheck}', [LaunchReadinessController::class, 'update'])->middleware('permission:features.manage')->name('launch-readiness.update');

            Route::get('/content', [AdminContentController::class, 'index'])->middleware('permission:announcements.manage,friday.manage')->name('content.index');
            Route::post('/content/announcements', [AdminContentController::class, 'storeAnnouncement'])->middleware('permission:announcements.manage')->name('content.announcements.store');
            Route::post('/content/friday', [AdminContentController::class, 'storeFriday'])->middleware('permission:friday.manage')->name('content.friday.store');

            Route::get('/ikrar-santri', [StudentPledgeController::class, 'edit'])->middleware('permission:institution.manage')->name('student-pledge.edit');
            Route::put('/ikrar-santri', [StudentPledgeController::class, 'update'])->middleware('permission:institution.manage')->name('student-pledge.update');
            Route::delete('/ikrar-santri', [StudentPledgeController::class, 'reset'])->middleware('permission:institution.manage')->name('student-pledge.reset');

            Route::get('/website', [WebsiteController::class, 'index'])->middleware(['permission:website.manage','feature:public_website'])->name('website.index');
            Route::post('/website/pages', [WebsiteController::class, 'storePage'])->middleware(['permission:website.manage','feature:public_website'])->name('website.pages.store');
            Route::put('/website/pages/{page}', [WebsiteController::class, 'updatePage'])->middleware(['permission:website.manage','feature:public_website'])->name('website.pages.update');
            Route::post('/website/articles', [WebsiteController::class, 'storeArticle'])->middleware(['permission:website.manage','feature:public_website'])->name('website.articles.store');
            Route::put('/website/articles/{article}', [WebsiteController::class, 'updateArticle'])->middleware(['permission:website.manage','feature:public_website'])->name('website.articles.update');
            Route::put('/website/registrations/{registration}', [WebsiteController::class, 'updateRegistration'])->middleware(['permission:admissions.manage','feature:admissions'])->name('website.registrations.update');

            Route::get('/report-cards', [ReportCardController::class, 'index'])->middleware(['permission:report_cards.manage','feature:report_cards'])->name('report-cards.index');
            Route::post('/report-cards', [ReportCardController::class, 'store'])->middleware(['permission:report_cards.manage','feature:report_cards'])->name('report-cards.store');
            Route::get('/report-cards/{reportCard}', [ReportCardController::class, 'show'])->middleware(['permission:report_cards.manage','feature:report_cards'])->name('report-cards.show');
            Route::put('/report-cards/{reportCard}', [ReportCardController::class, 'update'])->middleware(['permission:report_cards.manage','feature:report_cards'])->name('report-cards.update');
            Route::post('/report-cards/{reportCard}/publish', [ReportCardController::class, 'publish'])->middleware(['permission:report_cards.manage','feature:report_cards'])->name('report-cards.publish');
            Route::get('/report-cards/{reportCard}/print', [ReportCardController::class, 'print'])->middleware(['permission:report_cards.manage','feature:report_cards'])->name('report-cards.print');

            Route::get('/reports', [ReportController::class, 'index'])->middleware('permission:reports.view')->name('reports.index');
            Route::get('/reports/students.csv', [ReportController::class, 'studentsCsv'])->middleware('permission:reports.export')->name('reports.students.csv');
            Route::get('/reports/attendance.csv', [ReportController::class, 'attendanceCsv'])->middleware('permission:reports.export')->name('reports.attendance.csv');
            Route::get('/reports/guardians.csv', [ReportController::class, 'guardiansCsv'])->middleware('permission:reports.export')->name('reports.guardians.csv');
            Route::get('/reports/tahsin.csv', [ReportController::class, 'tahsinCsv'])->middleware('permission:reports.export')->name('reports.tahsin.csv');
            Route::get('/reports/memorization.csv', [ReportController::class, 'memorizationCsv'])->middleware('permission:reports.export')->name('reports.memorization.csv');
            Route::get('/reports/murajaah.csv', [ReportController::class, 'murajaahCsv'])->middleware('permission:reports.export')->name('reports.murajaah.csv');
            Route::get('/reports/monthly.csv', [ReportController::class, 'monthlyCsv'])->middleware('permission:reports.export')->name('reports.monthly.csv');
            Route::get('/reports/tasks.csv', [ReportController::class, 'tasksCsv'])->middleware('permission:reports.export')->name('reports.tasks.csv');
        });
    });

    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function (): void {
        Route::get('/academy', [AcademyRecommendationController::class, 'index'])->middleware(['feature:parent_academy','permission:academy.view'])->name('academy.index');
        Route::post('/academy/recommendations', [AcademyRecommendationController::class, 'store'])->middleware(['feature:parent_academy','permission:academy.view'])->name('academy.recommendations.store');

        Route::get('/daily', [DailyOperationsController::class, 'index'])->middleware('permission:academic.view')->name('daily.index');
        Route::get('/classes', [ClassroomController::class, 'index'])->middleware('permission:academic.view')->name('classrooms.index');
        Route::get('/classes/{class}', [ClassroomController::class, 'showClass'])->middleware('permission:academic.view')->name('classrooms.class');
        Route::get('/groups/{group}', [ClassroomController::class, 'showGroup'])->middleware('permission:academic.view')->name('classrooms.group');

        Route::get('/meetings/create', [MeetingController::class, 'create'])->middleware('permission:meetings.manage')->name('meetings.create');
        Route::post('/meetings', [MeetingController::class, 'store'])->middleware('permission:meetings.manage')->name('meetings.store');
        Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->middleware('permission:meetings.view')->name('meetings.show');
        Route::get('/meetings/{meeting}/attendance', [MeetingController::class, 'attendance'])->middleware('permission:attendance.manage')->name('meetings.attendance');
        Route::put('/meetings/{meeting}/attendance', [MeetingController::class, 'storeAttendance'])->middleware('permission:attendance.manage')->name('meetings.attendance.store');
        Route::post('/meetings/{meeting}/tahsin', [MeetingController::class, 'storeTahsin'])->middleware('permission:learning.manage')->name('meetings.tahsin.store');
        Route::post('/meetings/{meeting}/memorization', [MeetingController::class, 'storeMemorization'])->middleware('permission:learning.manage')->name('meetings.memorization.store');
        Route::post('/meetings/{meeting}/murajaah', [MeetingController::class, 'storeMurajaah'])->middleware('permission:learning.manage')->name('meetings.murajaah.store');
        Route::put('/meetings/{meeting}/finish', [MeetingController::class, 'finish'])->middleware('permission:meetings.manage')->name('meetings.finish');

        Route::get('/learning-plan', [LearningPlanController::class, 'index'])->middleware('permission:learning.manage')->name('learning-plan.index');
        Route::post('/learning-plan/targets', [LearningPlanController::class, 'storeTarget'])->middleware('permission:learning.manage')->name('learning-plan.targets.store');
        Route::put('/learning-plan/targets/{target}', [LearningPlanController::class, 'updateTarget'])->middleware('permission:learning.manage')->name('learning-plan.targets.update');
        Route::post('/learning-plan/observations', [LearningPlanController::class, 'storeObservation'])->middleware('permission:learning.manage')->name('learning-plan.observations.store');

        Route::get('/assignments', [AssignmentController::class, 'index'])->middleware('permission:assignments.view')->name('assignments.index');
        Route::get('/assignments/create', [AssignmentController::class, 'create'])->middleware('permission:assignments.manage')->name('assignments.create');
        Route::post('/assignments', [AssignmentController::class, 'store'])->middleware('permission:assignments.manage')->name('assignments.store');
        Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->middleware('permission:assignments.view')->name('assignments.show');
        Route::put('/submissions/{submission}/review', [AssignmentController::class, 'review'])->middleware('permission:assignments.review')->name('submissions.review');
    });

    Route::prefix('guardian')->name('guardian.')->middleware('role:guardian')->group(function (): void {
        Route::get('/children/{student}', [PortalController::class, 'child'])->middleware('permission:students.view')->name('children.show');
        Route::get('/tasks', [PortalController::class, 'tasks'])->middleware('permission:assignments.view')->name('tasks.index');
        Route::get('/tasks/{recipient}', [PortalController::class, 'task'])->middleware('permission:assignments.view')->name('tasks.show');
        Route::post('/tasks/{recipient}/submit', [PortalController::class, 'submit'])->middleware('permission:assignments.submit')->name('tasks.submit');
    });
});
