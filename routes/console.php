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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use App\Services\QuranAudioSyncService;
use App\Services\QuranCorpusSyncService;
use App\Services\RoadmapStatusService;
use App\Services\QuranDivisionService;
use App\Services\MushafLineService;

Artisan::command('sullam:about', function (): void {
    $release = trim((string) @file_get_contents(base_path('RELEASE'))) ?: 'unknown';
    $this->info('Sullamul Hifz '.$release.' — Qur’an learning ecosystem.');
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
        $this->error("Master segment legacy Juz 30 tidak lengkap. Ditemukan: {$rubuCount}.");
        return 1;
    }

    $this->info('Academic Core siap: 8 segment legacy Juz 30 dan tabel target/observasi tersedia. Rubu‘ al-Ḥizb standar dikelola oleh Qur’an Journey.');
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

Artisan::command('sullam:sync-quran-divisions', function (): int {
    $result = app(QuranDivisionService::class)->sync();
    $this->table(['Unit','Jumlah'], [
        ['Juz',$result['juz']],
        ['Ḥizb',$result['hizb']],
        ['Rubu‘ al-Ḥizb',$result['rubu']],
        ['Rukū‘',$result['ruku']],
        ['Fami Bisyauqin / Manzil',$result['fami_manzil']],
    ]);
    $ready = $result['juz'] >= 30 && $result['hizb'] >= 60 && $result['rubu'] >= 240 && $result['fami_manzil'] >= 7;
    if (! $ready) {
        $this->warn('Pembagian mushaf belum lengkap. Pastikan korpus Full Qur’an 6.236 ayat sudah tersinkron.');
        return 1;
    }
    $this->info('Peta pembagian mushaf siap: 30 Juz, 60 Ḥizb, 240 Rubu‘ al-Ḥizb, serta 7 manzil Fami Bisyauqin.');
    return 0;
})->purpose('Menyusun unit Juz, Hizb, Rubu, Ruku dan manzil Fami dari korpus Al-Qur’an');

Artisan::command('sullam:ensure-mushaf-lines {--force}', function (): int {
    if (! Schema::hasTable('quran_mushaf_lines')) {
        $this->error('Tabel quran_mushaf_lines belum tersedia. Jalankan migration v2.6.1 terlebih dahulu.');
        return 1;
    }

    $service = app(MushafLineService::class);
    $before = $service->status();
    $this->info('Mushaf Line Engine: '.$before['pages'].'/604 halaman sebelum sinkronisasi.');

    try {
        $result = $service->sync((bool) $this->option('force'));
    } catch (\Throwable $exception) {
        report($exception);
        $this->error('Sinkronisasi Mushaf Line gagal: '.$exception->getMessage());
        return 1;
    }

    $juz29 = $service->coverageForJuz(29);
    $juz28 = $service->coverageForJuz(28);
    $this->table(['Komponen','Nilai'], [
        ['Layout',$result['layout']],
        ['Sumber',$result['source']],
        ['Metode',$result['method']],
        ['Halaman',$result['pages'].'/604'],
        ['Baris tersimpan',$result['lines']],
        ['Juz 29 · halaman 15 slot',$juz29['complete_pages'].'/'.$juz29['expected_pages']],
        ['Juz 28 · halaman 15 slot',$juz28['complete_pages'].'/'.$juz28['expected_pages']],
    ]);

    if (! $result['complete'] || ! $juz29['complete'] || ! $juz28['complete']) {
        $this->warn('Layout belum 604/604 halaman. Sinkronisasi bersifat resume-safe dan halaman yang dibuka guru tetap dicoba on-demand.');
        return 1;
    }

    $this->info('Mushaf Line Engine lengkap: 604 halaman tersedia untuk validasi pola 3/5 baris.');
    return 0;
})->purpose('Menyinkronkan layout 15 slot fisik Mushaf Madinah untuk Marhalah 3/5 baris');

Artisan::command('sullam:verify-quran-journey', function (): int {
    $required = [
        'quran_journey_profiles','quran_journey_portions','memorization_milestones','memorization_retention_checks',
        'quran_division_units','quran_program_templates','quran_program_steps','quran_program_enrollments',
        'quran_program_progress','quran_heritage_terms','quran_mushaf_lines',
    ];
    $missing = collect($required)->reject(fn(string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Struktur Qur’an Journey belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $marhalah = \App\Models\MarhalahType::query()->where('status','active')->whereNotNull('juz_from')->count();
    $khatamSteps = \App\Models\QuranProgramTemplate::query()->where('code','khatam-30-hari')->first()?->steps()->count() ?? 0;
    $famiSteps = \App\Models\QuranProgramTemplate::query()->where('code','fami-bisyauqin')->first()?->steps()->count() ?? 0;
    $divisions = app(QuranDivisionService::class)->sync();
    $terms = \App\Models\QuranHeritageTerm::query()->where('status','active')->count();
    $lineService = app(MushafLineService::class);
    $mushafLine = $lineService->status();
    $juz29Line = $lineService->coverageForJuz(29);
    $juz28Line = $lineService->coverageForJuz(28);

    $this->table(['Komponen','Tersedia','Target'], [
        ['Marhalah berbasis Juz',$marhalah,6],
        ['Khatam 30 Hari',$khatamSteps,30],
        ['Fami Bisyauqin',$famiSteps,7],
        ['Juz',$divisions['juz'],30],
        ['Ḥizb',$divisions['hizb'],60],
        ['Rubu‘ al-Ḥizb',$divisions['rubu'],240],
        ['Warisan Ulama',$terms,10],
        ['Mushaf Line Engine',$mushafLine['pages'],604],
        ['Juz 29 · line-slot lengkap',$juz29Line['complete_pages'],$juz29Line['expected_pages']],
        ['Juz 28 · line-slot lengkap',$juz28Line['complete_pages'],$juz28Line['expected_pages']],
    ]);

    $portionReady = Schema::hasColumn('memorization_targets','quran_journey_portion_id')
        && Schema::hasColumn('memorization_targets','portion_confirmed')
        && Schema::hasColumn('memorization_targets','mushaf_page_number')
        && Schema::hasColumn('memorization_targets','start_word_location')
        && Schema::hasColumn('quran_journey_portions','start_line_number')
        && Schema::hasColumn('quran_journey_portions','start_word_location');
    $ready = $marhalah >= 6 && $khatamSteps >= 30 && $famiSteps >= 7 && $portionReady
        && $divisions['juz'] >= 30 && $divisions['hizb'] >= 60 && $divisions['rubu'] >= 240 && $terms >= 10
        && $mushafLine['complete'] && $juz29Line['complete'] && $juz28Line['complete'];
    if (! $ready) {
        $this->warn('Implementasi Qur’an Journey belum lengkap. Validasi fase tidak boleh ditutup.');
        return 1;
    }
    $this->info('Qur’an Journey v2.6.1 + Mushaf Line Engine siap untuk validasi produksi. 100% tetap menunggu seluruh launch check Fase 4.');
    return 0;
})->purpose('Memeriksa Marhalah, Mushaf Line Engine, milestone, program khatam dan Peta Mushaf Fase 4');

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

Artisan::command('sullam:verify-academy-lms', function (): int {
    $required = [
        'academy_prerequisites','academy_quizzes','academy_quiz_questions','academy_quiz_options',
        'academy_quiz_attempts','academy_quiz_answers','academy_worksheets','academy_worksheet_submissions','academy_certificates',
    ];
    $missing = collect($required)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Academy LMS 2.0 belum lengkap: '.$missing->implode(', '));
        return 1;
    }
    if (! class_exists(\App\Services\AcademyLmsService::class)) {
        $this->error('AcademyLmsService tidak tersedia.');
        return 1;
    }

    $this->table(['Komponen','Jumlah'], [
        ['Prerequisite', \App\Models\AcademyPrerequisite::query()->count()],
        ['Quiz', \App\Models\AcademyQuiz::query()->count()],
        ['Worksheet', \App\Models\AcademyWorksheet::query()->count()],
        ['Sertifikat', \App\Models\AcademyCertificate::query()->where('status','issued')->count()],
    ]);
    $this->info('Struktur Academy LMS 2.0 v2.7.0 siap. Launch check produksi tetap harus divalidasi manual.');
    return 0;
})->purpose('Memeriksa struktur Academy LMS 2.0 v2.7.0');

Artisan::command('sullam:verify-family-teacher', function (): int {
    $required = ['family_learning_activities','teacher_competencies','teacher_competency_progress'];
    $missing = collect($required)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Family & Teacher Ecosystem belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $this->table(['Komponen','Jumlah'], [
        ['Aktivitas keluarga', \App\Models\FamilyLearningActivity::query()->count()],
        ['Aktivitas selesai/review', \App\Models\FamilyLearningActivity::query()->whereIn('status',['completed','reviewed'])->count()],
        ['Kompetensi guru', \App\Models\TeacherCompetency::query()->where('status','active')->count()],
        ['Refleksi kompetensi', \App\Models\TeacherCompetencyProgress::query()->whereIn('status',['reflection_submitted','needs_follow_up','demonstrated'])->count()],
    ]);
    $this->info('Struktur Family & Teacher Ecosystem v2.8.0 siap. Validasi alur Parent↔Teacher dan guardrail STIFIn tetap harus dilakukan manual di produksi.');
    return 0;
})->purpose('Memeriksa struktur Family & Teacher Ecosystem v2.8.0');

Artisan::command('sullam:verify-personal-learning', function (): int {
    $required = ['learning_observations','learning_insights','student_marhalah_histories','learning_recommendation_reviews'];
    $missing = collect($required)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Personal Learning System belum lengkap: '.$missing->implode(', '));
        return 1;
    }
    if (! class_exists(\App\Services\PersonalLearningRecommendationService::class)) {
        $this->error('PersonalLearningRecommendationService tidak tersedia.');
        return 1;
    }

    $recommendations = \App\Models\LearningInsight::query()->where('insight_type','personal_recommendation');
    $stifinLeaks = (clone $recommendations)->where(function ($query): void {
        $query->whereRaw('LOWER(title) LIKE ?', ['%stifin%'])
            ->orWhereRaw('LOWER(summary) LIKE ?', ['%stifin%'])
            ->orWhereRaw('LOWER(CAST(evidence AS CHAR)) LIKE ?', ['%stifin%']);
    })->count();

    $this->table(['Komponen','Jumlah'], [
        ['Observasi belajar', \App\Models\LearningObservation::query()->count()],
        ['Rekomendasi personal', (clone $recommendations)->count()],
        ['Review/override guru', \App\Models\LearningRecommendationReview::query()->count()],
        ['Evidence/rekomendasi memuat STIFIn', $stifinLeaks],
    ]);

    if ($stifinLeaks > 0) {
        $this->error('Guardrail gagal: ditemukan rekomendasi personal yang membawa STIFIn sebagai evidence/isi rekomendasi.');
        return 1;
    }

    $this->info('Struktur Personal Learning System v2.9.0 siap. Teacher override tetap harus dibuktikan melalui smoke test produksi.');
    return 0;
})->purpose('Memeriksa Fase 7 Personal Learning System v2.9.0 dan guardrail evidence');

Artisan::command('sullam:verify-personal-mode', function (): int {
    $required = ['personal_profiles','personal_goals','personal_practice_entries'];
    $missing = collect($required)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty() || ! Schema::hasColumn('institutions','workspace_type') || ! Schema::hasColumn('institutions','owner_user_id')) {
        $this->error('Public Personal Mode belum lengkap: '.($missing->implode(', ') ?: 'kolom workspace Personal belum tersedia'));
        return 1;
    }

    $roleReady = DB::table('roles')->where('name','personal')->exists();
    $permissionReady = DB::table('permissions')->where('name','personal.use')->exists();
    $privacyIssues = DB::table('institutions')->where('workspace_type','personal')->where('privacy_mode','!=','private')->count();
    $ownershipIssues = DB::table('personal_profiles as pp')
        ->join('users as u','u.id','=','pp.user_id')
        ->whereColumn('pp.institution_id','!=','u.institution_id')->count();

    $servicePath = app_path('Services/PersonalJourneyService.php');
    $serviceUsesStifin = is_file($servicePath) && str_contains(strtolower((string) file_get_contents($servicePath)), 'stifin');

    $this->table(['Komponen','Jumlah'], [
        ['Workspace Personal', DB::table('institutions')->where('workspace_type','personal')->count()],
        ['Profil Personal', DB::table('personal_profiles')->count()],
        ['Target Personal', DB::table('personal_goals')->count()],
        ['Jurnal aktivitas Personal', DB::table('personal_practice_entries')->count()],
        ['Masalah ownership profil', $ownershipIssues],
        ['Workspace Personal tidak privat', $privacyIssues],
        ['Mesin arahan menyebut STIFIn', $serviceUsesStifin ? 1 : 0],
    ]);

    if (! $roleReady || ! $permissionReady || $ownershipIssues > 0 || $privacyIssues > 0 || $serviceUsesStifin) {
        $this->error('Guardrail Personal Mode belum lolos. Periksa role/permission, ownership, privasi, dan independensi STIFIn.');
        return 1;
    }

    $this->info('Struktur Public Self-Registration + Personal Mode v3.0.0 siap. Registrasi, isolasi dua akun, dan jurnal tetap harus dibuktikan lewat smoke test produksi.');
    return 0;
})->purpose('Memeriksa Public Self-Registration + Personal Mode v3.0.0');

