<?php

use App\Models\AcademyLesson;
use App\Models\AcademyProgram;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\LearningGroup;
use App\Models\QuranAudioSource;
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

Artisan::command('sullam:about', function (): void {
    $this->info('Sullamul Hifz v2.1.0 — Unified Platform & Secure Media.');
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


Artisan::command('sullam:ensure-quran-audio {--institution=}', function (): int {
    if (! Schema::hasTable('quran_audio_sources') || ! Schema::hasTable('quran_ayah_timings')) {
        $this->error('Tabel Quran Learning belum tersedia. Jalankan migration terlebih dahulu.');
        return 1;
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
                ->whereBetween('surah_id', [78, 114])
                ->count() >= QuranAudioSyncService::JUZ_30_AYAH_COUNT);

        if ($complete) {
            $defaultSource = $sources->firstWhere('is_default', true) ?: $sources->first();
            $service->seedPresets($institution, $defaultSource);
            foreach ($sources->sortByDesc('is_default') as $source) {
                $count = QuranAyahTiming::query()
                    ->where('quran_audio_source_id', $source->id)
                    ->whereBetween('surah_id', [78, 114])
                    ->count();
                $this->info("{$institution->name}: {$source->reciter_name} lengkap ({$count}/564)." . ($source->is_default ? ' [utama]' : ''));
            }
            continue;
        }

        $this->info("{$institution->name}: sinkronisasi dua qari Juz 30 dimulai...");
        try {
            $result = $service->syncInstitution($institution);
            foreach ($result['sources'] as $sourceResult) {
                $this->info("{$institution->name}: {$sourceResult['reciter_name']} {$sourceResult['timings']}/564 timing." . ($sourceResult['is_default'] ? ' [utama]' : ''));
            }
            $this->info("Total: {$result['total_timings']}/{$result['expected_timings']} timing, {$result['pages']} halaman, {$result['presets']} preset.");
            if ($result['failed_surahs']) {
                $hasFailure = true;
                $this->warn('Bagian yang perlu dicoba ulang: '.implode('; ', $result['failed_surahs']));
            }
        } catch (Throwable $exception) {
            report($exception);
            $hasFailure = true;
            $this->warn("{$institution->name}: sinkronisasi belum lengkap — {$exception->getMessage()}");
        }
    }

    return $hasFailure ? 1 : 0;
})->purpose('Mengisi timing Juz 30 untuk Al-Husary dan Al-Minshawi secara idempoten');

Artisan::command('sullam:verify-quran-learning', function (): int {
    $required = [
        'quran_audio_sources',
        'quran_ayah_timings',
        'quran_video_resources',
        'quran_practice_presets',
        'quran_practice_sessions',
    ];
    $missing = collect($required)->reject(fn (string $table): bool => Schema::hasTable($table));

    if ($missing->isNotEmpty()) {
        $this->error('Tabel Quran Learning belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $rows = [];
    $allComplete = true;
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
            $timings = QuranAyahTiming::query()
                ->where('quran_audio_source_id', $source->id)
                ->whereBetween('surah_id', [78, 114])
                ->count();
            $complete = $timings >= 564;
            $allComplete = $allComplete && $complete;
            $rows[] = [
                $institution->name,
                $source->reciter_name,
                $source->is_default ? 'Utama' : 'Tambahan',
                $timings.'/564',
                $complete ? 'Lengkap' : 'Perlu sinkronisasi',
            ];
        }
    }

    $this->table(['Lembaga', 'Qari', 'Peran', 'Timing', 'Status'], $rows);
    $this->info('Struktur Quran Learning v1.6.1 siap: Al-Husary sebagai qari utama dan Al-Minshawi sebagai pilihan murajaah.');

    if (! $allComplete) {
        $this->warn('Timing belum lengkap. Aplikasi tetap dijalankan dan sinkronisasi dilanjutkan di latar belakang.');
    }

    return 0;
})->purpose('Memeriksa dua sumber qari dan kelengkapan timing Juz 30 v1.6.1');

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


Schedule::command('sullam:purge-expired-media')->dailyAt('02:30')->withoutOverlapping();
