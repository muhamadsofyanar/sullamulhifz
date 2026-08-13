<?php

/** @phase 4.2 Public Positioning; @phase 4.3 Identity Core; @phase 4.4 Multi-tenant; @phase 4.5 Personal 2.0; @phase 4.6 Private Ustadz; @phase 4.7 Institution Suite; @phase 4.8 Family Portal; @phase 4.9 Learning & Academy Integration; @phase 5.0 Business; @phase 5.1 SaaS Readiness; @phase 5.2 Smart Assistant; @phase 5.3 Mobile & Global */

use App\Http\Controllers\Admin\AcademicController;
use App\Http\Controllers\Admin\AcademicCoreController;
use App\Http\Controllers\Admin\AcademyController as AdminAcademyController;
use App\Http\Controllers\Admin\FamilyTeacherController as AdminFamilyTeacherController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\InstitutionSuiteController;
use App\Http\Controllers\Admin\QuranLibraryController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\InfaqController as AdminInfaqController;
use App\Http\Controllers\Admin\InfaqPolicyController;
use App\Http\Controllers\Admin\InfaqRealisationController;
use App\Http\Controllers\Admin\InfaqReportController;
use App\Http\Controllers\Admin\InfaqTransferController;
use App\Http\Controllers\Admin\RecoveryController;
use App\Http\Controllers\Admin\GuidedQuranProgramController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\LaunchReadinessController;
use App\Http\Controllers\Admin\OperationsController as AdminOperationsController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Admin\ReportCardController;
use App\Http\Controllers\Admin\WebsiteController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Admin\CommunicationController as AdminCommunicationController;
use App\Http\Controllers\Admin\EcosystemController as AdminEcosystemController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentPledgeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountInvitationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PersonalBusinessController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PersonalLearningHubController;
use App\Http\Controllers\PersonalSmartAssistantController;
use App\Http\Controllers\PersonalGuidedLearningController;
use App\Http\Controllers\PersonalProgramController;
use App\Http\Controllers\PersonalExperienceController;
use App\Http\Controllers\PersonalCommunityController;
use App\Http\Controllers\PersonalPaymentController;
use App\Http\Controllers\PersonalRegistrationController;
use App\Http\Controllers\GuidedQuranReviewController;
use App\Http\Controllers\AcademyController;
use App\Http\Controllers\AcademyExperienceController;
use App\Http\Controllers\AcademyLmsController;
use App\Http\Controllers\AcademyQuranController;
use App\Http\Controllers\ContentFeedController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guardian\PortalController;
use App\Http\Controllers\Guardian\FamilyLearningController;
use App\Http\Controllers\LiaisonController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\QuranPracticeController;
use App\Http\Controllers\QuranJourneyController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\WorkspaceContextController;
use App\Http\Controllers\InstitutionRegistrationController;
use App\Http\Controllers\RelationshipController;
use App\Http\Controllers\PrivateMentorshipController;
use App\Http\Controllers\FamilyPortalController;
use App\Http\Controllers\InfaqController;
use App\Http\Controllers\InfaqEvidenceController;
use App\Http\Controllers\InfaqReceiptController;
use App\Http\Controllers\PublicInfaqController;
use App\Http\Controllers\Admin\WorkspaceController as AdminWorkspaceController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\AcademyRecommendationController;
use App\Http\Controllers\Teacher\FamilyTeacherController;
use App\Http\Controllers\Teacher\ClassroomController;
use App\Http\Controllers\Teacher\DailyOperationsController;
use App\Http\Controllers\Teacher\MeetingController;
use App\Http\Controllers\Teacher\LearningPlanController;
use App\Http\Controllers\Teacher\PersonalLearningController;
use App\Http\Controllers\Teacher\SmartAssistantReviewController;
use App\Http\Controllers\Teacher\TahfizhController;
use App\Http\Controllers\Teacher\MemorizationFocusController;
use App\Http\Controllers\Teacher\QuranJourneyController as TeacherQuranJourneyController;
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
    ->middleware(['auth', 'password.changed', 'workspace.operational', 'permission:academy.view', 'feature:academy_portal', 'personal.module:academy'])
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
        Route::get('/quran-journey', [QuranJourneyController::class, 'academy'])->middleware('feature:quran_journey')->name('quran-journey');
        Route::post('/quran-journey/program', [QuranJourneyController::class, 'start'])->middleware('feature:quran_journey')->name('quran-journey.programs.start');
        Route::put('/quran-journey/program/{enrollment}/langkah', [QuranJourneyController::class, 'step'])->middleware('feature:quran_journey')->name('quran-journey.programs.step');
        Route::get('/jalur-belajar', [AcademyExperienceController::class, 'paths'])->middleware('feature:learning_paths')->name('paths');
        Route::get('/jalur-belajar/{path}', [AcademyExperienceController::class, 'path'])->middleware('feature:learning_paths')->name('path');
        Route::get('/tersimpan', [AcademyExperienceController::class, 'bookmarks'])->name('bookmarks');
        Route::post('/materi/{lesson}/simpan', [AcademyExperienceController::class, 'toggleBookmark'])->name('lesson.bookmark');
        Route::post('/audio/preset/{preset}/simpan', [AcademyExperienceController::class, 'togglePresetBookmark'])->middleware('feature:quran_audio')->name('audio.preset.bookmark');
        Route::post('/audio/ayah/{globalNumber}/simpan', [AcademyQuranController::class, 'toggleAyahBookmark'])->middleware('feature:quran_audio')->whereNumber('globalNumber')->name('audio.ayah.bookmark');
        Route::post('/materi/{lesson}/refleksi', [AcademyExperienceController::class, 'storeReflection'])->middleware('feature:academy_reflections')->name('lesson.reflection');
        Route::post('/kuis/{quiz}/jawab', [AcademyLmsController::class, 'submitQuiz'])->name('quiz.submit');
        Route::post('/worksheet/{worksheet}/selesai', [AcademyLmsController::class, 'submitWorksheet'])->name('worksheet.submit');
        Route::get('/sertifikat/{certificate}', [AcademyLmsController::class, 'certificate'])->name('certificate');
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
Route::get('/solusi/{audience}', [PublicSiteController::class, 'solution'])->whereIn('audience', ['personal', 'ustadz', 'keluarga', 'lembaga'])->name('public.solution');
Route::get('/fitur', [PublicSiteController::class, 'features'])->name('public.features');
Route::get('/harga', [PublicSiteController::class, 'pricing'])->name('public.pricing');
Route::get('/tentang', fn () => app(PublicSiteController::class)->page('tentang'))->name('public.about');
Route::get('/program', fn () => app(PublicSiteController::class)->page('program'))->name('public.programs');
Route::get('/tpa', fn () => app(PublicSiteController::class)->page('tpa'))->name('public.tpa');
Route::get('/academy', fn () => app(PublicSiteController::class)->page('academy'))->name('public.academy');
Route::get('/sertifikat/verify/{verificationCode}', [AcademyLmsController::class, 'verify'])->name('certificate.verify');
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
Route::get('/daftar-personal', [PersonalRegistrationController::class, 'create'])->middleware('guest')->name('personal.register');
Route::post('/daftar-personal', [PersonalRegistrationController::class, 'store'])->middleware(['guest','throttle:3,10'])->name('personal.register.store');
Route::get('/daftar-lembaga', [InstitutionRegistrationController::class, 'create'])->middleware('guest')->name('institution.register');
Route::post('/daftar-lembaga', [InstitutionRegistrationController::class, 'store'])->middleware(['guest','throttle:3,10'])->name('institution.register.store');
Route::get('/transparansi-infak/{institution:slug}', [PublicInfaqController::class, 'show'])->name('public.infaq.show');
Route::get('/transparansi-infak/bukti/{evidence}', [InfaqEvidenceController::class, 'redacted'])->name('public.infaq.evidence');

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