Artisan::command('sullam:verify-guided-quran', function (): int {
    $required = [
        'guided_quran_programs', 'guided_quran_program_reviewers', 'guided_quran_enrollments',
        'quran_guided_submissions', 'quran_guided_submission_reviews',
    ];
    $missing = collect($required)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Guided Quran Learning belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $ownershipIssues = DB::table('quran_guided_submissions as s')
        ->join('guided_quran_enrollments as e', 'e.id', '=', 's.guided_quran_enrollment_id')
        ->where(function ($query): void {
            $query->whereColumn('s.learner_institution_id', '!=', 'e.learner_institution_id')
                ->orWhereColumn('s.student_id', '!=', 'e.student_id')
                ->orWhere(function ($q): void {
                    $q->whereNotNull('e.learner_user_id')->whereColumn('s.learner_user_id', '!=', 'e.learner_user_id');
                });
        })->count();

    $reviewerScopeIssues = DB::table('guided_quran_program_reviewers as r')
        ->join('guided_quran_programs as p', 'p.id', '=', 'r.guided_quran_program_id')
        ->join('users as u', 'u.id', '=', 'r.reviewer_user_id')
        ->whereColumn('u.institution_id', '!=', 'p.provider_institution_id')
        ->count();

    $personalRoleId = DB::table('roles')->where('name', 'personal')->value('id');
    $personalPermissions = $personalRoleId ? DB::table('role_permissions as rp')
        ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
        ->where('rp.role_id', $personalRoleId)
        ->whereIn('p.name', ['guided_learning.use', 'academy.view', 'quran.view'])
        ->distinct()->count('p.name') : 0;

    $this->table(['Komponen', 'Jumlah'], [
        ['Program Al-Qur’an', DB::table('guided_quran_programs')->count()],
        ['Enrollment program', DB::table('guided_quran_enrollments')->count()],
        ['Reviewer ditugaskan', DB::table('guided_quran_program_reviewers')->where('status', 'active')->count()],
        ['Setoran online', DB::table('quran_guided_submissions')->count()],
        ['Setoran menunggu review', DB::table('quran_guided_submissions')->where('review_status', 'pending')->count()],
        ['Feedback/review', DB::table('quran_guided_submission_reviews')->count()],
        ['Masalah ownership setoran', $ownershipIssues],
        ['Reviewer di luar penyelenggara', $reviewerScopeIssues],
        ['Izin Personal inti tersedia', $personalPermissions],
    ]);

    if ($ownershipIssues > 0 || $reviewerScopeIssues > 0 || $personalPermissions !== 3) {
        $this->error('Guardrail Guided Quran Learning belum lolos. Periksa ownership, reviewer, dan permission Personal.');
        return 1;
    }

    $this->info('Struktur Guided Quran Learning v3.1.1 siap. Setoran audio, review asatidz, feedback, dan isolasi dua akun tetap harus dibuktikan lewat smoke test produksi.');
    return 0;
})->purpose('Memeriksa program online, setoran, review asatidz, dan guardrail privasi v3.1.1');

