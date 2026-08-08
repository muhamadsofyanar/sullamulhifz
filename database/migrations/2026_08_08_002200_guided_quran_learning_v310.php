<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guided_quran_programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('academy_program_id')->nullable()->constrained('academy_programs')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 190);
            $table->string('slug', 190);
            $table->string('program_type', 40)->default('tahfizh');
            $table->string('delivery_mode', 30)->default('online');
            $table->unsignedTinyInteger('target_juz')->nullable();
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->text('submission_guidance')->nullable();
            $table->boolean('accepts_audio')->default(true);
            $table->boolean('accepts_text')->default(true);
            $table->boolean('is_public')->default(false);
            $table->string('status', 30)->default('draft');
            $table->timestamp('enrollment_opens_at')->nullable();
            $table->timestamp('enrollment_closes_at')->nullable();
            $table->timestamps();
            $table->unique(['provider_institution_id', 'slug'], 'guided_program_provider_slug_uq');
            $table->index(['is_public', 'status', 'delivery_mode'], 'guided_program_public_idx');
        });

        Schema::create('guided_quran_program_reviewers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('guided_quran_program_id')->constrained('guided_quran_programs')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->unique(['guided_quran_program_id', 'reviewer_user_id'], 'guided_program_reviewer_uq');
        });

        Schema::create('guided_quran_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('guided_quran_program_id')->constrained('guided_quran_programs')->cascadeOnDelete();
            $table->foreignId('learner_institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('learner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('learner_mode', 30)->default('personal_online');
            $table->string('status', 30)->default('active');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['guided_quran_program_id', 'student_id'], 'guided_program_student_uq');
            $table->index(['learner_institution_id', 'learner_user_id', 'status'], 'guided_enrollment_learner_idx');
        });

        Schema::create('quran_guided_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('guided_quran_enrollment_id')->constrained('guided_quran_enrollments')->cascadeOnDelete();
            $table->foreignId('learner_institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('learner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedSmallInteger('surah_id')->nullable();
            $table->foreignId('audio_media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('submission_type', 40)->default('memorization');
            $table->unsignedSmallInteger('start_verse')->nullable();
            $table->unsignedSmallInteger('end_verse')->nullable();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->text('evidence_text')->nullable();
            $table->text('learner_notes')->nullable();
            $table->string('review_status', 30)->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['guided_quran_enrollment_id', 'review_status'], 'guided_submission_queue_idx');
            $table->index(['learner_institution_id', 'learner_user_id'], 'guided_submission_learner_idx');
            $table->foreign('surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
        });

        Schema::create('quran_guided_submission_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quran_guided_submission_id');
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('feedback_audio_media_asset_id')->nullable();
            $table->string('decision', 30);
            $table->text('feedback_text')->nullable();
            $table->timestamps();
            $table->index(['quran_guided_submission_id', 'created_at'], 'guided_review_history_idx');
            $table->foreign('quran_guided_submission_id', 'guided_review_submission_fk')
                ->references('id')->on('quran_guided_submissions')->cascadeOnDelete();
            $table->foreign('feedback_audio_media_asset_id', 'guided_review_feedback_audio_fk')
                ->references('id')->on('media_assets')->nullOnDelete();
        });

        $permissions = [
            'guided_learning.use' => ['Mengikuti program Al-Qur’an', 'Mendaftar program dan mengirim setoran yang memang dipilih untuk diperiksa.'],
            'guided_learning.review' => ['Meninjau setoran Al-Qur’an', 'Meninjau setoran program yang ditugaskan kepada reviewer.'],
            'guided_learning.manage' => ['Mengelola program Al-Qur’an', 'Mengelola program online/hybrid dan reviewer lembaga penyelenggara.'],
            'academy.view' => ['Mengakses Academy', 'Mengakses materi Academy sesuai lingkup program.'],
            'quran.view' => ['Mengakses latihan Al-Qur’an', 'Mengakses sarana latihan dan audio Al-Qur’an.'],
        ];
        foreach ($permissions as $name => [$displayName, $description]) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['display_name' => $displayName, 'description' => $description],
            );
        }

        $rolePermissions = [
            'personal' => ['guided_learning.use', 'academy.view', 'quran.view'],
            'teacher' => ['guided_learning.review'],
            'head' => ['guided_learning.review', 'guided_learning.manage'],
            'institution_admin' => ['guided_learning.review', 'guided_learning.manage'],
            'superadmin' => ['guided_learning.review', 'guided_learning.manage'],
        ];
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if (! $roleId) continue;
            foreach (DB::table('permissions')->whereIn('name', $permissionNames)->pluck('id') as $permissionId) {
                DB::table('role_permissions')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }
        }

        $personalInstitutionIds = DB::table('institutions')->where('workspace_type', 'personal')->pluck('id');
        foreach ($personalInstitutionIds as $institutionId) {
            foreach (['academy_portal', 'quran_audio'] as $featureKey) {
                DB::table('feature_flags')->updateOrInsert(
                    ['institution_id' => $institutionId, 'feature_key' => $featureKey],
                    ['enabled' => true, 'updated_at' => now(), 'created_at' => now()],
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_guided_submission_reviews');
        Schema::dropIfExists('quran_guided_submissions');
        Schema::dropIfExists('guided_quran_enrollments');
        Schema::dropIfExists('guided_quran_program_reviewers');
        Schema::dropIfExists('guided_quran_programs');
    }
};
