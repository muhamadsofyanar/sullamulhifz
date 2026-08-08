<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('marhalah_types')) {
            Schema::table('marhalah_types', function (Blueprint $table): void {
                if (! Schema::hasColumn('marhalah_types', 'juz_from')) {
                    $table->unsignedTinyInteger('juz_from')->nullable()->after('line_count');
                }
                if (! Schema::hasColumn('marhalah_types', 'juz_to')) {
                    $table->unsignedTinyInteger('juz_to')->nullable()->after('juz_from');
                }
                if (! Schema::hasColumn('marhalah_types', 'portion_unit')) {
                    $table->string('portion_unit', 30)->nullable()->after('juz_to');
                }
                if (! Schema::hasColumn('marhalah_types', 'portion_value')) {
                    $table->decimal('portion_value', 6, 2)->nullable()->after('portion_unit');
                }
                if (! Schema::hasColumn('marhalah_types', 'portion_label')) {
                    $table->string('portion_label', 190)->nullable()->after('portion_value');
                }
                if (! Schema::hasColumn('marhalah_types', 'journey_note')) {
                    $table->text('journey_note')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('memorization_targets')) {
            Schema::table('memorization_targets', function (Blueprint $table): void {
                if (! Schema::hasColumn('memorization_targets', 'journey_juz_number')) {
                    $table->unsignedTinyInteger('journey_juz_number')->nullable()->after('marhalah_type_id');
                }
                if (! Schema::hasColumn('memorization_targets', 'portion_confirmed')) {
                    $table->boolean('portion_confirmed')->default(false)->after('journey_juz_number');
                }
                if (! Schema::hasColumn('memorization_targets', 'portion_note')) {
                    $table->string('portion_note', 500)->nullable()->after('portion_confirmed');
                }
            });
        }

        if (! Schema::hasTable('quran_journey_profiles')) {
            Schema::create('quran_journey_profiles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('current_marhalah_type_id')->nullable()->constrained('marhalah_types')->nullOnDelete();
                $table->unsignedTinyInteger('current_juz_number')->nullable();
                $table->string('stage_code', 40)->nullable();
                $table->string('cadence_mode', 30)->default('flexible');
                $table->text('cadence_notes')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('foundation_completed_at')->nullable();
                $table->foreignId('updated_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->string('status', 30)->default('active');
                $table->timestamps();
                $table->unique(['institution_id', 'student_id'], 'quran_journey_profile_student_uq');
                $table->index(['institution_id', 'current_juz_number']);
            });
        }

        if (! Schema::hasTable('quran_journey_portions')) {
            Schema::create('quran_journey_portions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('marhalah_type_id')->nullable()->constrained('marhalah_types')->nullOnDelete();
                $table->foreignId('assigned_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->unsignedTinyInteger('journey_juz_number');
                $table->string('portion_unit', 30);
                $table->decimal('portion_value', 6, 2);
                $table->string('portion_label', 190);
                $table->unsignedInteger('start_global_number');
                $table->unsignedInteger('end_global_number');
                $table->unsignedSmallInteger('start_surah_id');
                $table->unsignedSmallInteger('start_verse');
                $table->unsignedSmallInteger('end_surah_id');
                $table->unsignedSmallInteger('end_verse');
                $table->unsignedSmallInteger('start_page_number')->nullable();
                $table->unsignedSmallInteger('end_page_number')->nullable();
                $table->boolean('teacher_confirmed')->default(false);
                $table->string('status', 30)->default('planned');
                $table->date('scheduled_for')->nullable();
                $table->date('due_date')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['institution_id','student_id','status'], 'quran_journey_portion_student_idx');
                $table->index(['student_id','journey_juz_number'], 'quran_journey_portion_juz_idx');
            });
        }

        if (Schema::hasTable('memorization_targets') && ! Schema::hasColumn('memorization_targets', 'quran_journey_portion_id')) {
            Schema::table('memorization_targets', function (Blueprint $table): void {
                $table->foreignId('quran_journey_portion_id')->nullable()->after('portion_note')
                    ->constrained('quran_journey_portions')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('memorization_milestones')) {
            Schema::create('memorization_milestones', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->string('unit_type', 30);
                $table->string('unit_key', 100);
                $table->string('label', 190);
                $table->unsignedSmallInteger('start_surah_id')->nullable();
                $table->unsignedSmallInteger('end_surah_id')->nullable();
                $table->unsignedInteger('start_global_number')->nullable();
                $table->unsignedInteger('end_global_number')->nullable();
                $table->string('memorization_status', 30)->default('not_started');
                $table->string('retention_status', 30)->default('not_assessed');
                $table->timestamp('memorized_at')->nullable();
                $table->timestamp('maintained_at')->nullable();
                $table->foreignId('verified_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['institution_id', 'student_id', 'unit_type', 'unit_key'], 'memorization_milestone_unit_uq');
                $table->index(['student_id', 'unit_type', 'memorization_status'], 'memorization_milestone_status_idx');
            });
        }

        if (! Schema::hasTable('memorization_retention_checks')) {
            Schema::create('memorization_retention_checks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('memorization_milestone_id')->constrained('memorization_milestones')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
                $table->string('result', 30);
                $table->string('assistance_level', 30)->default('none');
                $table->timestamp('checked_at');
                $table->date('next_check_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['student_id', 'checked_at']);
            });
        }

        if (! Schema::hasTable('quran_program_templates')) {
            Schema::create('quran_program_templates', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 80)->unique();
                $table->string('name', 190);
                $table->string('program_type', 40);
                $table->unsignedSmallInteger('duration_days')->nullable();
                $table->text('description');
                $table->text('scholarly_note')->nullable();
                $table->string('status', 30)->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quran_program_steps')) {
            Schema::create('quran_program_steps', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('quran_program_template_id')->constrained('quran_program_templates')->cascadeOnDelete();
                $table->unsignedSmallInteger('sequence');
                $table->string('mnemonic_letter', 20)->nullable();
                $table->string('label', 190);
                $table->unsignedSmallInteger('start_surah_id')->nullable();
                $table->unsignedSmallInteger('end_surah_id')->nullable();
                $table->unsignedTinyInteger('start_juz')->nullable();
                $table->unsignedTinyInteger('end_juz')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->unique(['quran_program_template_id', 'sequence'], 'quran_program_step_sequence_uq');
            });
        }

        if (! Schema::hasTable('quran_program_enrollments')) {
            Schema::create('quran_program_enrollments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('quran_program_template_id')->constrained('quran_program_templates')->restrictOnDelete();
                $table->foreignId('student_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('assigned_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->string('purpose', 30)->default('tilawah');
                $table->string('schedule_mode', 30)->default('flexible');
                $table->date('start_date');
                $table->date('target_end_date')->nullable();
                $table->string('status', 30)->default('active');
                $table->unsignedSmallInteger('current_step')->default(1);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'student_id', 'status'], 'quran_program_student_idx');
                $table->index(['institution_id', 'user_id', 'status'], 'quran_program_user_idx');
            });
        }

        if (! Schema::hasTable('quran_program_progress')) {
            Schema::create('quran_program_progress', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('quran_program_enrollment_id')->constrained('quran_program_enrollments')->cascadeOnDelete();
                $table->foreignId('quran_program_step_id')->constrained('quran_program_steps')->cascadeOnDelete();
                $table->string('status', 30)->default('pending');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedSmallInteger('last_surah_id')->nullable();
                $table->unsignedSmallInteger('last_verse')->nullable();
                $table->unsignedInteger('last_global_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['quran_program_enrollment_id', 'quran_program_step_id'], 'quran_program_progress_step_uq');
            });
        }

        if (! Schema::hasTable('quran_division_units')) {
            Schema::create('quran_division_units', function (Blueprint $table): void {
                $table->id();
                $table->string('unit_type', 30);
                $table->unsignedSmallInteger('unit_number');
                $table->string('code', 80)->nullable();
                $table->string('label', 190);
                $table->unsignedInteger('start_global_number');
                $table->unsignedInteger('end_global_number');
                $table->unsignedSmallInteger('start_surah_id');
                $table->unsignedSmallInteger('start_verse');
                $table->unsignedSmallInteger('end_surah_id');
                $table->unsignedSmallInteger('end_verse');
                $table->unsignedTinyInteger('juz_number')->nullable();
                $table->unsignedSmallInteger('hizb_quarter')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->unique(['unit_type', 'unit_number'], 'quran_division_unit_number_uq');
                $table->index(['unit_type', 'start_global_number']);
            });
        }

        if (! Schema::hasTable('quran_heritage_terms')) {
            Schema::create('quran_heritage_terms', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 80)->unique();
                $table->string('name', 190);
                $table->string('arabic_name', 190)->nullable();
                $table->text('short_description');
                $table->text('practical_use')->nullable();
                $table->text('context_note')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->string('status', 30)->default('active');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_heritage_terms');
        Schema::dropIfExists('quran_division_units');
        Schema::dropIfExists('quran_program_progress');
        Schema::dropIfExists('quran_program_enrollments');
        Schema::dropIfExists('quran_program_steps');
        Schema::dropIfExists('quran_program_templates');
        Schema::dropIfExists('memorization_retention_checks');
        Schema::dropIfExists('memorization_milestones');

        if (Schema::hasTable('memorization_targets')) {
            Schema::table('memorization_targets', function (Blueprint $table): void {
                if (Schema::hasColumn('memorization_targets', 'quran_journey_portion_id')) {
                    $table->dropConstrainedForeignId('quran_journey_portion_id');
                }
                foreach (['portion_note', 'portion_confirmed', 'journey_juz_number'] as $column) {
                    if (Schema::hasColumn('memorization_targets', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('quran_journey_portions');
        Schema::dropIfExists('quran_journey_profiles');

        if (Schema::hasTable('marhalah_types')) {
            Schema::table('marhalah_types', function (Blueprint $table): void {
                foreach (['journey_note', 'portion_label', 'portion_value', 'portion_unit', 'juz_to', 'juz_from'] as $column) {
                    if (Schema::hasColumn('marhalah_types', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