Artisan::command('sullam:verify-personal-program-hub', function (): int {
    if (! Schema::hasTable('personal_module_enrollments')) {
        $this->error('Personal Program Hub belum siap: tabel personal_module_enrollments tidak tersedia.');
        return 1;
    }

    $allowed = ['quran_practice', 'quran_journey', 'guided_learning', 'academy'];
    $invalidKeys = DB::table('personal_module_enrollments')->whereNotIn('module_key', $allowed)->count();
    $invalidStatuses = DB::table('personal_module_enrollments')->whereNotIn('status', ['active', 'inactive'])->count();
    $ownershipIssues = DB::table('personal_module_enrollments as pme')
        ->join('personal_profiles as pp', 'pp.id', '=', 'pme.personal_profile_id')
        ->where(function ($query): void {
            $query->whereColumn('pme.user_id', '!=', 'pp.user_id')
                ->orWhereColumn('pme.institution_id', '!=', 'pp.institution_id');
        })->count();

    $this->table(['Komponen', 'Jumlah'], [
        ['Enrollment modul Personal', DB::table('personal_module_enrollments')->count()],
        ['Latihan Qur’an aktif', DB::table('personal_module_enrollments')->where('module_key', 'quran_practice')->where('status', 'active')->count()],
        ['Qur’an Journey aktif', DB::table('personal_module_enrollments')->where('module_key', 'quran_journey')->where('status', 'active')->count()],
        ['Program Asatidz aktif', DB::table('personal_module_enrollments')->where('module_key', 'guided_learning')->where('status', 'active')->count()],
        ['Program dinonaktifkan', DB::table('personal_module_enrollments')->where('status', 'inactive')->count()],
        ['Module key tidak dikenal', $invalidKeys],
        ['Status enrollment tidak dikenal', $invalidStatuses],
        ['Ownership tidak konsisten', $ownershipIssues],
    ]);

    if ($invalidKeys > 0 || $invalidStatuses > 0 || $ownershipIssues > 0) {
        $this->error('Guardrail Personal Program Hub gagal. Periksa module key dan ownership enrollment.');
        return 1;
    }

    $this->info('Personal Program Hub v3.4.0 siap. Pilihan registrasi, aktivasi/nonaktivasi, Home, navigasi, dan akses URL tetap harus dibuktikan lewat smoke test akun nyata.');
    return 0;
})->purpose('Memeriksa lifecycle enrollment dan ownership Personal Program Hub v3.4.0');

