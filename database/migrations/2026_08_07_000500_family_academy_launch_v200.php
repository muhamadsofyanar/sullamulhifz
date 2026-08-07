<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('academy_programs')) {
            Schema::create('academy_programs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('slug');
                $table->string('audience', 30)->default('guardian');
                $table->text('summary')->nullable();
                $table->longText('description')->nullable();
                $table->string('cover_url')->nullable();
                $table->string('status', 30)->default('draft');
                $table->boolean('is_featured')->default(false);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['institution_id', 'slug']);
                $table->index(['institution_id', 'status', 'audience']);
            });
        }

        if (! Schema::hasTable('academy_modules')) {
            Schema::create('academy_modules', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('academy_program_id')->constrained('academy_programs')->cascadeOnDelete();
                $table->string('title');
                $table->text('summary')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->string('status', 30)->default('published');
                $table->timestamps();
                $table->index(['academy_program_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('academy_lessons')) {
            Schema::create('academy_lessons', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('academy_module_id')->constrained('academy_modules')->cascadeOnDelete();
                $table->string('title');
                $table->string('slug');
                $table->string('lesson_type', 30)->default('article');
                $table->text('summary')->nullable();
                $table->longText('body')->nullable();
                $table->string('media_url')->nullable();
                $table->unsignedSmallInteger('duration_minutes')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('requires_action')->default(false);
                $table->string('status', 30)->default('draft');
                $table->timestamps();
                $table->unique(['academy_module_id', 'slug']);
                $table->index(['academy_module_id', 'status', 'sort_order']);
            });
        }

        if (! Schema::hasTable('academy_lesson_progress')) {
            Schema::create('academy_lesson_progress', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academy_lesson_id')->constrained('academy_lessons')->cascadeOnDelete();
                $table->string('status', 30)->default('started');
                $table->unsignedTinyInteger('progress_percent')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'academy_lesson_id'], 'academy_progress_user_lesson_uq');
                $table->index(['institution_id', 'user_id', 'status']);
            });
        }

        if (! Schema::hasTable('academy_recommendations')) {
            Schema::create('academy_recommendations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academy_lesson_id')->constrained('academy_lessons')->cascadeOnDelete();
                $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->text('message')->nullable();
                $table->string('status', 30)->default('active');
                $table->timestamp('recommended_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'student_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_recommendations');
        Schema::dropIfExists('academy_lesson_progress');
        Schema::dropIfExists('academy_lessons');
        Schema::dropIfExists('academy_modules');
        Schema::dropIfExists('academy_programs');
    }
};
