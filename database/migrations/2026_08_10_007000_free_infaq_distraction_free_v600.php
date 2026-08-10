<?php

/** @phase 6.0 Free, Infaq & Distraction-Free Tahfizh */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('memorization_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('memorization_records', 'daily_decision')) {
                $table->string('daily_decision', 24)->nullable()->after('result');
            }
            if (! Schema::hasColumn('memorization_records', 'short_note')) {
                $table->string('short_note', 500)->nullable()->after('daily_decision');
            }
            if (! Schema::hasColumn('memorization_records', 'submission_key')) {
                $table->string('submission_key', 64)->nullable()->after('short_note');
                $table->unique(
                    ['institution_id', 'student_id', 'submission_key'],
                    'memorization_submission_scope_unique',
                );
            }
        });

        Schema::table('murajaah_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('murajaah_records', 'daily_decision')) {
                $table->string('daily_decision', 24)->nullable()->after('result');
            }
            if (! Schema::hasColumn('murajaah_records', 'short_note')) {
                $table->string('short_note', 500)->nullable()->after('daily_decision');
            }
            if (! Schema::hasColumn('murajaah_records', 'submission_key')) {
                $table->string('submission_key', 64)->nullable()->after('short_note');
                $table->unique(
                    ['institution_id', 'student_id', 'submission_key'],
                    'murajaah_submission_scope_unique',
                );
            }
        });

        if (! Schema::hasTable('student_memorization_focuses')) {
            Schema::create('student_memorization_focuses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('set_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->string('focus_key', 40);
                $table->string('status', 20)->default('active');
                $table->text('notes')->nullable();
                $table->timestamp('started_at');
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'student_id', 'status'], 'memorization_focus_active_idx');
            });
        }

        if (! Schema::hasTable('student_memorization_assessments')) {
            Schema::create('student_memorization_assessments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
                $table->string('assessment_type', 30);
                $table->date('assessed_on');
                $table->string('accuracy_status', 30)->nullable();
                $table->string('fluency_status', 30)->nullable();
                $table->string('independence_status', 30)->nullable();
                $table->string('makhraj_tajwid_status', 30)->nullable();
                $table->string('retention_status', 30)->nullable();
                $table->string('recommended_focus', 40)->nullable();
                $table->text('summary')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'student_id', 'assessed_on'], 'memorization_assessment_student_idx');
            });
        }

        if (! Schema::hasTable('infaq_transactions')) {
            Schema::create('infaq_transactions', function (Blueprint $table): void {
                $table->id();
                $table->uuid('public_id')->unique();
                $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('idempotency_key', 80);
                $table->string('purpose', 40);
                $table->decimal('amount', 14, 2);
                $table->char('currency', 3)->default('IDR');
                $table->string('payment_method', 30)->default('bank_transfer');
                $table->string('status', 24)->default('pending');
                $table->boolean('is_anonymous')->default(false);
                $table->string('receipt_number', 80)->nullable()->unique();
                $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('verified_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'status', 'created_at'], 'infaq_scope_status_idx');
                $table->index(['purpose', 'status', 'created_at'], 'infaq_purpose_status_idx');
                $table->unique(['user_id', 'idempotency_key'], 'infaq_user_idempotency_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('infaq_transactions');
        Schema::dropIfExists('student_memorization_assessments');
        Schema::dropIfExists('student_memorization_focuses');

        Schema::table('murajaah_records', function (Blueprint $table): void {
            if (Schema::hasColumn('murajaah_records', 'submission_key')) {
                $table->dropUnique('murajaah_submission_scope_unique');
            }
            foreach (['submission_key', 'short_note', 'daily_decision'] as $column) {
                if (Schema::hasColumn('murajaah_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        Schema::table('memorization_records', function (Blueprint $table): void {
            if (Schema::hasColumn('memorization_records', 'submission_key')) {
                $table->dropUnique('memorization_submission_scope_unique');
            }
            foreach (['submission_key', 'short_note', 'daily_decision'] as $column) {
                if (Schema::hasColumn('memorization_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
