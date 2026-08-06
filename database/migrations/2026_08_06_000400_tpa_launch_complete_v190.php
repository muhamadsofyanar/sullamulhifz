<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table): void {
            if (! Schema::hasColumn('meetings', 'meeting_type')) {
                $table->string('meeting_type', 40)->default('general')->after('topic');
            }
            if (! Schema::hasColumn('meetings', 'attendance_completed_at')) {
                $table->timestamp('attendance_completed_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('meetings', 'learning_completed_at')) {
                $table->timestamp('learning_completed_at')->nullable()->after('attendance_completed_at');
            }
            if (! Schema::hasColumn('meetings', 'summary_published_at')) {
                $table->timestamp('summary_published_at')->nullable()->after('learning_completed_at');
            }
            if (! Schema::hasColumn('meetings', 'guardian_summary')) {
                $table->text('guardian_summary')->nullable()->after('general_notes');
            }
            if (! Schema::hasColumn('meetings', 'closed_by_user_id')) {
                $table->foreignId('closed_by_user_id')->nullable()->after('teacher_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('tahsin_records', function (Blueprint $table): void {
            foreach ([
                'fluency_status' => 'string',
                'makhraj_status' => 'string',
                'tajwid_status' => 'string',
                'adab_status' => 'string',
                'decision' => 'string',
            ] as $column => $type) {
                if (! Schema::hasColumn('tahsin_records', $column)) {
                    $table->{$type}($column, 40)->nullable()->after('overall_status');
                }
            }
        });

        Schema::table('memorization_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('memorization_records', 'memorization_target_id')) {
                $table->foreignId('memorization_target_id')->nullable()->after('meeting_id')->constrained('memorization_targets')->nullOnDelete();
            }
            if (! Schema::hasColumn('memorization_records', 'fluency_status')) {
                $table->string('fluency_status', 40)->nullable()->after('result');
            }
            if (! Schema::hasColumn('memorization_records', 'tajwid_status')) {
                $table->string('tajwid_status', 40)->nullable()->after('fluency_status');
            }
            if (! Schema::hasColumn('memorization_records', 'error_count')) {
                $table->unsignedSmallInteger('error_count')->nullable()->after('tajwid_status');
            }
            if (! Schema::hasColumn('memorization_records', 'review_recommendation')) {
                $table->string('review_recommendation', 80)->nullable()->after('follow_up');
            }
        });

        Schema::table('murajaah_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('murajaah_records', 'strength_status')) {
                $table->string('strength_status', 40)->nullable()->after('result');
            }
            if (! Schema::hasColumn('murajaah_records', 'review_recommendation')) {
                $table->string('review_recommendation', 80)->nullable()->after('next_review_date');
            }
        });

        Schema::table('assignments', function (Blueprint $table): void {
            if (! Schema::hasColumn('assignments', 'assignment_type')) {
                $table->string('assignment_type', 40)->default('general')->after('title');
            }
            if (! Schema::hasColumn('assignments', 'quran_audio_source_id')) {
                $table->foreignId('quran_audio_source_id')->nullable()->after('surah_id')->constrained('quran_audio_sources')->nullOnDelete();
            }
            if (! Schema::hasColumn('assignments', 'repeat_count')) {
                $table->unsignedSmallInteger('repeat_count')->nullable()->after('end_verse');
            }
            if (! Schema::hasColumn('assignments', 'repeat_mode')) {
                $table->string('repeat_mode', 30)->nullable()->after('repeat_count');
            }
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('assignment_submissions', 'guardian_checklist_completed')) {
                $table->boolean('guardian_checklist_completed')->default(false)->after('guardian_notes');
            }
        });

        if (! Schema::hasTable('launch_checks')) {
            Schema::create('launch_checks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->string('check_key');
                $table->string('category');
                $table->string('label');
                $table->string('status')->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('checked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('checked_at')->nullable();
                $table->timestamps();
                $table->unique(['institution_id', 'check_key']);
            });
        }

        $checks = [
            ['security_https','Keamanan','HTTPS dan domain portal telah aktif'],
            ['security_roles','Keamanan','Akses admin, guru, dan wali telah diuji'],
            ['security_password','Keamanan','Kebijakan kata sandi dan pemulihan akun telah diuji'],
            ['data_backup','Data','Backup database sebelum peluncuran telah dibuat'],
            ['data_restore','Data','Prosedur pemulihan backup telah dibaca dan diuji'],
            ['learning_teacher','Pembelajaran','Alur guru: pertemuan, absensi, Tahsin, Tahfizh, dan Murajaah telah diuji'],
            ['learning_guardian','Pembelajaran','Alur wali: perkembangan, tugas, audio, dan buku penghubung telah diuji'],
            ['report_monthly','Laporan','Ringkasan bulanan dan ekspor CSV telah diuji'],
            ['report_card','Laporan','Rapor semester dan cetak telah diuji'],
            ['mobile_pwa','Perangkat','Tampilan ponsel dan instalasi PWA telah diuji'],
            ['operations_docs','Operasional','Panduan admin, guru, wali, upgrade, dan rollback tersedia'],
            ['privacy_children','Privasi','Data anak, media, dan catatan individual terlindungi'],
        ];

        foreach (DB::table('institutions')->whereNull('deleted_at')->get() as $institution) {
            foreach ($checks as [$key, $category, $label]) {
                DB::table('launch_checks')->updateOrInsert(
                    ['institution_id' => $institution->id, 'check_key' => $key],
                    [
                        'category' => $category,
                        'label' => $label,
                        'status' => 'pending',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_checks');

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            if (Schema::hasColumn('assignment_submissions', 'guardian_checklist_completed')) {
                $table->dropColumn('guardian_checklist_completed');
            }
        });

        Schema::table('assignments', function (Blueprint $table): void {
            if (Schema::hasColumn('assignments', 'quran_audio_source_id')) {
                $table->dropConstrainedForeignId('quran_audio_source_id');
            }
            foreach (['assignment_type','repeat_count','repeat_mode'] as $column) {
                if (Schema::hasColumn('assignments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('murajaah_records', function (Blueprint $table): void {
            foreach (['strength_status','review_recommendation'] as $column) {
                if (Schema::hasColumn('murajaah_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('memorization_records', function (Blueprint $table): void {
            if (Schema::hasColumn('memorization_records', 'memorization_target_id')) {
                $table->dropConstrainedForeignId('memorization_target_id');
            }
            foreach (['fluency_status','tajwid_status','error_count','review_recommendation'] as $column) {
                if (Schema::hasColumn('memorization_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('tahsin_records', function (Blueprint $table): void {
            foreach (['fluency_status','makhraj_status','tajwid_status','adab_status','decision'] as $column) {
                if (Schema::hasColumn('tahsin_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('meetings', function (Blueprint $table): void {
            if (Schema::hasColumn('meetings', 'closed_by_user_id')) {
                $table->dropConstrainedForeignId('closed_by_user_id');
            }
            foreach (['meeting_type','attendance_completed_at','learning_completed_at','summary_published_at','guardian_summary'] as $column) {
                if (Schema::hasColumn('meetings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
