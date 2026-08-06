<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->cascadeOnDelete();
            $table->foreignId('learning_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->date('meeting_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('topic')->nullable();
            $table->text('general_notes')->nullable();
            $table->string('status')->default('ongoing');
            $table->timestamps();
            $table->index(['meeting_date', 'teacher_id']);
        });

        Schema::create('attendance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('present');
            $table->time('arrival_time')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['meeting_id', 'student_id']);
        });

        Schema::create('quran_surahs', function (Blueprint $table): void {
            $table->unsignedSmallInteger('id')->primary();
            $table->string('name_arabic');
            $table->string('name_latin');
            $table->string('revelation_place')->nullable();
            $table->unsignedSmallInteger('verse_count');
            $table->unsignedSmallInteger('sequence');
        });

        Schema::create('marhalah_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('sequence');
            $table->unsignedTinyInteger('line_count')->nullable();
            $table->text('description');
            $table->string('status')->default('active');
        });

        Schema::create('tahsin_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->text('material_text');
            $table->unsignedSmallInteger('surah_id')->nullable();
            $table->unsignedSmallInteger('start_verse')->nullable();
            $table->unsignedSmallInteger('end_verse')->nullable();
            $table->string('overall_status');
            $table->json('focus_categories')->nullable();
            $table->text('teacher_notes')->nullable();
            $table->string('follow_up')->nullable();
            $table->timestamps();
            $table->foreign('surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
            $table->index(['student_id', 'created_at']);
        });

        Schema::create('memorization_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->foreignId('marhalah_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('record_type')->default('new_memorization');
            $table->unsignedSmallInteger('surah_id');
            $table->unsignedSmallInteger('start_verse');
            $table->unsignedSmallInteger('end_verse');
            $table->string('result');
            $table->string('assistance_level')->default('none');
            $table->string('follow_up')->nullable();
            $table->text('teacher_notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->foreign('surah_id')->references('id')->on('quran_surahs')->restrictOnDelete();
            $table->index(['student_id', 'recorded_at']);
        });

        Schema::create('murajaah_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->string('murajaah_type')->default('scheduled');
            $table->unsignedSmallInteger('surah_id');
            $table->unsignedSmallInteger('start_verse');
            $table->unsignedSmallInteger('end_verse');
            $table->string('result');
            $table->string('assistance_level')->default('none');
            $table->date('next_review_date')->nullable();
            $table->text('teacher_notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->foreign('surah_id')->references('id')->on('quran_surahs')->restrictOnDelete();
            $table->index(['student_id', 'recorded_at']);
        });

        Schema::create('assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('learning_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('target_text');
            $table->unsignedSmallInteger('surah_id')->nullable();
            $table->unsignedSmallInteger('start_verse')->nullable();
            $table->unsignedSmallInteger('end_verse')->nullable();
            $table->string('learning_method')->default('repetition');
            $table->text('instructions');
            $table->json('evidence_types')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->boolean('allow_resubmission')->default(true);
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
            $table->index(['due_at', 'status']);
        });

        Schema::create('assignment_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_source')->default('class');
            $table->string('status')->default('assigned');
            $table->timestamp('first_viewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('assignment_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assignment_recipient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->text('guardian_notes')->nullable();
            $table->timestamp('submitted_at');
            $table->string('review_status')->default('pending');
            $table->foreignId('reviewed_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->text('teacher_feedback')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
            $table->unique(['assignment_recipient_id', 'attempt_number'], 'asgn_submission_recipient_attempt_uq');
        });
    }

    public function down(): void
    {
        foreach (['assignment_submissions','assignment_recipients','assignments','murajaah_records','memorization_records','tahsin_records','marhalah_types','quran_surahs','attendance_records','meetings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
