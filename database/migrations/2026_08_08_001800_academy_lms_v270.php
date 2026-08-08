<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('academy_prerequisites')) {
            Schema::create('academy_prerequisites', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->string('subject_type', 20);
                $table->unsignedBigInteger('subject_id');
                $table->string('required_type', 20);
                $table->unsignedBigInteger('required_id');
                $table->timestamps();
                $table->unique(['institution_id','subject_type','subject_id','required_type','required_id'], 'academy_prerequisite_uq');
                $table->index(['institution_id','subject_type','subject_id'], 'academy_prerequisite_subject_idx');
            });
        }

        if (! Schema::hasTable('academy_quizzes')) {
            Schema::create('academy_quizzes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('academy_lesson_id')->constrained('academy_lessons')->cascadeOnDelete();
                $table->string('title');
                $table->text('instructions')->nullable();
                $table->unsignedTinyInteger('passing_percent')->default(70);
                $table->unsignedTinyInteger('max_attempts')->default(3);
                $table->string('status', 20)->default('published');
                $table->timestamps();
                $table->unique('academy_lesson_id');
            });
        }

        if (! Schema::hasTable('academy_quiz_questions')) {
            Schema::create('academy_quiz_questions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('academy_quiz_id')->constrained('academy_quizzes')->cascadeOnDelete();
                $table->string('question_type', 24)->default('multiple_choice');
                $table->text('prompt');
                $table->unsignedSmallInteger('points')->default(1);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->text('explanation')->nullable();
                $table->timestamps();
                $table->index(['academy_quiz_id','sort_order']);
            });
        }

        if (! Schema::hasTable('academy_quiz_options')) {
            Schema::create('academy_quiz_options', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('academy_quiz_question_id')->constrained('academy_quiz_questions')->cascadeOnDelete();
                $table->string('label', 1000);
                $table->boolean('is_correct')->default(false);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['academy_quiz_question_id','sort_order']);
            });
        }

        if (! Schema::hasTable('academy_quiz_attempts')) {
            Schema::create('academy_quiz_attempts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academy_quiz_id')->constrained('academy_quizzes')->cascadeOnDelete();
                $table->unsignedSmallInteger('attempt_number');
                $table->unsignedSmallInteger('score')->default(0);
                $table->unsignedSmallInteger('max_score')->default(0);
                $table->unsignedTinyInteger('percent')->default(0);
                $table->boolean('passed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id','academy_quiz_id','attempt_number'], 'academy_quiz_attempt_uq');
                $table->index(['institution_id','user_id','passed']);
            });
        }

        if (! Schema::hasTable('academy_quiz_answers')) {
            Schema::create('academy_quiz_answers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('academy_quiz_attempt_id')->constrained('academy_quiz_attempts')->cascadeOnDelete();
                $table->foreignId('academy_quiz_question_id')->constrained('academy_quiz_questions')->cascadeOnDelete();
                $table->foreignId('academy_quiz_option_id')->nullable()->constrained('academy_quiz_options')->nullOnDelete();
                $table->text('answer_text')->nullable();
                $table->boolean('is_correct')->default(false);
                $table->unsignedSmallInteger('awarded_points')->default(0);
                $table->timestamps();
                $table->unique(['academy_quiz_attempt_id','academy_quiz_question_id'], 'academy_quiz_answer_uq');
            });
        }

        if (! Schema::hasTable('academy_worksheets')) {
            Schema::create('academy_worksheets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('academy_lesson_id')->constrained('academy_lessons')->cascadeOnDelete();
                $table->string('title');
                $table->text('instructions')->nullable();
                $table->string('completion_mode', 24)->default('reflection');
                $table->boolean('is_required')->default(true);
                $table->string('status', 20)->default('published');
                $table->timestamps();
                $table->unique('academy_lesson_id');
            });
        }

        if (! Schema::hasTable('academy_worksheet_submissions')) {
            Schema::create('academy_worksheet_submissions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academy_worksheet_id')->constrained('academy_worksheets')->cascadeOnDelete();
                $table->longText('response')->nullable();
                $table->string('status', 20)->default('completed');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id','academy_worksheet_id'], 'academy_worksheet_submission_uq');
            });
        }

        if (! Schema::hasTable('academy_certificates')) {
            Schema::create('academy_certificates', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academy_program_id')->constrained('academy_programs')->cascadeOnDelete();
                $table->string('certificate_number', 80)->unique();
                $table->string('verification_code', 64)->unique();
                $table->string('status', 20)->default('issued');
                $table->timestamp('issued_at');
                $table->timestamps();
                $table->unique(['user_id','academy_program_id'], 'academy_certificate_user_program_uq');
                $table->index(['institution_id','status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_certificates');
        Schema::dropIfExists('academy_worksheet_submissions');
        Schema::dropIfExists('academy_worksheets');
        Schema::dropIfExists('academy_quiz_answers');
        Schema::dropIfExists('academy_quiz_attempts');
        Schema::dropIfExists('academy_quiz_options');
        Schema::dropIfExists('academy_quiz_questions');
        Schema::dropIfExists('academy_quizzes');
        Schema::dropIfExists('academy_prerequisites');
    }
};
