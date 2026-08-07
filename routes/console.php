<?php

use App\Models\AcademyLesson;
use App\Models\AcademyProgram;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\LearningGroup;
use App\Models\QuranAudioSource;
use App\Models\QuranAyah;
use App\Models\QuranAyahTiming;
use App\Models\QuranRubu;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Schedule;
use App\Services\QuranAudioSyncService;
use App\Services\QuranCorpusSyncService;
use App\Services\RoadmapStatusService;

Artisan::command('sullam:about', function (): void {
    $this->info('Sullamul Hifz v2.5.0 — Tahfizh Learning Engine.');
})->purpose('Menampilkan identitas aplikasi');

Artisan::command('sullam:reset-admin {--email=} {--password=}', function (): int {
    $email = $this->option('email') ?: env('INITIAL_ADMIN_EMAIL');
    $password = $this->option('password') ?: env('INITIAL_ADMIN_PASSWORD');

    if (! $email || ! $password) {
        $this->error('Email atau password admin belum tersedia. Isi INITIAL_ADMIN_EMAIL dan INITIAL_ADMIN_PASSWORD.');
        return 1;
    }

    if (strlen((string) $password) < 12
        || ! preg_match('/[A-Z]/', (string) $password)
        || ! preg_match('/[a-z]/', (string) $password)
        || ! preg_match('/[0-9]/', (string) $password)) {
        $this->error('Password admin minimal 12 karakter dan wajib memuat huruf besar, huruf kecil, serta angka.');
        return 1;
    }

    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("Akun admin {$email} tidak ditemukan. Jalankan seeder produksi terlebih dahulu.");
        return 1;
    }

    $user->forceFill([
        'password' => Hash::make($password),
        'must_change_password' => true,
        'status' => 'active',
    ])->save();

    $this->info("Password admin {$email} berhasil direset dari Environment Variables.");
    $this->warn('Login dengan INITIAL_ADMIN_PASSWORD, lalu ganti password saat diminta.');
    return 0;
})->purpose('Mereset password admin dari Environment Variables');

Artisan::command('sullam:verify-installation', function (): int {
    $studentCount = Student::where('status', 'active')->count();
    $guardianCount = Guardian::where('status', 'active')->count();
    $teacherCount = Teacher::where('status', 'active')->count();

    $tahfizhAGroup = LearningGroup::where('code', 'TAHFIZH-A')->first();
    $tahfizhBGroup = LearningGroup::where('code', 'TAHFIZH-B')->first();

    $tahfizhA = $tahfizhAGroup ? $tahfizhAGroup->activeMemberships()->count() : 0;
    $tahfizhB = $tahfizhBGroup ? $tahfizhBGroup->activeMemberships()->count() : 0;

    $this->table(
        ['Komponen', 'Jumlah', 'Target'],
        [
            ['Santri aktif', $studentCount, 88],
            ['Wali aktif', $guardianCount, 88],
            ['Guru aktif', $teacherCount, 4],
            ['Kelas Tahfizh A', $tahfizhA, 30],
            ['Kelas Tahfizh B', $tahfizhB, 27],
        ]
    );

    $valid = $studentCount >= 88
        && $guardianCount >= 88
        && $teacherCount >= 4
        && $tahfizhA === 30
        && $tahfizhB === 27;

    if (! $valid) {
        $this->error('Verifikasi instalasi belum memenuhi target.');
        return 1;
    }

    $this->info('Verifikasi instalasi berhasil.');
    return 0;
})->purpose('Memeriksa jumlah data awal dan kelas Tahfizh');

