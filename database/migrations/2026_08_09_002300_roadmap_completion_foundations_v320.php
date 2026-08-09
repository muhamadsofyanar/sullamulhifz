<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('talent_progress_records')) {
            Schema::create('talent_progress_records', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
                $table->foreignId('learning_group_id')->nullable()->constrained('learning_groups')->nullOnDelete();
                $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('domain', 80);
                $table->string('rubric_key', 100);
                $table->string('progress_level', 30);
                $table->text('observation');
                $table->text('next_step')->nullable();
                $table->date('recorded_on');
                $table->string('status', 24)->default('active');
                $table->timestamps();
                $table->index(['institution_id', 'student_id', 'recorded_on'], 'talent_progress_student_date_idx');
                $table->index(['program_id', 'learning_group_id', 'status'], 'talent_progress_program_group_idx');
            });
        }

        if (! Schema::hasTable('student_portfolio_evidence')) {
            Schema::create('student_portfolio_evidence', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_portfolio_id')->constrained('student_portfolios')->cascadeOnDelete();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
                $table->string('evidence_type', 40)->default('note');
                $table->string('label', 180);
                $table->text('reference_url')->nullable();
                $table->text('note')->nullable();
                $table->date('occurred_on')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'student_portfolio_id'], 'portfolio_evidence_portfolio_idx');
            });
        }

        if (! Schema::hasTable('ai_assist_drafts')) {
            Schema::create('ai_assist_drafts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('purpose', 80);
                $table->json('evidence_snapshot')->nullable();
                $table->longText('draft_text');
                $table->string('provider', 80)->nullable();
                $table->string('model', 100)->nullable();
                $table->string('status', 30)->default('pending_review');
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'status', 'created_at'], 'ai_drafts_status_idx');
                $table->index(['student_id', 'created_at'], 'ai_drafts_student_idx');
            });
        }

        if (! Schema::hasTable('ai_assist_reviews')) {
            Schema::create('ai_assist_reviews', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('ai_assist_draft_id')->constrained('ai_assist_drafts')->cascadeOnDelete();
                $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('decision', 24);
                $table->longText('final_text')->nullable();
                $table->text('review_note')->nullable();
                $table->timestamp('reviewed_at');
                $table->timestamps();
                $table->unique('ai_assist_draft_id', 'ai_review_draft_uq');
                $table->index(['institution_id', 'reviewed_at'], 'ai_reviews_institution_date_idx');
            });
        }

        if (! Schema::hasTable('community_moderation_actions')) {
            Schema::create('community_moderation_actions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('community_space_id')->constrained('community_spaces')->cascadeOnDelete();
                $table->foreignId('community_post_id')->nullable()->constrained('community_posts')->nullOnDelete();
                $table->foreignId('moderator_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('action', 30);
                $table->text('reason')->nullable();
                $table->string('policy_version', 40);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'community_space_id', 'created_at'], 'community_moderation_space_idx');
            });
        }

        if (! Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('provider', 60)->nullable();
                $table->string('external_reference', 190)->nullable();
                $table->string('purpose', 80);
                $table->decimal('amount', 14, 2);
                $table->char('currency', 3)->default('IDR');
                $table->string('status', 30)->default('pending');
                $table->json('metadata')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'status', 'created_at'], 'payment_status_idx');
                $table->unique(['provider', 'external_reference'], 'payment_provider_reference_uq');
            });
        }

        if (Schema::hasTable('memorization_review_plans') && ! Schema::hasColumn('memorization_review_plans', 'reminder_sent_at')) {
            Schema::table('memorization_review_plans', function (Blueprint $table): void {
                $table->timestamp('reminder_sent_at')->nullable()->after('review_date')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('memorization_review_plans') && Schema::hasColumn('memorization_review_plans', 'reminder_sent_at')) {
            Schema::table('memorization_review_plans', function (Blueprint $table): void {
                $table->dropColumn('reminder_sent_at');
            });
        }

        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('community_moderation_actions');
        Schema::dropIfExists('ai_assist_reviews');
        Schema::dropIfExists('ai_assist_drafts');
        Schema::dropIfExists('student_portfolio_evidence');
        Schema::dropIfExists('talent_progress_records');
    }
};
