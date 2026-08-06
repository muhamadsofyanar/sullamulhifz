<?php

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
use App\Services\QuranAudioSyncService;

Artisan::command('sullam:about', function (): void {
    $this->info('Sullamul Hifz v1.6.0 — Bukan Sekadar Hafal, Tapi KUAT.');
})->purpose('Menampilkan identitas aplikasi');

Artisan::command('sullam:reset-admin {--email=} {--password=}', function (): int {
    $email = $this->option('email') ?: env('INITIAL_ADMIN_EMAIL');
    $password = $this->option('password') ?: env('INITIAL_ADMIN_PASSWORD');

    if (! $email || ! $password) {
        $this->error('Email atau password admin belum tersedia. Isi INITIAL_ADMIN_EMAIL dan INITIAL_ADMIN_PASSWORD.');
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
    $hasFailure = false;

    foreach ($institutions as $institution) {
        $source = QuranAudioSource::query()
            ->where('institution_id', $institution->id)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->first();

        $count = $source
            ? QuranAyahTiming::query()->where('quran_audio_source_id', $source->id)->whereBetween('surah_id', [78, 114])->count()
            : 0;

        if ($count >= QuranAudioSyncService::JUZ_30_AYAH_COUNT) {
            $service->seedPresets($institution, $source);
            $this->info("{$institution->name}: audio Juz 30 sudah lengkap ({$count}/564). Preset dipastikan tersedia.");
            continue;
        }

        $this->info("{$institution->name}: sinkronisasi audio Juz 30 dimulai ({$count}/564)...");
        try {
            $result = $service->syncInstitution($institution);
            $this->info("{$institution->name}: {$result['timings']}/564 timing, {$result['pages']} halaman, {$result['presets']} preset.");
            if ($result['failed_surahs']) {
                $hasFailure = true;
                $this->warn('Surah yang perlu dicoba ulang: '.implode(', ', $result['failed_surahs']));
            }
        } catch (Throwable $exception) {
            report($exception);
            $hasFailure = true;
            $this->warn("{$institution->name}: sinkronisasi belum lengkap — {$exception->getMessage()}");
        }
    }

    return $hasFailure ? 1 : 0;
})->purpose('Mengisi timing 564 ayat Juz 30 dan preset latihan secara idempoten');

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
    foreach (Institution::query()->where('status', 'active')->get() as $institution) {
        $source = QuranAudioSource::query()
            ->where('institution_id', $institution->id)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->first();
        $timings = $source
            ? QuranAyahTiming::query()->where('quran_audio_source_id', $source->id)->whereBetween('surah_id', [78, 114])->count()
            : 0;
        $rows[] = [$institution->name, $source?->reciter_name ?? 'Belum ada', $timings, $timings >= 564 ? 'Lengkap' : 'Perlu sinkronisasi'];
    }

    $this->table(['Lembaga', 'Sumber', 'Timing', 'Status'], $rows);
    $this->info('Struktur Quran Learning v1.6.0 siap. Kekurangan timing tidak menghentikan aplikasi; sinkronisasi dapat diulang.');
    return 0;
})->purpose('Memeriksa tabel dan kelengkapan pustaka Quran Learning v1.6.0');