Artisan::command('sullam:verify-academic-core', function (): int {
    $required = ['quran_rubus', 'memorization_targets', 'learning_observations'];
    $missing = collect($required)->reject(fn (string $table) => Schema::hasTable($table));

    if ($missing->isNotEmpty()) {
        $this->error('Tabel Academic Core belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $rubuCount = QuranRubu::where('juz_number', 30)->where('status', 'active')->count();
    if ($rubuCount !== 8) {
        $this->error("Master rubu Juz 30 tidak lengkap. Ditemukan: {$rubuCount}.");
        return 1;
    }

    $this->info('Academic Core siap: 8 rubu Juz 30 dan tabel target/observasi tersedia.');
    return 0;
})->purpose('Memeriksa tabel dan master data Academic Core v1.5.0');


Artisan::command('sullam:ensure-quran-corpus {--force}', function (): int {
    if (! Schema::hasTable('quran_ayahs')) {
        $this->error('Tabel quran_ayahs belum tersedia. Jalankan migration terlebih dahulu.');
        return 1;
    }

    try {
        $result = app(QuranCorpusSyncService::class)->sync((bool) $this->option('force'));
        $this->table(['Korpus', 'Jumlah', 'Target'], [
            ['Surah', $result['surahs'], 114],
            ['Ayat', $result['ayahs'], 6236],
            ['Juz', $result['juz'], 30],
            ['Halaman', $result['pages'], 604],
            ['Rubu‘ al-Hizb', $result['rubus'], 240],
        ]);
        $this->info(($result['changed'] ? 'Korpus Full Qur’an diperbarui' : 'Korpus Full Qur’an sudah lengkap').'. Sumber: '.$result['source'].'.');
        return $result['complete'] ? 0 : 1;
    } catch (Throwable $exception) {
        report($exception);
        $this->warn('Korpus Full Qur’an belum lengkap — '.$exception->getMessage());
        return 1;
    }
})->purpose('Mengisi 114 surah, 6.236 ayat, 30 juz, 604 halaman dan 240 Rubu al-Hizb');

Artisan::command('sullam:ensure-quran-audio {--institution=}', function (): int {
    if (! Schema::hasTable('quran_audio_sources') || ! Schema::hasTable('quran_ayah_timings')) {
        $this->error('Tabel Quran Learning belum tersedia. Jalankan migration terlebih dahulu.');
        return 1;
    }

    $corpus = app(QuranCorpusSyncService::class);
    if (! $corpus->isComplete()) {
        $this->info('Korpus 30 juz belum lengkap; sinkronisasi korpus dijalankan lebih dahulu...');
        try {
            $corpus->sync();
        } catch (Throwable $exception) {
            report($exception);
            $this->warn('Audio belum dapat disinkronkan karena korpus belum lengkap — '.$exception->getMessage());
            return 1;
        }
    }

    $query = Institution::query()->where('status', 'active');
    if ($institutionId = $this->option('institution')) {
        $query->whereKey($institutionId);
    }

    $institutions = $query->get();
    if ($institutions->isEmpty()) {
        $this->warn('Tidak ada lembaga aktif untuk disinkronkan.');
        return 0;
    }

    $service = app(QuranAudioSyncService::class);
    $requiredIds = collect($service->sourceDefinitions())->pluck('external_id')->map(fn ($id) => (string) $id);
    $hasFailure = false;

    foreach ($institutions as $institution) {
        $sources = QuranAudioSource::query()
            ->where('institution_id', $institution->id)
            ->where('status', 'active')
            ->whereIn('external_id', $requiredIds)
            ->get();

        $complete = $sources->count() === $requiredIds->count()
            && $sources->every(fn (QuranAudioSource $source): bool => QuranAyahTiming::query()
                ->where('quran_audio_source_id', $source->id)
                ->count() >= QuranAudioSyncService::FULL_QURAN_AYAH_COUNT);

        if ($complete) {
            $defaultSource = $sources->firstWhere('is_default', true) ?: $sources->first();
            $service->seedPresets($institution, $defaultSource);
            foreach ($sources->sortByDesc('is_default') as $source) {
                $count = QuranAyahTiming::query()->where('quran_audio_source_id', $source->id)->count();
                $this->info("{$institution->name}: {$source->reciter_name} lengkap ({$count}/6236)." . ($source->is_default ? ' [utama]' : ''));
            }
            continue;
        }

        $this->info("{$institution->name}: sinkronisasi dua qari untuk 30 juz dimulai/resume...");
        try {
            $result = $service->syncInstitution($institution);
            foreach ($result['sources'] as $sourceResult) {
                $this->info("{$institution->name}: {$sourceResult['reciter_name']} {$sourceResult['timings']}/6236 timing." . ($sourceResult['is_default'] ? ' [utama]' : ''));
            }
            $this->info("Total: {$result['total_timings']}/{$result['expected_timings']} timing; {$result['presets']} preset.");
            if ($result['failed_surahs']) {
                $hasFailure = true;
                $this->warn('Surah yang perlu dicoba ulang: '.implode('; ', $result['failed_surahs']));
            }
        } catch (Throwable $exception) {
            report($exception);
            $hasFailure = true;
            $this->warn("{$institution->name}: sinkronisasi belum lengkap — {$exception->getMessage()}");
        }
    }

    return $hasFailure ? 1 : 0;
})->purpose('Mengisi timing Full Qur’an untuk Al-Husary dan Al-Minshawi secara idempoten/resume-safe');

Artisan::command('sullam:verify-quran-learning', function (): int {
    $required = [
        'quran_surahs', 'quran_ayahs', 'quran_reading_progress',
        'quran_audio_sources', 'quran_ayah_timings', 'quran_video_resources',
        'quran_practice_presets', 'quran_practice_sessions',
    ];
    $missing = collect($required)->reject(fn (string $table): bool => Schema::hasTable($table));

    if ($missing->isNotEmpty()) {
        $this->error('Struktur Full Qur’an belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $corpus = app(QuranCorpusSyncService::class)->status();
    $this->table(['Korpus', 'Tersedia', 'Target'], [
        ['Surah', $corpus['surahs'], 114],
        ['Ayat Uthmani', $corpus['ayahs'], 6236],
        ['Juz', $corpus['juz'], 30],
        ['Halaman', $corpus['pages'], 604],
        ['Rubu‘ al-Hizb', $corpus['rubus'], 240],
    ]);

    $rows = [];
    $allComplete = (bool) $corpus['complete'];
    foreach (Institution::query()->where('status', 'active')->get() as $institution) {
        $sources = QuranAudioSource::query()
            ->where('institution_id', $institution->id)
            ->where('status', 'active')
            ->whereIn('external_id', ['118', '112'])
            ->orderByDesc('is_default')
            ->get();

        if ($sources->count() !== 2) {
            $allComplete = false;
        }

        foreach ($sources as $source) {
            $timings = QuranAyahTiming::query()->where('quran_audio_source_id', $source->id)->count();
            $complete = $timings >= QuranAudioSyncService::FULL_QURAN_AYAH_COUNT;
            $allComplete = $allComplete && $complete;
            $rows[] = [
                $institution->name,
                $source->reciter_name,
                $source->is_default ? 'Utama' : 'Murāja‘ah',
                $timings.'/6236',
                $complete ? 'Lengkap' : 'Sedang dilengkapi',
            ];
        }
    }

    $this->table(['Lembaga', 'Qari', 'Peran', 'Timing', 'Status'], $rows);
    if ($allComplete) {
        $this->info('Full Qur’an Engine: korpus 30 juz dan dua qari telah lengkap.');
    } else {
        $this->warn('Full Qur’an Engine belum 100% secara data. Aplikasi tetap hidup; sinkronisasi dapat dilanjutkan di latar belakang.');
    }

    // Struktur valid berarti container boleh tetap hidup. Kelengkapan data dilaporkan lewat roadmap, bukan mematikan aplikasi.
    return 0;
})->purpose('Memeriksa korpus 30 juz dan dua sumber qari Full Qur’an v2.4.0');

Artisan::command('sullam:verify-tahfizh', function (): int {
    $required = [
        'tahsin_records','memorization_records','murajaah_records','memorization_targets',
        'tahfizh_learning_cycles','memorization_review_plans','quran_learning_error_items',
        'meetings','attendance_records',
    ];
    $missing = collect($required)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Struktur Tahfizh Learning Engine belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $columnsReady = Schema::hasColumn('memorization_records', 'delivery_mode')
        && Schema::hasColumn('memorization_records', 'learning_cycle_id')
        && Schema::hasColumn('memorization_records', 'next_review_date')
        && Schema::hasColumn('murajaah_records', 'review_plan_id');

    $institutions = Institution::query()->where('status', 'active')->get();
    $rows = [];
    foreach ($institutions as $institution) {
        $rows[] = [
            $institution->name,
            \App\Models\TahfizhLearningCycle::query()->where('institution_id', $institution->id)->count(),
            \App\Models\MemorizationReviewPlan::query()->where('institution_id', $institution->id)->where('status', 'scheduled')->count(),
            \App\Models\QuranLearningErrorItem::query()->where('institution_id', $institution->id)->whereNull('resolved_at')->count(),
            \App\Models\MemorizationRecord::query()->where('institution_id', $institution->id)->count(),
            \App\Models\MurajaahRecord::query()->where('institution_id', $institution->id)->count(),
        ];
    }
    $this->table(['Lembaga','Siklus','Review terjadwal','Fokus koreksi','Setoran','Murāja‘ah'], $rows);
    if (! $columnsReady) {
        $this->error('Kolom penghubung siklus/jadwal Tahfizh belum lengkap.');
        return 1;
    }
    $this->info('Tahfizh Learning Engine v2.5.0 siap untuk validasi guru–wali. Implementasi tidak dianggap 100% fase sampai launch checks Fase 3 selesai.');
    return 0;
})->purpose('Memeriksa fondasi siklus Tahfizh, setoran, Murāja‘ah, fokus koreksi dan jadwal penjagaan v2.5.0');

Artisan::command('sullam:roadmap-status {--institution=}', function (): int {
    $query = Institution::query()->where('status', 'active');
    if ($id = $this->option('institution')) {
        $query->whereKey($id);
    }

    foreach ($query->get() as $institution) {
        $this->newLine();
        $this->info('Roadmap '.$institution->name);
        $rows = collect(app(RoadmapStatusService::class)->phases($institution))->map(fn (array $phase): array => [
            $phase['number'], $phase['name'], $phase['implementation_pct'].'%', $phase['validation_pct'].'%', $phase['percent'].'%', strtoupper($phase['status']),
        ])->values()->all();
        $this->table(['Fase', 'Pengembangan', 'Implementasi', 'Validasi', 'Total', 'Status'], $rows);
    }
    return 0;
})->purpose('Menampilkan progres jujur 10 fase; 100% hanya setelah implementasi dan validasi lulus');

Artisan::command('sullam:verify-launch', function (): int {
    $requiredTables = [
        'launch_checks', 'meetings', 'attendance_records', 'tahsin_records',
        'memorization_records', 'murajaah_records', 'assignments', 'report_cards',
        'login_histories', 'activity_logs',
    ];
    $missing = collect($requiredTables)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Struktur peluncuran belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $checks = \App\Models\LaunchCheck::count();
    $this->table(['Komponen', 'Status'], [
        ['Academic Core', Schema::hasTable('memorization_targets') ? 'Siap' : 'Belum'],
        ['Quran Learning', Schema::hasTable('quran_ayah_timings') ? 'Siap' : 'Belum'],
        ['Operasional Harian', Schema::hasColumn('meetings','meeting_type') ? 'Siap' : 'Belum'],
        ['Rapor & Laporan', Schema::hasTable('report_cards') ? 'Siap' : 'Belum'],
        ['Kesiapan Launch', $checks.' pemeriksaan'],
    ]);
    $this->info('Fondasi TPA Launch v1.9.0 siap. v2.0.0 menambahkan Family Learning, mobile-first UX, dan Academy.');
    return 0;
})->purpose('Memeriksa struktur TPA Launch Complete v1.9.0');

Artisan::command('sullam:verify-academy', function (): int {
    $requiredTables = [
        'academy_programs', 'academy_modules', 'academy_lessons',
        'academy_lesson_progress', 'academy_recommendations',
    ];
    $missing = collect($requiredTables)->reject(fn (string $table): bool => Schema::hasTable($table));

    if ($missing->isNotEmpty()) {
        $this->error('Struktur Academy belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $institutions = Institution::query()->where('status', 'active')->get();
    $failed = false;
    $rows = [];

    foreach ($institutions as $institution) {
        $parent = AcademyProgram::query()
            ->where('institution_id', $institution->id)
            ->where('slug', 'parent-academy-rumah-qurani')
            ->where('status', 'published')
            ->first();
        $teacher = AcademyProgram::query()
            ->where('institution_id', $institution->id)
            ->where('slug', 'orientasi-guru-sullamul-hifz')
            ->where('status', 'published')
            ->first();

        $parentLessons = $parent ? AcademyLesson::query()
            ->whereHas('module', fn ($query) => $query->where('academy_program_id', $parent->id))
            ->where('status', 'published')->count() : 0;
        $teacherLessons = $teacher ? AcademyLesson::query()
            ->whereHas('module', fn ($query) => $query->where('academy_program_id', $teacher->id))
            ->where('status', 'published')->count() : 0;

        $requiredExpansionSlugs = [
            'stifin-sebagai-informasi-pendamping',
            'stifin-parenting-mendampingi-tanpa-membatasi',
            'hidup-bersama-al-quran',
            'pendidikan-anak-adab-keteladanan',
        ];
        $expansionCount = AcademyProgram::query()
            ->where('institution_id', $institution->id)
            ->where('status', 'published')
            ->whereIn('slug', $requiredExpansionSlugs)
            ->count();

        $ready = (bool) $parent && (bool) $teacher && $parentLessons >= 10 && $teacherLessons >= 5 && $expansionCount === count($requiredExpansionSlugs);
        $failed = $failed || ! $ready;
        $rows[] = [$institution->name, $parentLessons, $teacherLessons, $expansionCount.'/4', $ready ? 'Siap' : 'Perlu diperiksa'];
    }

    $this->table(['Lembaga', 'Parent Academy', 'Teacher Academy', 'E-course v2.2', 'Status'], $rows);
    if ($failed) {
        $this->warn('Academy belum memenuhi data awal v2.2.0. Jalankan AcademyLaunchV200Seeder dan AcademyExpansionV220Seeder.');
        return 1;
    }

    $this->info('Academy v2.2.0 siap: portal mandiri, Parent/Teacher Academy, STIFIn proporsional, Al-Qur’an, pendidikan anak, progress, dan rekomendasi tersedia.');
    return 0;
})->purpose('Memeriksa struktur dan konten awal Family Learning & Academy v2.0.0');


Artisan::command('sullam:verify-ecosystem', function (): int {
    $requiredTables = [
        'academy_learning_paths', 'academy_learning_path_items', 'academy_bookmarks', 'academy_reflections',
        'student_portfolios', 'community_spaces', 'community_posts', 'learning_insights', 'integration_connections',
    ];
    $missing = collect($requiredTables)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Fondasi ekosistem v2.3 belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $rows = [];
    foreach (Institution::query()->where('status', 'active')->get() as $institution) {
        $paths = \App\Models\AcademyLearningPath::query()->where('institution_id', $institution->id)->where('status', 'published')->count();
        $programs = AcademyProgram::query()->where('institution_id', $institution->id)->where('status', 'published')->count();
        $features = \App\Models\FeatureFlag::query()->where('institution_id', $institution->id)->count();
        $community = \App\Models\CommunitySpace::query()->where('institution_id', $institution->id)->count();
        $rows[] = [$institution->name, $programs, $paths, $features, $community, $paths >= 3 ? 'Siap' : 'Perlu seeder'];
    }
    $this->table(['Lembaga', 'Program Academy', 'Learning Path', 'Feature Flag', 'Community Draft', 'Status'], $rows);
    $this->info('Fondasi data ekosistem v2.3.0 tersedia. Status 10 fase ditentukan terpisah oleh sullam:roadmap-status dan tidak otomatis 100%.');
    return 0;
})->purpose('Memeriksa fondasi roadmap 10 fase Sullamul Hifz v2.3.0');

Schedule::command('sullam:purge-expired-media')->dailyAt('02:30')->withoutOverlapping();