Artisan::command('sullam:send-murajaah-reminders {--through=}', function (): int {
    $through = $this->option('through')
        ? \Illuminate\Support\Carbon::parse((string) $this->option('through'))->startOfDay()
        : today()->addDay();
    $result = app(\App\Services\MurajaahReminderService::class)->sendDue($through);
    $this->info("Reminder Murāja‘ah: {$result['plans']} jadwal, {$result['recipients']} penerima.");
    return 0;
})->purpose('Mengirim reminder penjagaan Murajaah yang jatuh tempo tanpa duplikasi');

Artisan::command('sullam:verify-roadmap-foundations-v320', function (): int {
    $required = [
        'talent_progress_records', 'student_portfolio_evidence',
        'ai_assist_drafts', 'ai_assist_reviews',
        'community_moderation_actions', 'payment_transactions',
    ];
    $missing = collect($required)->reject(fn (string $table): bool => Schema::hasTable($table));
    $reminderReady = class_exists(\App\Services\MurajaahReminderService::class)
        && Schema::hasColumn('memorization_review_plans', 'reminder_sent_at');

    $this->table(['Fondasi', 'Status'], [
        ['Character/Talent non-ranking', Schema::hasTable('talent_progress_records') ? 'siap' : 'belum'],
        ['Portfolio evidence', Schema::hasTable('student_portfolio_evidence') ? 'siap' : 'belum'],
        ['Reminder Murāja‘ah', $reminderReady ? 'siap' : 'belum'],
        ['AI draft + human review', Schema::hasTable('ai_assist_drafts') && Schema::hasTable('ai_assist_reviews') ? 'siap' : 'belum'],
        ['Community moderation audit', Schema::hasTable('community_moderation_actions') ? 'siap' : 'belum'],
        ['Payment ledger opsional', Schema::hasTable('payment_transactions') ? 'siap' : 'belum'],
    ]);

    if ($missing->isNotEmpty() || ! $reminderReady) {
        $this->error('Fondasi roadmap v3.2.0 belum lengkap: '.$missing->implode(', '));
        return 1;
    }

    $this->info('Fondasi implementasi v3.2.0 tersedia. Aktivasi eksternal dan launch checks tetap wajib divalidasi di produksi.');
    return 0;
})->purpose('Memeriksa fondasi implementasi Fase 8, 9, dan readiness Fase 10');

