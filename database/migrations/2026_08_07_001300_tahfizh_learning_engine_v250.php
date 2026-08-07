<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('tahfizh_learning_cycles')) {
            Schema::create('tahfizh_learning_cycles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('memorization_target_id')->nullable()->constrained()->nullOnDelete();
                $table->string('cycle_type')->default('new_memorization');
                $table->string('preparation_method')->default('talaqqi');
                $table->string('status')->default('preparing');
                $table->text('teacher_guidance')->nullable();
                $table->text('guardian_guidance')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ready_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['institution_id', 'student_id', 'status'], 'tahfizh_cycles_student_status_idx');
            });
        }

        if (! Schema::hasTable('memorization_review_plans')) {
            Schema::create('memorization_review_plans', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->foreignId('memorization_target_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('source_memorization_record_id')->nullable()->constrained('memorization_records')->nullOnDelete();
                $table->unsignedBigInteger('completed_by_murajaah_record_id')->nullable();
                $table->foreign('completed_by_murajaah_record_id', 'review_plan_completed_murajaah_fk')->references('id')->on('murajaah_records')->nullOnDelete();
                $table->unsignedSmallInteger('surah_id');
                $table->unsignedSmallInteger('start_verse');
                $table->unsignedSmallInteger('end_verse');
                $table->date('review_date');
                $table->string('review_type')->default('scheduled');
                $table->string('priority')->default('normal');
                $table->string('status')->default('scheduled');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->foreign('surah_id')->references('id')->on('quran_surahs')->restrictOnDelete();
                $table->index(['institution_id', 'review_date', 'status'], 'review_plans_due_idx');
                $table->index(['student_id', 'status']);
            });
        }

        if (! Schema::hasTable('quran_learning_error_items')) {
            Schema::create('quran_learning_error_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('meeting_id')->nullable()->constrained()->nullOnDelete();
                $table->string('record_type');
                $table->unsignedBigInteger('record_id');
                $table->string('category');
                $table->string('severity')->default('attention');
                $table->unsignedSmallInteger('ayah_number')->nullable();
                $table->text('note')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->index(['record_type', 'record_id']);
                $table->index(['student_id', 'category', 'resolved_at'], 'quran_errors_student_category_idx');
            });
        }

        Schema::table('memorization_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('memorization_records', 'learning_cycle_id')) {
                $table->foreignId('learning_cycle_id')->nullable()->after('memorization_target_id')->constrained('tahfizh_learning_cycles')->nullOnDelete();
            }
            if (! Schema::hasColumn('memorization_records', 'delivery_mode')) {
                $table->string('delivery_mode')->default('individual_submission')->after('record_type');
            }
            if (! Schema::hasColumn('memorization_records', 'prompt_count')) {
                $table->unsignedSmallInteger('prompt_count')->nullable()->after('error_count');
            }
            if (! Schema::hasColumn('memorization_records', 'self_correction_count')) {
                $table->unsignedSmallInteger('self_correction_count')->nullable()->after('prompt_count');
            }
            if (! Schema::hasColumn('memorization_records', 'next_review_date')) {
                $table->date('next_review_date')->nullable()->after('review_recommendation');
            }
        });

        Schema::table('murajaah_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('murajaah_records', 'learning_cycle_id')) {
                $table->foreignId('learning_cycle_id')->nullable()->after('meeting_id')->constrained('tahfizh_learning_cycles')->nullOnDelete();
            }
            if (! Schema::hasColumn('murajaah_records', 'review_plan_id')) {
                $table->foreignId('review_plan_id')->nullable()->after('learning_cycle_id')->constrained('memorization_review_plans')->nullOnDelete();
            }
            if (! Schema::hasColumn('murajaah_records', 'prompt_count')) {
                $table->unsignedSmallInteger('prompt_count')->nullable()->after('assistance_level');
            }
            if (! Schema::hasColumn('murajaah_records', 'self_correction_count')) {
                $table->unsignedSmallInteger('self_correction_count')->nullable()->after('prompt_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('murajaah_records', function (Blueprint $table): void {
            foreach (['review_plan_id', 'learning_cycle_id'] as $column) {
                if (Schema::hasColumn('murajaah_records', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
            foreach (['prompt_count', 'self_correction_count'] as $column) {
                if (Schema::hasColumn('murajaah_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('memorization_records', function (Blueprint $table): void {
            if (Schema::hasColumn('memorization_records', 'learning_cycle_id')) {
                $table->dropConstrainedForeignId('learning_cycle_id');
            }
            foreach (['delivery_mode', 'prompt_count', 'self_correction_count', 'next_review_date'] as $column) {
                if (Schema::hasColumn('memorization_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('quran_learning_error_items');
        Schema::dropIfExists('memorization_review_plans');
        Schema::dropIfExists('tahfizh_learning_cycles');
    }
};
