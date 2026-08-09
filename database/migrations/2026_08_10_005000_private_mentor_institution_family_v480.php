<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @phase 4.6 Private Ustadz
 * @phase 4.7 Institution Suite
 * @phase 4.8 Family & Parent Portal
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mentorship_sessions')) {
            Schema::create('mentorship_sessions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_relationship_id')->constrained('user_relationships')->cascadeOnDelete();
                $table->foreignId('learner_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('mentor_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('focus', 180);
                $table->text('learner_note')->nullable();
                $table->text('mentor_note')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->unsignedSmallInteger('duration_minutes')->nullable();
                $table->string('status', 30)->default('requested');
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
                $table->index(['mentor_user_id', 'status', 'scheduled_at'], 'mentorship_mentor_schedule_idx');
                $table->index(['learner_user_id', 'status', 'scheduled_at'], 'mentorship_learner_schedule_idx');
            });
        }

        if (! Schema::hasTable('family_support_notes')) {
            Schema::create('family_support_notes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_relationship_id')->constrained('user_relationships')->cascadeOnDelete();
                $table->foreignId('child_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('author_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('note_type', 30)->default('encouragement');
                $table->text('body');
                $table->date('observed_on');
                $table->string('status', 24)->default('visible');
                $table->timestamps();
                $table->index(['child_user_id', 'status', 'observed_on'], 'family_note_child_date_idx');
                $table->index(['user_relationship_id', 'observed_on'], 'family_note_relationship_date_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('family_support_notes');
        Schema::dropIfExists('mentorship_sessions');
    }
};