Route::middleware(['auth', 'password.changed', 'workspace.operational'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::post('/ruang-aktif', [WorkspaceContextController::class, 'switch'])->middleware('throttle:10,1')->name('workspace.switch');
    Route::get('/hubungan', [RelationshipController::class, 'index'])->name('relationships.index');
    Route::post('/hubungan', [RelationshipController::class, 'store'])->middleware('throttle:6,10')->name('relationships.store');
    Route::put('/hubungan/{relationship}/tanggapi', [RelationshipController::class, 'respond'])->name('relationships.respond');
    Route::delete('/hubungan/{relationship}', [RelationshipController::class, 'destroy'])->name('relationships.destroy');

    Route::prefix('bimbingan-privat')->name('mentorship.')->group(function (): void {
        Route::get('/', [PrivateMentorshipController::class, 'index'])->name('index');
        Route::post('/hubungan', [PrivateMentorshipController::class, 'store'])->middleware('throttle:6,10')->name('relationships.store');
        Route::put('/hubungan/{relationship}/tanggapi', [PrivateMentorshipController::class, 'respond'])->name('relationships.respond');
        Route::put('/hubungan/{relationship}/izin', [PrivateMentorshipController::class, 'updateConsent'])->name('relationships.consent');
        Route::post('/hubungan/{relationship}/sesi', [PrivateMentorshipController::class, 'storeSession'])->middleware('throttle:10,1')->name('sessions.store');
        Route::put('/sesi/{session}', [PrivateMentorshipController::class, 'updateSession'])->name('sessions.update');
    });

    Route::prefix('keluarga')->name('family.')->group(function (): void {
        Route::get('/', [FamilyPortalController::class, 'index'])->name('index');
        Route::post('/hubungan', [FamilyPortalController::class, 'store'])->middleware('throttle:6,10')->name('relationships.store');
        Route::put('/hubungan/{relationship}/tanggapi', [FamilyPortalController::class, 'respond'])->name('relationships.respond');
        Route::put('/hubungan/{relationship}/izin', [FamilyPortalController::class, 'updateConsent'])->name('relationships.consent');
        Route::post('/hubungan/{relationship}/catatan', [FamilyPortalController::class, 'storeNote'])->middleware('throttle:10,1')->name('notes.store');
    });

    Route::get('/undangan-ruang/{token}', [InstitutionSuiteController::class, 'showInvitation'])
        ->name('institution-suite.invitations.show');
    Route::post('/undangan-ruang/{token}', [InstitutionSuiteController::class, 'accept'])
        ->middleware('throttle:10,1')
        ->name('institution-suite.invitations.accept');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/preferensi', [PreferenceController::class, 'edit'])->name('preferences.edit'); // @phase 5.3
    Route::put('/preferensi', [PreferenceController::class, 'update'])->name('preferences.update'); // @phase 5.3

    Route::get('/paket-layanan', [PersonalBusinessController::class, 'index'])->middleware(['role:personal,teacher,institution_admin,superadmin','feature:business_center'])->name('business.index'); // @phase 5.0
    Route::post('/paket-layanan/{plan}/aktifkan', [PersonalBusinessController::class, 'subscribe'])->middleware(['role:personal,teacher,institution_admin,superadmin','feature:business_center','throttle:4,10'])->name('business.subscribe'); // @phase 5.0
    Route::get('/infak', [InfaqController::class, 'index'])->name('infaq.index'); // @phase 6.0
    Route::post('/infak', [InfaqController::class, 'store'])->middleware('throttle:4,10')->name('infaq.store'); // @phase 6.0
    Route::put('/infak/{transaction}/persetujuan-nama', [InfaqController::class, 'consent'])->name('infaq.consent');
    Route::get('/infak/bukti-penerimaan/{transaction}', [InfaqReceiptController::class, 'show'])->name('infaq.receipt');
    Route::get('/infak/bukti-asli/{evidence}', [InfaqEvidenceController::class, 'original'])->middleware('permission:infaq.audit.view')->name('infaq.evidence.original');
    Route::get('/infak/bukti-transfer/{transaction}', [InfaqEvidenceController::class, 'transferProof'])->middleware('permission:infaq.verify,infaq.audit.view')->name('infaq.transfer-proof');

    Route::prefix('personal')->name('personal.')->middleware('role:personal')->group(function (): void {
        Route::get('/', [PersonalController::class, 'index'])->middleware('permission:personal.use')->name('dashboard');
        Route::get('/program', [PersonalProgramController::class, 'index'])->middleware('permission:personal.use')->name('programs.index');
        Route::get('/ruang-belajar', [PersonalLearningHubController::class, 'index'])->middleware('permission:personal.use')->name('learning-hub.index'); // @phase 4.9
        Route::get('/pendamping-cerdas', [PersonalSmartAssistantController::class, 'index'])->middleware(['permission:personal.use','feature:smart_assistant'])->name('smart-assistant.index'); // @phase 5.2
        Route::post('/pendamping-cerdas/review', [PersonalSmartAssistantController::class, 'requestReview'])->middleware(['permission:personal.use','feature:smart_assistant','throttle:3,10'])->name('smart-assistant.review-request'); // @phase 5.2
        Route::post('/program-saya/{moduleKey}/aktifkan', [PersonalProgramController::class, 'enroll'])->middleware('permission:personal.use')->name('programs.enroll');
        Route::delete('/program-saya/{moduleKey}', [PersonalProgramController::class, 'deactivate'])->middleware('permission:personal.use')->name('programs.deactivate');
        Route::put('/onboarding', [PersonalController::class, 'onboarding'])->middleware('permission:personal.use')->name('onboarding');
        Route::post('/aktivitas', [PersonalController::class, 'storeActivity'])->middleware('permission:personal.use')->name('activities.store');
        Route::post('/target', [PersonalController::class, 'storeGoal'])->middleware('permission:personal.use')->name('goals.store');
        Route::put('/target/{goal}/selesai', [PersonalController::class, 'completeGoal'])->middleware('permission:personal.use')->name('goals.complete');
        Route::get('/perjalanan', [PersonalExperienceController::class, 'journey'])->middleware('permission:personal.use')->name('journey.index');
        Route::post('/check-in', [PersonalExperienceController::class, 'checkIn'])->middleware(['permission:personal.use','throttle:10,1'])->name('check-in.store');
        Route::post('/portofolio', [PersonalExperienceController::class, 'storePortfolio'])->middleware(['permission:personal.use','throttle:10,1'])->name('portfolio.store'); // @phase 4.5
        Route::get('/community', [PersonalCommunityController::class, 'index'])->middleware(['feature:community','personal.module:community'])->name('community.index');
        Route::post('/community', [PersonalCommunityController::class, 'store'])->middleware(['feature:community','personal.module:community','throttle:5,10'])->name('community.store');
        Route::get('/pembayaran', [PersonalPaymentController::class, 'index'])->middleware(['feature:payments','personal.module:payments'])->name('payments.index');
        Route::post('/pembayaran', [PersonalPaymentController::class, 'store'])->middleware(['feature:payments','personal.module:payments','throttle:3,10'])->name('payments.store');
        Route::get('/belajar', [PersonalGuidedLearningController::class, 'index'])->middleware(['permission:guided_learning.use','personal.module:guided_learning'])->name('learning.index');
        Route::post('/program/{program}/ikuti', [PersonalGuidedLearningController::class, 'enroll'])->middleware(['permission:guided_learning.use','personal.module:guided_learning'])->name('learning.enroll');
        Route::post('/program-saya/{enrollment}/setoran', [PersonalGuidedLearningController::class, 'submit'])->middleware(['permission:guided_learning.use','personal.module:guided_learning'])->name('learning.submit');
    });

    Route::get('/review-setoran-quran', [GuidedQuranReviewController::class, 'index'])->middleware('permission:guided_learning.review')->name('guided-review.index');
    Route::put('/review-setoran-quran/{submission}', [GuidedQuranReviewController::class, 'review'])->middleware('permission:guided_learning.review')->name('guided-review.review');

    Route::get('/buku-penghubung', [LiaisonController::class, 'index'])->middleware('permission:liaison.view')->name('liaison.index');
    Route::get('/buku-penghubung/buat', [LiaisonController::class, 'create'])->middleware('permission:liaison.manage')->name('liaison.create');
    Route::post('/buku-penghubung', [LiaisonController::class, 'store'])->middleware('permission:liaison.manage')->name('liaison.store');
    Route::get('/buku-penghubung/{thread}', [LiaisonController::class, 'show'])->middleware('permission:liaison.view')->name('liaison.show');
    Route::post('/buku-penghubung/{thread}/balas', [LiaisonController::class, 'reply'])->middleware('permission:liaison.manage')->name('liaison.reply');

    Route::get('/pengumuman', [ContentFeedController::class, 'announcements'])->middleware('permission:announcements.view')->name('feed.announcements');
    Route::post('/pengumuman/{announcement}/konfirmasi', [ContentFeedController::class, 'acknowledge'])->middleware('permission:announcements.view')->name('feed.announcements.acknowledge');
    Route::get('/pembinaan-jumat', [ContentFeedController::class, 'friday'])->middleware('permission:friday.view')->name('feed.friday');
    Route::get('/nilai/ikrar-santri', [ContentFeedController::class, 'pledge'])->name('feed.pledge');
    Route::get('/academy/belajar', [AcademyController::class, 'index'])->middleware(['permission:academy.view','feature:academy_portal','personal.module:academy'])->name('academy.index');
    Route::get('/academy/program/{program}', [AcademyController::class, 'program'])->middleware(['permission:academy.view','feature:academy_portal','personal.module:academy'])->name('academy.program');
    Route::get('/academy/materi/{lesson}', [AcademyController::class, 'lesson'])->middleware(['permission:academy.view','feature:academy_portal','personal.module:academy'])->name('academy.lesson');
    Route::post('/academy/materi/{lesson}/selesai', [AcademyController::class, 'complete'])->middleware(['permission:academy.view','feature:academy_portal','personal.module:academy'])->name('academy.lesson.complete');
    Route::post('/academy/kuis/{quiz}/jawab', [AcademyLmsController::class, 'submitQuiz'])->middleware(['permission:academy.view','feature:academy_portal','personal.module:academy'])->name('academy.quiz.submit');
    Route::post('/academy/worksheet/{worksheet}/selesai', [AcademyLmsController::class, 'submitWorksheet'])->middleware(['permission:academy.view','feature:academy_portal','personal.module:academy'])->name('academy.worksheet.submit');
    Route::get('/academy/sertifikat/{certificate}', [AcademyLmsController::class, 'certificate'])->middleware(['permission:academy.view','feature:academy_portal','personal.module:academy'])->name('academy.certificate');

    Route::get('/perjalanan-quran', [QuranJourneyController::class, 'index'])->middleware(['permission:quran.view','feature:quran_journey','personal.module:quran_journey'])->name('quran-journey.index');
    Route::post('/perjalanan-quran/program', [QuranJourneyController::class, 'start'])->middleware(['permission:quran.view','feature:quran_journey','personal.module:quran_journey'])->name('quran-journey.programs.start');
    Route::put('/perjalanan-quran/program/{enrollment}/langkah', [QuranJourneyController::class, 'step'])->middleware(['permission:quran.view','feature:quran_journey','personal.module:quran_journey'])->name('quran-journey.programs.step');

    Route::get('/latihan-quran', [QuranPracticeController::class, 'index'])->middleware(['permission:quran.view','feature:quran_audio','personal.module:quran_practice'])->name('quran-practice.index');
    Route::get('/latihan-quran/playlist', [QuranPracticeController::class, 'playlist'])->middleware(['permission:quran.view','feature:quran_audio','personal.module:quran_practice'])->name('quran-practice.playlist');
    Route::get('/latihan-quran/target/{target}', [QuranPracticeController::class, 'target'])->middleware(['permission:quran.view','feature:quran_audio','personal.module:quran_practice'])->name('quran-practice.target');
    Route::post('/latihan-quran/sesi', [QuranPracticeController::class, 'startSession'])->middleware(['permission:quran.view','feature:quran_audio','personal.module:quran_practice'])->name('quran-practice.sessions.start');
    Route::put('/latihan-quran/sesi/{session}/selesai', [QuranPracticeController::class, 'completeSession'])->middleware(['permission:quran.view','feature:quran_audio','personal.module:quran_practice'])->name('quran-practice.sessions.complete');
    Route::get('/media/submission/{submission}', [MediaController::class, 'submission'])->middleware('permission:media.view')->name('media.submission');
    Route::get('/media/guided-submission/{guidedSubmission}', [MediaController::class, 'guidedSubmission'])->name('media.guided-submission');
    Route::get('/media/guided-feedback/{guidedReview}', [MediaController::class, 'guidedFeedback'])->name('media.guided-feedback');
    Route::get('/media/liaison/{message}', [MediaController::class, 'liaison'])->middleware('permission:media.view')->name('media.liaison');
    Route::get('/media/announcement/{announcement}', [MediaController::class, 'announcement'])->middleware('permission:media.view')->name('media.announcement');
    Route::get('/media/friday/{session}', [MediaController::class, 'friday'])->middleware('permission:media.view')->name('media.friday');
    Route::get('/media/assets/{mediaAsset}', [MediaController::class, 'adminAsset'])->middleware('permission:media.manage')->name('media.asset');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::middleware('role:superadmin')->group(function (): void {
            Route::get('/workspaces', [AdminWorkspaceController::class, 'index'])->name('workspaces.index');
            Route::put('/workspaces/{institution}/status', [AdminWorkspaceController::class, 'status'])->name('workspaces.status');
            Route::get('/pemulihan-data', [RecoveryController::class, 'index'])->middleware('permission:backup.manage')->name('recovery.index');
            Route::post('/pemulihan-data', [RecoveryController::class, 'store'])->middleware(['permission:backup.manage','throttle:3,10'])->name('recovery.store');
            Route::put('/pemulihan-data/{restoreRequest}/setujui', [RecoveryController::class, 'approve'])->middleware(['permission:backup.manage','throttle:5,10'])->name('recovery.approve');
            Route::put('/pemulihan-data/{restoreRequest}/simulasi', [RecoveryController::class, 'simulation'])->middleware(['permission:backup.manage','throttle:5,10'])->name('recovery.simulation');
        });
        Route::middleware('role:superadmin,institution_admin')->group(function (): void {
            Route::get('/suite-lembaga', [InstitutionSuiteController::class, 'index'])->name('institution-suite.index');
            Route::post('/suite-lembaga/undangan', [InstitutionSuiteController::class, 'invite'])->middleware('throttle:10,1')->name('institution-suite.invitations.store');
            Route::delete('/suite-lembaga/undangan/{invitation}', [InstitutionSuiteController::class, 'revoke'])->name('institution-suite.invitations.destroy');
            Route::put('/suite-lembaga/anggota/{membership}', [InstitutionSuiteController::class, 'updateMember'])->name('institution-suite.members.update');
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
            Route::get('/ekosistem', [AdminEcosystemController::class, 'index'])->middleware('permission:features.manage')->name('ecosystem.index');
            Route::get('/bisnis', [AdminBusinessController::class, 'index'])->middleware('permission:features.manage')->name('business.index'); // @phase 5.0
            Route::get('/infak', [AdminInfaqController::class, 'index'])->middleware('permission:infaq.verify,infaq.audit.view')->name('infaq.index'); // @phase 6.1
            Route::put('/infak/{transaction}', [AdminInfaqController::class, 'update'])->middleware(['permission:infaq.verify','throttle:20,1'])->name('infaq.update'); // @phase 6.1
            Route::put('/bisnis/paket/{plan}', [AdminBusinessController::class, 'updatePlan'])->middleware('permission:features.manage')->name('business.plans.update'); // @phase 5.0
            Route::put('/bisnis/pembayaran/{transaction}', [AdminBusinessController::class, 'payment'])->middleware('permission:features.manage')->name('business.payments.update'); // @phase 5.0
            Route::get('/operasional-saas', [AdminOperationsController::class, 'index'])->middleware('permission:features.manage')->name('operations.index'); // @phase 5.1
            Route::post('/operasional-saas/periksa', [AdminOperationsController::class, 'run'])->middleware(['permission:features.manage','throttle:5,1'])->name('operations.run'); // @phase 5.1
            Route::put('/ekosistem/personal/{user}', [AdminEcosystemController::class, 'access'])->middleware('permission:features.manage')->name('ecosystem.personal.access');
            Route::post('/ekosistem/community-spaces', [AdminEcosystemController::class, 'storeSpace'])->middleware('permission:features.manage')->name('ecosystem.community-spaces.store');
            Route::put('/ekosistem/community-spaces/{space}', [AdminEcosystemController::class, 'updateSpace'])->middleware('permission:features.manage')->name('ecosystem.community-spaces.update');
            Route::put('/ekosistem/community/{post}', [AdminEcosystemController::class, 'moderate'])->middleware('permission:features.manage')->name('ecosystem.community.moderate');
            Route::put('/ekosistem/pembayaran/{transaction}', [AdminEcosystemController::class, 'payment'])->middleware('permission:features.manage')->name('ecosystem.payments.update');
            Route::post('/platform/branches', [PlatformController::class, 'storeBranch'])->middleware('permission:features.manage')->name('platform.branches.store');
            Route::put('/platform/branches/{branch}', [PlatformController::class, 'updateBranch'])->middleware('permission:features.manage')->name('platform.branches.update');
            Route::post('/platform/periods', [PlatformController::class, 'storePeriod'])->middleware('permission:academic.manage')->name('platform.periods.store');
            Route::put('/platform/features/{featureFlag}', [PlatformController::class, 'updateFeature'])->middleware('permission:features.manage')->name('platform.features.update');

            Route::get('/communications', [AdminCommunicationController::class, 'index'])->middleware(['permission:integrations.manage','feature:api_integrations'])->name('communications.index');
            Route::put('/communications/connections/{connection}', [AdminCommunicationController::class, 'updateConnection'])->middleware(['permission:integrations.manage','feature:api_integrations'])->name('communications.connections.update');
            Route::post('/communications/connections/{connection}/test', [AdminCommunicationController::class, 'test'])->middleware(['permission:integrations.manage','feature:api_integrations','throttle:5,1'])->name('communications.connections.test');
            Route::put('/communications/templates/{template}', [AdminCommunicationController::class, 'updateTemplate'])->middleware(['permission:integrations.manage','feature:api_integrations'])->name('communications.templates.update');
            Route::post('/communications/deliveries/{delivery}/retry', [AdminCommunicationController::class, 'retry'])->middleware(['permission:integrations.manage','feature:api_integrations','throttle:10,1'])->name('communications.deliveries.retry');

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

        Route::middleware('role:superadmin,institution_admin,head,auditor')->group(function (): void {
            Route::middleware('feature:v610_pilot')->prefix('infak-transparan')->name('infaq.')->group(function (): void {
                Route::get('/kebijakan', [InfaqPolicyController::class, 'edit'])->middleware('permission:infaq.policy.manage')->name('policy.edit');
                Route::put('/kebijakan', [InfaqPolicyController::class, 'update'])->middleware('permission:infaq.policy.manage')->name('policy.update');
                Route::get('/realisasi', [InfaqRealisationController::class, 'index'])->middleware('permission:infaq.realisation.manage,infaq.realisation.approve,infaq.audit.view')->name('realisations.index');
                Route::post('/realisasi', [InfaqRealisationController::class, 'store'])->middleware(['permission:infaq.realisation.manage','throttle:10,1'])->name('realisations.store');
                Route::put('/realisasi/{realisation}/review', [InfaqRealisationController::class, 'review'])->middleware(['permission:infaq.realisation.approve','throttle:20,1'])->name('realisations.review');
                Route::put('/realisasi/{realisation}/ajukan-ulang', [InfaqRealisationController::class, 'resubmit'])->middleware(['permission:infaq.realisation.manage','throttle:10,1'])->name('realisations.resubmit');
                Route::post('/pemindahan', [InfaqTransferController::class, 'store'])->middleware(['permission:infaq.realisation.manage','throttle:10,1'])->name('transfers.store');
                Route::put('/pemindahan/{fundTransfer}/review', [InfaqTransferController::class, 'review'])->middleware(['permission:infaq.realisation.approve','throttle:20,1'])->name('transfers.review');
                Route::get('/laporan', [InfaqReportController::class, 'index'])->middleware('permission:infaq.report.manage,infaq.audit.view')->name('reports.index');
                Route::post('/laporan', [InfaqReportController::class, 'store'])->middleware(['permission:infaq.report.manage','throttle:5,1'])->name('reports.store');
                Route::post('/laporan/koreksi', [InfaqReportController::class, 'correction'])->middleware(['permission:infaq.report.manage','throttle:5,1'])->name('reports.correction');
            });
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
            Route::post('/academy/prerequisites', [AdminAcademyController::class, 'storePrerequisite'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.prerequisites.store');
            Route::delete('/academy/prerequisites/{prerequisite}', [AdminAcademyController::class, 'destroyPrerequisite'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.prerequisites.destroy');
            Route::post('/academy/quizzes', [AdminAcademyController::class, 'storeQuiz'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.quizzes.store');
            Route::post('/academy/quiz-questions', [AdminAcademyController::class, 'storeQuizQuestion'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.quiz-questions.store');
            Route::delete('/academy/quiz-questions/{question}', [AdminAcademyController::class, 'destroyQuizQuestion'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.quiz-questions.destroy');
            Route::post('/academy/worksheets', [AdminAcademyController::class, 'storeWorksheet'])->middleware(['permission:academy.manage','feature:academy_portal'])->name('academy.worksheets.store');

            Route::get('/family-teacher', [AdminFamilyTeacherController::class, 'index'])->middleware(['permission:academy.manage','feature:family_learning'])->name('family-teacher.index');
            Route::post('/family-teacher/competencies', [AdminFamilyTeacherController::class, 'storeCompetency'])->middleware(['permission:academy.manage','feature:family_learning'])->name('family-teacher.competencies.store');
            Route::put('/family-teacher/competencies/{competency}', [AdminFamilyTeacherController::class, 'updateCompetency'])->middleware(['permission:academy.manage','feature:family_learning'])->name('family-teacher.competencies.update');
            Route::put('/family-teacher/progress/{progress}/review', [AdminFamilyTeacherController::class, 'reviewProgress'])->middleware(['permission:academy.manage','feature:family_learning'])->name('family-teacher.progress.review');

            Route::get('/quran-library', [QuranLibraryController::class, 'index'])->middleware(['permission:quran.manage','feature:quran_audio'])->name('quran-library.index');
            Route::post('/quran-library/sync-corpus', [QuranLibraryController::class, 'syncCorpus'])->middleware(['permission:quran.manage','feature:quran_audio'])->name('quran-library.sync-corpus');
            Route::post('/quran-library/sync', [QuranLibraryController::class, 'sync'])->middleware(['permission:quran.manage','feature:quran_audio'])->name('quran-library.sync');
            Route::post('/quran-library/videos', [QuranLibraryController::class, 'storeVideo'])->middleware(['permission:quran.manage','feature:quran_audio'])->name('quran-library.videos.store');
            Route::put('/quran-library/videos/{video}', [QuranLibraryController::class, 'updateVideo'])->middleware(['permission:quran.manage','feature:quran_audio'])->name('quran-library.videos.update');

            Route::get('/guided-learning', [GuidedQuranProgramController::class, 'index'])->middleware('permission:guided_learning.manage')->name('guided-learning.index');
            Route::post('/guided-learning/programs', [GuidedQuranProgramController::class, 'storeProgram'])->middleware('permission:guided_learning.manage')->name('guided-learning.programs.store');
            Route::post('/guided-learning/programs/{program}/reviewers', [GuidedQuranProgramController::class, 'assignReviewer'])->middleware('permission:guided_learning.manage')->name('guided-learning.reviewers.store');
            Route::post('/guided-learning/programs/{program}/students', [GuidedQuranProgramController::class, 'enrollStudent'])->middleware('permission:guided_learning.manage')->name('guided-learning.students.store');

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
        Route::get('/pendamping-cerdas', [SmartAssistantReviewController::class, 'index'])->middleware('feature:smart_assistant')->name('smart-assistant.index'); // @phase 5.2
        Route::put('/pendamping-cerdas/{draft}', [SmartAssistantReviewController::class, 'review'])->middleware('feature:smart_assistant')->name('smart-assistant.review'); // @phase 5.2
        Route::get('/academy', [AcademyRecommendationController::class, 'index'])->middleware(['feature:parent_academy','permission:academy.view'])->name('academy.index');
        Route::post('/academy/recommendations', [AcademyRecommendationController::class, 'store'])->middleware(['feature:parent_academy','permission:academy.view'])->name('academy.recommendations.store');
        Route::get('/family-learning', [FamilyTeacherController::class, 'index'])->middleware(['feature:family_learning','permission:academy.view'])->name('family-learning.index');
        Route::post('/family-learning/activities', [FamilyTeacherController::class, 'storeActivity'])->middleware(['feature:family_learning','permission:academy.view'])->name('family-learning.activities.store');
        Route::put('/family-learning/activities/{activity}/review', [FamilyTeacherController::class, 'reviewActivity'])->middleware(['feature:family_learning','permission:academy.view'])->name('family-learning.activities.review');
        Route::put('/family-learning/competencies/{competency}', [FamilyTeacherController::class, 'submitCompetency'])->middleware(['feature:family_learning','permission:academy.view'])->name('family-learning.competencies.submit');

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
        Route::post('/meetings/{meeting}/quick-memorization', [MeetingController::class, 'storeQuickMemorization'])->middleware(['permission:learning.manage', 'throttle:60,1'])->name('meetings.quick-memorization.store');
        Route::post('/meetings/{meeting}/quick-murajaah', [MeetingController::class, 'storeQuickMurajaah'])->middleware(['permission:learning.manage', 'throttle:60,1'])->name('meetings.quick-murajaah.store');
        Route::put('/meetings/{meeting}/finish', [MeetingController::class, 'finish'])->middleware('permission:meetings.manage')->name('meetings.finish');

        Route::get('/learning-plan', [LearningPlanController::class, 'index'])->middleware('permission:learning.manage')->name('learning-plan.index');
        Route::post('/learning-plan/targets', [LearningPlanController::class, 'storeTarget'])->middleware('permission:learning.manage')->name('learning-plan.targets.store');
        Route::put('/learning-plan/targets/{target}', [LearningPlanController::class, 'updateTarget'])->middleware('permission:learning.manage')->name('learning-plan.targets.update');
        Route::post('/learning-plan/observations', [LearningPlanController::class, 'storeObservation'])->middleware('permission:learning.manage')->name('learning-plan.observations.store');

        Route::get('/personal-learning', [PersonalLearningController::class, 'index'])->middleware('permission:learning.manage')->name('personal-learning.index');
        Route::post('/personal-learning/{student}/recommendations', [PersonalLearningController::class, 'generate'])->middleware('permission:learning.manage')->name('personal-learning.recommendations.generate');
        Route::put('/personal-learning/recommendations/{insight}', [PersonalLearningController::class, 'review'])->middleware('permission:learning.manage')->name('personal-learning.recommendations.review');

        Route::get('/quran-journey', [TeacherQuranJourneyController::class, 'index'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.index');
        Route::get('/quran-journey/students/{student}', [TeacherQuranJourneyController::class, 'student'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.student');
        Route::post('/quran-journey/students/{student}/initialize', [TeacherQuranJourneyController::class, 'initialize'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.initialize');
        Route::put('/quran-journey/students/{student}/cadence', [TeacherQuranJourneyController::class, 'updateCadence'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.cadence.update');
        Route::post('/quran-journey/students/{student}/advance', [TeacherQuranJourneyController::class, 'advance'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.advance');
        Route::post('/quran-journey/students/{student}/portions', [TeacherQuranJourneyController::class, 'storePortion'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.portions.store');
        Route::post('/quran-journey/students/{student}/line-portions', [TeacherQuranJourneyController::class, 'storeLinePortion'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.line-portions.store');
        Route::post('/quran-journey/students/{student}/page-portions', [TeacherQuranJourneyController::class, 'storePagePortion'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.page-portions.store');
        Route::post('/quran-journey/students/{student}/milestones/current-juz', [TeacherQuranJourneyController::class, 'currentJuzMilestone'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.milestones.current-juz');
        Route::post('/quran-journey/students/{student}/milestones', [TeacherQuranJourneyController::class, 'milestone'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.milestones.store');
        Route::post('/quran-journey/students/{student}/milestones/{milestone}/retention', [TeacherQuranJourneyController::class, 'retention'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.retention.store');
        Route::post('/quran-journey/students/{student}/programs', [TeacherQuranJourneyController::class, 'assignProgram'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.programs.store');
        Route::put('/quran-journey/students/{student}/programs/{enrollment}/step', [TeacherQuranJourneyController::class, 'programStep'])->middleware(['permission:learning.manage','feature:quran_journey'])->name('quran-journey.programs.step');

        Route::get('/tahfizh', [TahfizhController::class, 'index'])->middleware('permission:learning.manage')->name('tahfizh.index');
        Route::get('/tahfizh/students/{student}', [TahfizhController::class, 'student'])->middleware('permission:learning.manage')->name('tahfizh.student');
        Route::post('/tahfizh/cycles', [TahfizhController::class, 'storeCycle'])->middleware('permission:learning.manage')->name('tahfizh.cycles.store');
        Route::post('/tahfizh/students/{student}/memorization', [TahfizhController::class, 'storeMemorization'])->middleware('permission:learning.manage')->name('tahfizh.memorization.store');
        Route::post('/tahfizh/students/{student}/murajaah', [TahfizhController::class, 'storeMurajaah'])->middleware('permission:learning.manage')->name('tahfizh.murajaah.store');
        Route::post('/tahfizh/students/{student}/quick-memorization', [TahfizhController::class, 'storeQuickMemorization'])->middleware(['permission:learning.manage', 'throttle:60,1'])->name('tahfizh.quick-memorization.store');
        Route::post('/tahfizh/students/{student}/quick-murajaah', [TahfizhController::class, 'storeQuickMurajaah'])->middleware(['permission:learning.manage', 'throttle:60,1'])->name('tahfizh.quick-murajaah.store');
        Route::put('/tahfizh/cycles/{cycle}', [TahfizhController::class, 'updateCycle'])->middleware('permission:learning.manage')->name('tahfizh.cycles.update');
        Route::post('/tahfizh/reviews', [TahfizhController::class, 'storeReviewPlan'])->middleware('permission:learning.manage')->name('tahfizh.reviews.store');
        Route::put('/tahfizh/reviews/{plan}', [TahfizhController::class, 'updateReviewPlan'])->middleware('permission:learning.manage')->name('tahfizh.reviews.update');
        Route::put('/tahfizh/errors/{error}/resolve', [TahfizhController::class, 'resolveError'])->middleware('permission:learning.manage')->name('tahfizh.errors.resolve');
        Route::put('/tahfizh/students/{student}/focus', [MemorizationFocusController::class, 'update'])->middleware('permission:learning.manage')->name('tahfizh.focus.update');
        Route::post('/tahfizh/students/{student}/assessments', [MemorizationFocusController::class, 'assess'])->middleware(['permission:learning.manage', 'throttle:10,1'])->name('tahfizh.assessments.store');

        Route::get('/assignments', [AssignmentController::class, 'index'])->middleware('permission:assignments.view')->name('assignments.index');
        Route::get('/assignments/create', [AssignmentController::class, 'create'])->middleware('permission:assignments.manage')->name('assignments.create');
        Route::post('/assignments', [AssignmentController::class, 'store'])->middleware('permission:assignments.manage')->name('assignments.store');
        Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->middleware('permission:assignments.view')->name('assignments.show');
        Route::put('/submissions/{submission}/review', [AssignmentController::class, 'review'])->middleware('permission:assignments.review')->name('submissions.review');
    });

    Route::prefix('guardian')->name('guardian.')->middleware('role:guardian')->group(function (): void {
        Route::get('/children', [PortalController::class, 'children'])->middleware('permission:students.view')->name('children.index');
        Route::get('/children/{student}', [PortalController::class, 'child'])->middleware('permission:students.view')->name('children.show');
        Route::get('/tasks', [PortalController::class, 'tasks'])->middleware('permission:assignments.view')->name('tasks.index');
        Route::get('/tasks/{recipient}', [PortalController::class, 'task'])->middleware('permission:assignments.view')->name('tasks.show');
        Route::post('/tasks/{recipient}/submit', [PortalController::class, 'submit'])->middleware('permission:assignments.submit')->name('tasks.submit');
        Route::get('/family-learning', [FamilyLearningController::class, 'index'])->middleware(['feature:family_learning','permission:academy.view'])->name('family-learning.index');
        Route::put('/family-learning/{activity}/complete', [FamilyLearningController::class, 'complete'])->middleware(['feature:family_learning','permission:academy.view'])->name('family-learning.complete');
    });
});