// @phase 4.5 Personal 2.0 production verification
Artisan::command('sullam:verify-personal-v450', function (): int {
    $requiredColumns = ['age_group', 'interests', 'aspiration', 'quranic_purpose', 'learning_mode', 'safeguarding_acknowledged_at'];
    $missing = collect($requiredColumns)->reject(fn (string $column): bool => Schema::hasColumn('personal_profiles', $column));

    if ($missing->isNotEmpty()) {
        $this->error('Personal 2.0 belum siap. Kolom hilang: '.$missing->implode(', '));
        return 1;
    }

    $invalidModes = DB::table('personal_profiles')
        ->whereNotNull('learning_mode')
        ->whereNotIn('learning_mode', ['self', 'with_parent', 'private_teacher', 'institution'])
        ->count();
    $minorWithoutSafeguarding = DB::table('personal_profiles')
        ->whereIn('age_group', ['child', 'teen'])
        ->whereNull('safeguarding_acknowledged_at')
        ->count();
    $nonPrivatePortfolio = Schema::hasTable('student_portfolios')
        ? DB::table('student_portfolios')
            ->where('metadata->source', 'personal_v450')
            ->where('visibility', '!=', 'private')
            ->count()
        : 0;

    $this->table(['Komponen', 'Jumlah'], [
        ['Profil Personal', DB::table('personal_profiles')->count()],
        ['Profil dengan cita-cita', DB::table('personal_profiles')->whereNotNull('aspiration')->count()],
        ['Pengguna di bawah 18 tahun', DB::table('personal_profiles')->whereIn('age_group', ['child', 'teen'])->count()],
        ['Pengguna minor tanpa persetujuan pendamping', $minorWithoutSafeguarding],
        ['Jalur pendampingan tidak dikenal', $invalidModes],
        ['Portofolio v4.5.0 tidak privat', $nonPrivatePortfolio],
    ]);

    if ($minorWithoutSafeguarding > 0 || $invalidModes > 0 || $nonPrivatePortfolio > 0) {
        $this->error('Guardrail Personal 2.0 belum lulus. Periksa perlindungan anak, jalur pendampingan, dan privasi portofolio.');
        return 1;
    }

    $this->info('Personal 2.0 v4.5.0 siap untuk smoke test akun nyata. Tidak ada ranking cita-cita atau portofolio publik.');
    return 0;
})->purpose('Memeriksa profil cita-cita, perlindungan anak, jalur pendampingan, dan portofolio privat v4.5.0');

// @phase 4.6 Private Ustadz; @phase 4.7 Institution Suite; @phase 4.8 Family & Parent Portal
Artisan::command('sullam:verify-expansion-v480', function (): int {
    $requiredTables = ['mentorship_sessions', 'family_support_notes', 'user_relationships', 'workspace_invitations', 'workspace_memberships'];
    $missing = collect($requiredTables)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Fase v4.6–v4.8 belum siap. Tabel hilang: '.$missing->implode(', '));
        return 1;
    }

    $acceptedWithoutTimestamp = DB::table('user_relationships')
        ->whereIn('relationship_type', ['mentor_learner', 'guardian_child'])
        ->where('status', 'accepted')
        ->whereNull('accepted_at')
        ->count();
    $invalidMentorshipStatus = DB::table('mentorship_sessions')
        ->whereNotIn('status', ['requested', 'scheduled', 'completed', 'cancelled'])
        ->count();
    $orphanAcceptedInvitations = DB::table('workspace_invitations as wi')
        ->where('wi.status', 'accepted')
        ->whereNotNull('wi.accepted_by_user_id')
        ->whereNotExists(function ($query): void {
            $query->selectRaw('1')
                ->from('workspace_memberships as wm')
                ->whereColumn('wm.institution_id', 'wi.institution_id')
                ->whereColumn('wm.user_id', 'wi.accepted_by_user_id')
                ->where('wm.status', 'active');
        })
        ->count();
    $notesOutsideActiveFamily = DB::table('family_support_notes as fsn')
        ->join('user_relationships as ur', 'ur.id', '=', 'fsn.user_relationship_id')
        ->where('ur.relationship_type', '!=', 'guardian_child')
        ->orWhere('ur.status', '!=', 'accepted')
        ->count();

    $this->table(['Komponen', 'Jumlah'], [
        ['Hubungan Ustadz Privat aktif', DB::table('user_relationships')->where('relationship_type', 'mentor_learner')->where('status', 'accepted')->count()],
        ['Sesi bimbingan', DB::table('mentorship_sessions')->count()],
        ['Hubungan keluarga aktif', DB::table('user_relationships')->where('relationship_type', 'guardian_child')->where('status', 'accepted')->count()],
        ['Catatan dukungan keluarga', DB::table('family_support_notes')->where('status', 'visible')->count()],
        ['Keanggotaan workspace aktif', DB::table('workspace_memberships')->where('status', 'active')->count()],
        ['Undangan ruang pending', DB::table('workspace_invitations')->where('status', 'pending')->where('expires_at', '>', now())->count()],
    ]);

    if ($acceptedWithoutTimestamp || $invalidMentorshipStatus || $orphanAcceptedInvitations || $notesOutsideActiveFamily) {
        $this->error('Guardrail v4.8.0 belum lulus: periksa consent timestamp, status sesi, membership undangan, dan relasi catatan keluarga.');
        return 1;
    }

    $this->info('Fase v4.6–v4.8 siap untuk smoke test akun nyata. Akses tetap consent-based dan terisolasi per workspace.');
    return 0;
})->purpose('Memeriksa Ustadz Privat, Suite Lembaga, dan Portal Keluarga v4.8.0');


