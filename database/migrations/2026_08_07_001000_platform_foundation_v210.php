<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code');
                $table->text('address')->nullable();
                $table->string('phone', 40)->nullable();
                $table->string('status', 30)->default('active');
                $table->boolean('is_main')->default(false);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['institution_id', 'code']);
                $table->index(['institution_id', 'status']);
            });
        }

        if (! Schema::hasTable('academic_periods')) {
            Schema::create('academic_periods', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status', 30)->default('draft');
                $table->timestamps();
                $table->unique(['academic_year_id', 'name']);
                $table->index(['academic_year_id', 'status']);
            });
        }

        if (! Schema::hasTable('media_assets')) {
            Schema::create('media_assets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('disk', 40)->default('local');
                $table->string('directory', 255);
                $table->string('file_name', 255);
                $table->string('original_name', 255);
                $table->string('mime_type', 150)->nullable();
                $table->string('extension', 20)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('checksum', 64)->nullable();
                $table->string('visibility', 30)->default('private');
                $table->string('processing_status', 30)->default('ready');
                $table->timestamp('retention_until')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['disk', 'directory', 'file_name'], 'media_assets_storage_uq');
                $table->index(['institution_id', 'visibility']);
                $table->index(['retention_until', 'processing_status']);
            });
        }

        if (! Schema::hasTable('media_links')) {
            Schema::create('media_links', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
                $table->string('attachable_type');
                $table->unsignedBigInteger('attachable_id');
                $table->string('purpose', 50)->default('attachment');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['attachable_type', 'attachable_id'], 'media_links_attachable_idx');
                $table->unique(['media_asset_id', 'attachable_type', 'attachable_id', 'purpose'], 'media_links_asset_attachable_uq');
            });
        }

        if (! Schema::hasTable('announcement_targets')) {
            Schema::create('announcement_targets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
                $table->string('target_type', 30);
                $table->unsignedBigInteger('target_id')->nullable();
                $table->timestamps();
                $table->index(['target_type', 'target_id']);
                $table->unique(['announcement_id', 'target_type', 'target_id'], 'announcement_targets_uq');
            });
        }

        if (! Schema::hasTable('friday_session_targets')) {
            Schema::create('friday_session_targets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('friday_development_session_id')->constrained('friday_development_sessions')->cascadeOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('classes')->cascadeOnDelete();
                $table->foreignId('learning_group_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('level_id')->nullable()->constrained()->cascadeOnDelete();
                $table->boolean('target_all')->default(false);
                $table->timestamps();
                $table->index(['friday_development_session_id', 'target_all'], 'friday_targets_session_idx');
            });
        }

        if (! Schema::hasTable('student_marhalah_histories')) {
            Schema::create('student_marhalah_histories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('marhalah_type_id')->constrained()->restrictOnDelete();
                $table->date('effective_from');
                $table->date('effective_until')->nullable();
                $table->string('decision', 30)->default('start');
                $table->text('reason')->nullable();
                $table->foreignId('decided_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->text('evidence_notes')->nullable();
                $table->string('status', 30)->default('active');
                $table->timestamps();
                $table->index(['student_id', 'status']);
                $table->index(['effective_from', 'effective_until']);
            });
        }

        if (! Schema::hasTable('account_invitations')) {
            Schema::create('account_invitations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('token_hash', 64)->unique();
                $table->string('delivery_channel', 20)->default('email');
                $table->string('delivery_address', 190)->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('accepted_at')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['user_id', 'expires_at']);
            });
        }

        if (! Schema::hasTable('feature_flags')) {
            Schema::create('feature_flags', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('feature_key', 100);
                $table->boolean('enabled')->default(false);
                $table->json('config')->nullable();
                $table->timestamps();
                $table->unique(['institution_id', 'feature_key'], 'feature_flags_scope_uq');
                $table->index(['feature_key', 'enabled']);
            });
        }

        $this->addBranchColumns();
        $this->addMediaColumns();
        $this->addHistoryColumns();
        $this->seedFoundationData();
    }

    private function addBranchColumns(): void
    {
        foreach (['students', 'classes', 'learning_groups', 'schedules', 'user_roles'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                    $table->foreignId('branch_id')->nullable()->after('institution_id')->constrained('branches')->nullOnDelete();
                    $table->index(['branch_id', 'status'], $tableName.'_branch_status_idx');
                });
            }
        }
    }

    private function addMediaColumns(): void
    {
        $columns = [
            'students' => ['photo_media_id', 'media_assets'],
            'teachers' => ['photo_media_id', 'media_assets'],
            'student_consents' => ['evidence_media_id', 'media_assets'],
            'announcements' => ['attachment_media_id', 'media_assets'],
            'friday_development_sessions' => ['worksheet_media_id', 'media_assets'],
            'liaison_messages' => ['media_asset_id', 'media_assets'],
            'assignment_submissions' => ['media_asset_id', 'media_assets'],
        ];

        foreach ($columns as $tableName => [$column, $target]) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, $column)) {
                Schema::table($tableName, function (Blueprint $table) use ($column, $target): void {
                    $table->foreignId($column)->nullable()->constrained($target)->nullOnDelete();
                });
            }
        }
    }

    private function addHistoryColumns(): void
    {
        if (Schema::hasTable('class_enrollments') && ! Schema::hasColumn('class_enrollments', 'previous_enrollment_id')) {
            Schema::table('class_enrollments', function (Blueprint $table): void {
                $table->foreignId('previous_enrollment_id')->nullable()->constrained('class_enrollments')->nullOnDelete();
            });
        }
    }

    private function seedFoundationData(): void
    {
        $now = now();

        foreach (DB::table('institutions')->whereNull('deleted_at')->get() as $institution) {
            $branchId = DB::table('branches')->where('institution_id', $institution->id)->where('is_main', true)->value('id');

            if (! $branchId) {
                $branchId = DB::table('branches')->insertGetId([
                    'institution_id' => $institution->id,
                    'name' => 'Cabang Utama',
                    'code' => 'UTAMA',
                    'status' => 'active',
                    'is_main' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach (['students', 'classes', 'learning_groups', 'schedules', 'user_roles'] as $tableName) {
                if (Schema::hasColumn($tableName, 'branch_id')) {
                    DB::table($tableName)
                        ->where('institution_id', $institution->id)
                        ->whereNull('branch_id')
                        ->update(['branch_id' => $branchId]);
                }
            }

            $features = [
                'core_academic' => true,
                'quran_audio' => true,
                'parent_academy' => true,
                'public_website' => true,
                'report_cards' => true,
                'admissions' => true,
                'community' => false,
                'multi_branch' => false,
            ];

            foreach ($features as $key => $enabled) {
                DB::table('feature_flags')->updateOrInsert(
                    ['institution_id' => $institution->id, 'feature_key' => $key],
                    ['enabled' => $enabled, 'config' => null, 'created_at' => $now, 'updated_at' => $now],
                );
            }
        }

        foreach (DB::table('academic_years')->get() as $year) {
            DB::table('academic_periods')->updateOrInsert(
                ['academic_year_id' => $year->id, 'name' => 'Periode Utama'],
                [
                    'start_date' => $year->start_date,
                    'end_date' => $year->end_date,
                    'status' => $year->is_active ? 'active' : 'closed',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        if (Schema::hasTable('announcements')) {
            foreach (DB::table('announcements')->get() as $announcement) {
                $targetType = $announcement->audience_type ?? 'institution';
                $targetId = match ($targetType) {
                    'class' => $announcement->class_id ?? null,
                    'group' => $announcement->learning_group_id ?? null,
                    default => null,
                };

                DB::table('announcement_targets')->updateOrInsert(
                    ['announcement_id' => $announcement->id, 'target_type' => $targetType, 'target_id' => $targetId],
                    ['created_at' => $now, 'updated_at' => $now],
                );
            }
        }

        if (Schema::hasTable('friday_development_sessions')) {
            foreach (DB::table('friday_development_sessions')->get() as $session) {
                DB::table('friday_session_targets')->updateOrInsert(
                    [
                        'friday_development_session_id' => $session->id,
                        'class_id' => $session->class_id,
                        'learning_group_id' => null,
                        'level_id' => null,
                    ],
                    ['target_all' => $session->class_id === null, 'created_at' => $now, 'updated_at' => $now],
                );
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'assignment_submissions' => 'media_asset_id',
            'liaison_messages' => 'media_asset_id',
            'friday_development_sessions' => 'worksheet_media_id',
            'announcements' => 'attachment_media_id',
            'student_consents' => 'evidence_media_id',
            'teachers' => 'photo_media_id',
            'students' => 'photo_media_id',
        ] as $tableName => $column) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, $column)) {
                Schema::table($tableName, function (Blueprint $table) use ($column): void {
                    $table->dropConstrainedForeignId($column);
                });
            }
        }

        if (Schema::hasTable('class_enrollments') && Schema::hasColumn('class_enrollments', 'previous_enrollment_id')) {
            Schema::table('class_enrollments', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('previous_enrollment_id');
            });
        }

        foreach (['user_roles', 'schedules', 'learning_groups', 'classes', 'students'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropConstrainedForeignId('branch_id');
                });
            }
        }

        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('account_invitations');
        Schema::dropIfExists('student_marhalah_histories');
        Schema::dropIfExists('friday_session_targets');
        Schema::dropIfExists('announcement_targets');
        Schema::dropIfExists('media_links');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('branches');
    }
};
