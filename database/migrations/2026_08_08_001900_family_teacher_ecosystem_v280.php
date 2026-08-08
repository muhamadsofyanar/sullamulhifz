<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('family_learning_activities')) {
            Schema::create('family_learning_activities', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academy_lesson_id')->nullable()->constrained('academy_lessons')->nullOnDelete();
                $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title', 180);
                $table->string('activity_type', 30)->default('practice');
                $table->text('instructions');
                $table->timestamp('due_at')->nullable();
                $table->string('status', 24)->default('assigned');
                $table->longText('guardian_reflection')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('teacher_follow_up')->nullable();
                $table->timestamps();
                $table->index(['institution_id','student_id','status'], 'family_activity_student_status_idx');
                $table->index(['institution_id','created_by_user_id','status'], 'family_activity_teacher_status_idx');
            });
        }

        if (! Schema::hasTable('teacher_competencies')) {
            Schema::create('teacher_competencies', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academy_lesson_id')->nullable()->constrained('academy_lessons')->nullOnDelete();
                $table->string('code', 60);
                $table->string('title', 180);
                $table->string('category', 60)->default('professional');
                $table->text('description')->nullable();
                $table->text('evidence_guidance')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->string('status', 20)->default('active');
                $table->timestamps();
                $table->unique(['institution_id','code'], 'teacher_competency_code_uq');
                $table->index(['institution_id','status','sort_order'], 'teacher_competency_status_idx');
            });
        }

        if (! Schema::hasTable('teacher_competency_progress')) {
            Schema::create('teacher_competency_progress', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_competency_id')->constrained('teacher_competencies')->cascadeOnDelete();
                $table->string('status', 24)->default('not_started');
                $table->longText('reflection')->nullable();
                $table->longText('evidence_note')->nullable();
                $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('review_note')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->unique(['teacher_id','teacher_competency_id'], 'teacher_competency_progress_uq');
                $table->index(['institution_id','status'], 'teacher_competency_progress_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_competency_progress');
        Schema::dropIfExists('teacher_competencies');
        Schema::dropIfExists('family_learning_activities');
    }
};