// @phase 4.9 Learning & Academy Integration production verification
Artisan::command('sullam:verify-learning-hub-v490', function (): int {
    $requiredTables = [
        'personal_profiles', 'personal_goals', 'personal_module_enrollments',
        'quran_practice_sessions', 'quran_program_enrollments', 'guided_quran_enrollments',
        'academy_lesson_progress', 'workspace_memberships', 'user_relationships', 'mentorship_sessions',
    ];
    $missing = collect($requiredTables)->reject(fn (string $table): bool => Schema::hasTable($table));
    if ($missing->isNotEmpty()) {
        $this->error('Ruang Belajar v4.9.0 belum siap. Tabel fondasi hilang: '.$missing->implode(', '));
        return 1;
    }

    $personalOwnershipIssues = DB::table('personal_goals as pg')
        ->join('personal_profiles as pp', 'pp.id', '=', 'pg.personal_profile_id')
        ->where(function ($query): void {
            $query->whereColumn('pg.user_id', '!=', 'pp.user_id')
                ->orWhereColumn('pg.institution_id', '!=', 'pp.institution_id');
        })->count();

    $guidedOwnershipIssues = DB::table('guided_quran_enrollments as ge')
        ->join('personal_profiles as pp', 'pp.user_id', '=', 'ge.learner_user_id')
        ->whereNotNull('ge.learner_user_id')
        ->whereColumn('ge.learner_institution_id', '!=', 'pp.institution_id')
        ->count();

    $mentorshipScopeIssues = DB::table('mentorship_sessions as ms')
        ->join('user_relationships as ur', 'ur.id', '=', 'ms.user_relationship_id')
        ->where(function ($query): void {
            $query->where('ur.relationship_type', '!=', 'mentor_learner')
                ->orWhereColumn('ms.learner_user_id', '!=', 'ur.from_user_id')
                ->orWhereColumn('ms.mentor_user_id', '!=', 'ur.to_user_id');
        })->count();

    $this->table(['Komponen', 'Jumlah'], [
        ['Profil Personal', DB::table('personal_profiles')->count()],
        ['Program Personal aktif', DB::table('personal_module_enrollments')->where('status', 'active')->count()],
        ['Target Personal aktif', DB::table('personal_goals')->where('status', 'active')->count()],
        ['Program Asatidz aktif', DB::table('guided_quran_enrollments')->where('status', 'active')->count()],
        ['Qur’an Journey aktif', DB::table('quran_program_enrollments')->where('status', 'active')->count()],
        ['Materi Academy selesai', DB::table('academy_lesson_progress')->where('status', 'completed')->count()],
        ['Sesi Ustadz aktif/terjadwal', DB::table('mentorship_sessions')->whereIn('status', ['requested', 'scheduled'])->count()],
        ['Masalah ownership target', $personalOwnershipIssues],
        ['Masalah ownership program Asatidz', $guidedOwnershipIssues],
        ['Masalah scope sesi Ustadz', $mentorshipScopeIssues],
    ]);

    if (! Route::has('personal.learning-hub.index') || ! class_exists(\App\Services\UnifiedLearningHubService::class)) {
        $this->error('Route atau service Ruang Belajar v4.9.0 belum tersedia.');
        return 1;
    }

    if ($personalOwnershipIssues || $guidedOwnershipIssues || $mentorshipScopeIssues) {
        $this->error('Guardrail Ruang Belajar v4.9.0 belum lulus. Periksa ownership Personal, program Asatidz, dan scope Ustadz Privat.');
        return 1;
    }

    $this->info('Ruang Belajar Terpadu v4.9.0 siap untuk smoke test akun nyata. Integrasi merangkum data yang sudah berizin tanpa membuka jurnal atau portofolio privat.');
    return 0;
})->purpose('Memeriksa integrasi Learning & Academy dan guardrail ownership v4.9.0');

Schedule::command('sullam:purge-expired-media')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('sullam:send-murajaah-reminders')->dailyAt('05:30')->withoutOverlapping();

