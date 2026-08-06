<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code')->nullable();
            $table->string('full_name');
            $table->string('nickname')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('joined_at')->nullable();
            $table->string('specialization')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['institution_id', 'employee_code']);
        });

        Schema::create('guardians', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('occupation')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('student_code')->nullable();
            $table->string('full_name');
            $table->string('nickname')->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->date('joined_at')->nullable();
            $table->string('status')->default('active');
            $table->text('special_needs_notes')->nullable();
            $table->string('stifin_status')->default('untested');
            $table->string('stifin_result')->nullable();
            $table->date('stifin_tested_at')->nullable();
            $table->text('stifin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['institution_id', 'student_code']);
            $table->index(['institution_id', 'status']);
        });

        Schema::create('student_guardians', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->string('relationship')->default('guardian');
            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('can_receive_notifications')->default(true);
            $table->boolean('can_submit_assignments')->default(true);
            $table->boolean('can_view_learning_records')->default(true);
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'guardian_id', 'relationship'], 'student_guardian_relation_unique');
        });

        Schema::create('academic_years', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('draft');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['institution_id', 'name']);
        });

        Schema::create('levels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->unsignedInteger('sequence')->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['institution_id', 'code']);
        });

        Schema::create('classes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('code');
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['academic_year_id', 'code']);
        });

        Schema::create('class_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('enrolled_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
            $table->unique(['class_id', 'student_id', 'academic_year_id']);
        });

        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('category')->default('quran');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['institution_id', 'code']);
        });

        Schema::create('learning_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('code');
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['academic_year_id', 'code']);
        });

        Schema::create('group_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('joined_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['learning_group_id', 'student_id']);
        });

        Schema::create('teacher_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->cascadeOnDelete();
            $table->foreignId('learning_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->string('assignment_role')->default('lead');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['teacher_id', 'status']);
        });

        Schema::create('schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->cascadeOnDelete();
            $table->foreignId('learning_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        foreach (['schedules','teacher_assignments','group_memberships','learning_groups','programs','class_enrollments','classes','levels','academic_years','student_guardians','students','guardians','teachers'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
