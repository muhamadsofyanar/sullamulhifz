<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('liaison_threads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('category')->default('learning');
            $table->string('subject');
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['student_id', 'status']);
        });

        Schema::create('liaison_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liaison_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('participant_role');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->unique(['liaison_thread_id', 'user_id']);
        });

        Schema::create('liaison_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('liaison_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->restrictOnDelete();
            $table->text('message');
            $table->string('message_type')->default('text');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('title');
            $table->text('content');
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('friday_development_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->date('session_date');
            $table->string('category');
            $table->string('title');
            $table->text('objectives')->nullable();
            $table->text('summary');
            $table->unsignedSmallInteger('quran_surah_id')->nullable();
            $table->unsignedSmallInteger('quran_start_verse')->nullable();
            $table->unsignedSmallInteger('quran_end_verse')->nullable();
            $table->text('home_follow_up')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('quran_surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
        });

        Schema::create('student_consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->string('consent_type');
            $table->string('consent_status');
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'guardian_id', 'consent_type']);
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['notifications','activity_logs','student_consents','friday_development_sessions','announcements','liaison_messages','liaison_participants','liaison_threads'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