// @phase 5.0 Business, Payment & Integrations production verification
Artisan::command('sullam:verify-business-v500', function (): int {
    $required = ['billing_plans', 'billing_subscriptions', 'billing_invoices', 'payment_transactions', 'integration_connections'];
    $missing = array_values(array_filter($required, fn (string $table): bool => ! Schema::hasTable($table)));
    $planCount = Schema::hasTable('billing_plans') ? DB::table('billing_plans')->where('status', 'active')->count() : 0;
    $invoiceTenantIssues = 0;
    $paymentInvoiceIssues = 0;
    $duplicateInstitutionSubscriptions = 0;

    if (Schema::hasTable('billing_invoices') && Schema::hasTable('billing_subscriptions')) {
        $invoiceTenantIssues = DB::table('billing_invoices as bi')
            ->join('billing_subscriptions as bs', 'bs.id', '=', 'bi.billing_subscription_id')
            ->whereColumn('bi.institution_id', '!=', 'bs.institution_id')
            ->count();
    }
    if (Schema::hasTable('payment_transactions') && Schema::hasColumn('payment_transactions', 'billing_invoice_id') && Schema::hasTable('billing_invoices')) {
        $paymentInvoiceIssues = DB::table('payment_transactions as pt')
            ->join('billing_invoices as bi', 'bi.id', '=', 'pt.billing_invoice_id')
            ->whereColumn('pt.institution_id', '!=', 'bi.institution_id')
            ->count();
    }
    if (Schema::hasTable('billing_subscriptions')) {
        $duplicateInstitutionSubscriptions = DB::table('billing_subscriptions')
            ->select(['institution_id', 'billing_plan_id'])
            ->where('scope_type', 'institution')
            ->whereIn('status', ['pending', 'active'])
            ->groupBy('institution_id', 'billing_plan_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();
    }

    $this->table(['Komponen', 'Status/Jumlah'], [
        ['Tabel bisnis hilang', count($missing)],
        ['Paket aktif', $planCount],
        ['Masalah tenant invoice/subscription', $invoiceTenantIssues],
        ['Masalah tenant payment/invoice', $paymentInvoiceIssues],
        ['Duplikasi paket lembaga pending/aktif', $duplicateInstitutionSubscriptions],
        ['Route portal paket', Route::has('business.index') ? 'siap' : 'belum'],
        ['Route Admin bisnis', Route::has('admin.business.index') ? 'siap' : 'belum'],
    ]);

    if ($missing !== [] || $planCount < 1 || $invoiceTenantIssues || $paymentInvoiceIssues || $duplicateInstitutionSubscriptions || ! Route::has('business.index') || ! Route::has('admin.business.index')) {
        $this->error('Fase 9 v5.0 belum lulus verifier. Periksa schema, paket, route, dan isolasi tenant.');
        return 1;
    }

    if (config('sullam.subscriptions_enabled', false)) {
        $this->info('Business, Payment & Integrations v5.0 siap untuk smoke test transaksi nyata. Pembayaran berbayar tetap memerlukan verifikasi admin sebelum entitlement aktif.');
    } else {
        $this->info('Struktur bisnis v5.0 tetap utuh sebagai histori. Subscription baru ditutup karena fungsi inti berjalan gratis pada v6.0.');
    }
    return 0;
})->purpose('Memeriksa Fase 9 Business, Payment & Integrations v5.0');

// @phase 5.1 SaaS Production Readiness production verification
Artisan::command('sullam:verify-saas-v510', function (): int {
    $service = app(\App\Services\SaasReadinessService::class);
    $checks = $service->checks();
    $summary = $service->summary($checks);

    $this->table(['Check', 'Status', 'Keterangan'], array_map(
        fn (array $check): array => [$check['key'], strtoupper($check['status']), $check['message']],
        $checks,
    ));

    if (! Route::has('admin.operations.index') || ! Schema::hasTable('operational_check_runs')) {
        $this->error('Dashboard atau tabel operasional SaaS v5.1 belum tersedia.');
        return 1;
    }
    if (! $summary['critical_ready']) {
        $this->error('Ada pemeriksaan kritis yang gagal. SaaS Production Readiness belum aman untuk smoke test.');
        return 1;
    }

    if ($summary['fully_verified']) {
        $this->info('SaaS Production Readiness v5.1 lulus termasuk bukti operator backup/restore dan load test.');
    } else {
        $this->warn('Kode Fase 10 v5.1 lulus tanpa kegagalan kritis, tetapi masih ada bukti operator yang belum diverifikasi. Warning tidak dipalsukan menjadi PASS.');
    }
    return 0;
})->purpose('Memeriksa guardrail dan bukti operasional SaaS v5.1');

// @phase 5.2 Smart Assistant production verification
Artisan::command('sullam:verify-ai-assist-v520', function (): int {
    $approvedWithoutReview = 0;
    $reviewScopeIssues = 0;
    if (Schema::hasTable('ai_assist_drafts') && Schema::hasTable('ai_assist_reviews')) {
        $approvedWithoutReview = DB::table('ai_assist_drafts as d')
            ->leftJoin('ai_assist_reviews as r', 'r.ai_assist_draft_id', '=', 'd.id')
            ->where('d.status', 'approved')->whereNull('r.id')->count();

        $reviews = \App\Models\AiAssistReview::query()->with(['draft', 'reviewer'])->get();
        foreach ($reviews as $review) {
            $draft = $review->draft;
            if (! $draft || ! $review->reviewer) {
                $reviewScopeIssues++;
                continue;
            }
            if ((int) $draft->institution_id === (int) $review->reviewer->institution_id) {
                continue;
            }
            $learnerId = (int) data_get($draft->evidence_snapshot, 'learner_user_id', 0);
            $validCrossWorkspaceMentor = $draft->purpose === 'personal_learning_guidance'
                && $learnerId > 0
                && (int) $draft->created_by_user_id === $learnerId
                && \App\Models\UserRelationship::query()
                    ->where('relationship_type', 'mentor_learner')
                    ->where('status', 'accepted')
                    ->where('from_user_id', $learnerId)
                    ->where('to_user_id', $review->reviewer_user_id)
                    ->exists();
            if (! $validCrossWorkspaceMentor) {
                $reviewScopeIssues++;
            }
        }
    }

    $this->table(['Komponen', 'Jumlah/Status'], [
        ['Draft AI Assist', Schema::hasTable('ai_assist_drafts') ? DB::table('ai_assist_drafts')->count() : 'tabel hilang'],
        ['Review manusia', Schema::hasTable('ai_assist_reviews') ? DB::table('ai_assist_reviews')->count() : 'tabel hilang'],
        ['Approved tanpa review', $approvedWithoutReview],
        ['Masalah scope reviewer', $reviewScopeIssues],
        ['Pendamping Personal', Route::has('personal.smart-assistant.index') ? 'siap' : 'belum'],
        ['Review Ustadz', Route::has('teacher.smart-assistant.index') ? 'siap' : 'belum'],
    ]);

    if (! Schema::hasTable('ai_assist_drafts') || ! Schema::hasTable('ai_assist_reviews') || $approvedWithoutReview || $reviewScopeIssues || ! Route::has('personal.smart-assistant.index') || ! Route::has('teacher.smart-assistant.index')) {
        $this->error('Fase 11 v5.2 belum lulus human-review guardrail.');
        return 1;
    }

    $this->info('Pendamping Cerdas v5.2 siap untuk smoke test. Review lintas workspace hanya sah melalui hubungan Ustadz Privat yang telah disetujui.');
    return 0;
})->purpose('Memeriksa Pendamping Cerdas dan human-review guardrail v5.2');

// @phase 5.3 Mobile, Offline & Global production verification
Artisan::command('sullam:verify-mobile-v530', function (): int {
    $manifestPath = public_path('manifest.webmanifest');
    $swPath = public_path('service-worker.js');
    $manifest = is_file($manifestPath) ? json_decode((string) file_get_contents($manifestPath), true) : null;
    $sw = is_file($swPath) ? (string) file_get_contents($swPath) : '';
    $offlineGuard = str_contains($sw, "url.pathname.startsWith('/media/')")
        && str_contains($sw, "url.pathname.startsWith('/api/')")
        && str_contains($sw, "cache: 'no-store'");

    $this->table(['Komponen', 'Status'], [
        ['Tabel user_preferences', Schema::hasTable('user_preferences') ? 'siap' : 'belum'],
        ['Manifest valid', is_array($manifest) ? 'siap' : 'belum'],
        ['PWA display standalone', data_get($manifest, 'display') === 'standalone' ? 'siap' : 'belum'],
        ['Offline guard media/API/private navigation', $offlineGuard ? 'siap' : 'belum'],
        ['Route preferensi', Route::has('preferences.edit') ? 'siap' : 'belum'],
        ['Health API', Route::has('api.health') ? 'siap' : 'belum'],
    ]);

    if (! Schema::hasTable('user_preferences') || ! is_array($manifest) || data_get($manifest, 'display') !== 'standalone' || ! $offlineGuard || ! Route::has('preferences.edit') || ! Route::has('api.health')) {
        $this->error('Fase 12 v5.3 belum lulus PWA/offline-safe/global preference verifier.');
        return 1;
    }

    $this->info('Mobile, Offline & Global v5.3 siap smoke test perangkat. Data privat tidak dimasukkan ke cache offline.');
    return 0;
})->purpose('Memeriksa PWA, offline-safe cache, dan preferensi global v5.3');

// @phase 5.3 Consolidated v5.3 release verification — one command for phases 4.5 through 5.3
Artisan::command('sullam:verify-release-v530', function (): int {
    $commands = [
        'sullam:verify-personal-v450',
        'sullam:verify-expansion-v480',
        'sullam:verify-learning-hub-v490',
        'sullam:verify-business-v500',
        'sullam:verify-saas-v510',
        'sullam:verify-ai-assist-v520',
        'sullam:verify-mobile-v530',
    ];

    foreach ($commands as $command) {
        $this->newLine();
        $this->line('>>> '.$command);
        $exitCode = $this->call($command);
        if ($exitCode !== 0) {
            $this->error('Release v5.3.0 berhenti pada verifier: '.$command);
            return $exitCode;
        }
    }

    $this->newLine();
    $this->info('Release v5.3.0 lulus verifier aplikasi. Lanjutkan smoke test akun nyata dan bukti operator Fase 10.');
    return 0;
})->purpose('Menjalankan seluruh verifier release v5.3.0 dalam satu command');

// @phase 6.0 Free, Infaq & Distraction-Free Tahfizh production verification
Artisan::command('sullam:verify-v600', function (): int {
    $requiredTables = [
        'student_memorization_focuses',
        'student_memorization_assessments',
        'infaq_transactions',
    ];
    $missingTables = array_values(array_filter(
        $requiredTables,
        fn (string $table): bool => ! Schema::hasTable($table),
    ));
    $missingColumns = [];
    foreach (['memorization_records', 'murajaah_records'] as $table) {
        foreach (['daily_decision', 'short_note', 'submission_key'] as $column) {
            if (! Schema::hasColumn($table, $column)) {
                $missingColumns[] = $table.'.'.$column;
            }
        }
    }
    $duplicateActiveFocus = Schema::hasTable('student_memorization_focuses')
        ? DB::table('student_memorization_focuses')
            ->select(['institution_id', 'student_id'])
            ->where('status', 'active')
            ->groupBy('institution_id', 'student_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()->count()
        : 0;
    $requiredRoutes = [
        'teacher.tahfizh.quick-memorization.store',
        'teacher.tahfizh.quick-murajaah.store',
        'teacher.tahfizh.focus.update',
        'teacher.tahfizh.assessments.store',
        'infaq.index',
        'admin.infaq.index',
    ];
    $missingRoutes = array_values(array_filter(
        $requiredRoutes,
        fn (string $route): bool => ! Route::has($route),
    ));
    $legacyRoutesReady = Route::has('teacher.tahfizh.memorization.store')
        && Route::has('teacher.tahfizh.murajaah.store');
    $freeMode = ! config('sullam.subscriptions_enabled', false);

    $this->table(['Komponen', 'Status/Jumlah'], [
        ['Tabel v6 hilang', count($missingTables)],
        ['Kolom v6 hilang', count($missingColumns)],
        ['Rute v6 hilang', count($missingRoutes)],
        ['Form rinci lama', $legacyRoutesReady ? 'dipertahankan' : 'hilang'],
        ['Fokus aktif ganda', $duplicateActiveFocus],
        ['Mode fungsi inti gratis', $freeMode ? 'aktif' : 'belum aktif'],
    ]);

    if ($missingTables !== [] || $missingColumns !== [] || $missingRoutes !== [] || ! $legacyRoutesReady || $duplicateActiveFocus || ! $freeMode) {
        $this->error('v6.0 belum lulus verifier. Periksa migration, route, fokus aktif, dan SUBSCRIPTIONS_ENABLED=false.');
        return 1;
    }

    $this->info('v6.0 siap smoke test: fungsi inti gratis, infak sukarela, alur setoran cepat, dan form rinci lama tetap tersedia.');
    return 0;
})->purpose('Memeriksa Free, Infaq & Distraction-Free Tahfizh v6.0');

Artisan::command('sullam:verify-release-v600', function (): int {
    foreach (['sullam:verify-release-v530', 'sullam:verify-v600'] as $command) {
        $this->newLine();
        $this->line('>>> '.$command);
        $exitCode = $this->call($command);
        if ($exitCode !== 0) {
            $this->error('Release v6.0.0 berhenti pada verifier: '.$command);
            return $exitCode;
        }
    }

    $this->newLine();
    $this->info('Release v6.0.0 lulus verifier aplikasi. Lanjutkan smoke test setoran, ringkasan keluarga, dan infak dengan akun nyata.');
    return 0;
})->purpose('Menjalankan seluruh verifier release v6.0.0');
