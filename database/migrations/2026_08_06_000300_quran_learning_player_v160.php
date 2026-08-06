<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('quran_audio_sources')) {
            Schema::create('quran_audio_sources', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('provider')->default('mp3quran');
                $table->string('external_id')->nullable();
                $table->string('reciter_name');
                $table->string('rewaya')->nullable();
                $table->text('base_url')->nullable();
                $table->json('metadata')->nullable();
                $table->boolean('is_default')->default(false);
                $table->string('status')->default('active');
                $table->timestamps();
                $table->unique(['institution_id', 'provider', 'external_id'], 'quran_audio_source_scope_uq');
            });
        }

        if (! Schema::hasTable('quran_ayah_timings')) {
            Schema::create('quran_ayah_timings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('quran_audio_source_id')->constrained()->cascadeOnDelete();
                $table->unsignedSmallInteger('surah_id');
                $table->unsignedSmallInteger('verse_number');
                $table->unsignedInteger('start_ms');
                $table->unsignedInteger('end_ms');
                $table->unsignedSmallInteger('page_number')->nullable();
                $table->text('page_image_url')->nullable();
                $table->text('audio_url');
                $table->text('polygon')->nullable();
                $table->decimal('x', 8, 2)->nullable();
                $table->decimal('y', 8, 2)->nullable();
                $table->timestamps();
                $table->foreign('surah_id')->references('id')->on('quran_surahs')->cascadeOnDelete();
                $table->unique(['quran_audio_source_id', 'surah_id', 'verse_number'], 'quran_ayah_timing_source_verse_uq');
                $table->index(['quran_audio_source_id', 'page_number']);
            });
        }

        if (! Schema::hasTable('quran_video_resources')) {
            Schema::create('quran_video_resources', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title');
                $table->string('source_type')->default('youtube');
                $table->text('video_url');
                $table->text('thumbnail_url')->nullable();
                $table->unsignedSmallInteger('surah_id')->nullable();
                $table->unsignedSmallInteger('start_verse')->nullable();
                $table->unsignedSmallInteger('end_verse')->nullable();
                $table->unsignedInteger('start_seconds')->nullable();
                $table->unsignedInteger('end_seconds')->nullable();
                $table->unsignedSmallInteger('default_repeat')->default(1);
                $table->text('notes')->nullable();
                $table->string('status')->default('draft');
                $table->timestamps();
                $table->foreign('surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
                $table->index(['institution_id', 'status']);
            });
        }

        if (! Schema::hasTable('quran_practice_presets')) {
            Schema::create('quran_practice_presets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('quran_audio_source_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('quran_video_resource_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('quran_rubu_id')->nullable()->constrained('quran_rubus')->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('mode')->default('range');
                $table->unsignedSmallInteger('page_number')->nullable();
                $table->unsignedSmallInteger('start_surah_id')->nullable();
                $table->unsignedSmallInteger('end_surah_id')->nullable();
                $table->unsignedSmallInteger('start_verse')->nullable();
                $table->unsignedSmallInteger('end_verse')->nullable();
                $table->unsignedSmallInteger('repeat_count')->default(3);
                $table->string('repeat_scope')->default('each_item');
                $table->unsignedSmallInteger('gap_seconds')->default(1);
                $table->decimal('playback_rate', 4, 2)->default(1.00);
                $table->string('audience')->default('all');
                $table->boolean('is_featured')->default(false);
                $table->string('status')->default('active');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->foreign('start_surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
                $table->foreign('end_surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
                $table->index(['institution_id', 'status', 'is_featured'], 'quran_preset_listing_idx');
            });
        }

        if (! Schema::hasTable('quran_practice_sessions')) {
            Schema::create('quran_practice_sessions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('quran_practice_preset_id')->nullable()->constrained()->nullOnDelete();
                $table->string('mode');
                $table->json('selection');
                $table->unsignedSmallInteger('repeat_target')->default(1);
                $table->unsignedSmallInteger('repeat_completed')->default(0);
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->string('status')->default('started');
                $table->timestamps();
                $table->index(['user_id', 'started_at']);
                $table->index(['student_id', 'started_at']);
            });
        }

        $institutionIds = DB::table('institutions')->pluck('id');
        foreach ($institutionIds as $institutionId) {
            DB::table('quran_audio_sources')->updateOrInsert(
                ['institution_id' => $institutionId, 'provider' => 'mp3quran', 'external_id' => '5'],
                [
                    'name' => 'Murattal Ahmad Al-Ajmi',
                    'reciter_name' => 'Ahmad bin Ali Al-Ajmi',
                    'rewaya' => 'Hafs dari Asim — Murattal',
                    'base_url' => 'https://server10.mp3quran.net/ajm/',
                    'metadata' => json_encode([
                        'timing_endpoint' => 'https://mp3quran.net/api/v3/ayat_timing',
                        'source_attribution' => 'MP3Quran.net',
                        'sync_scope' => 'Juz 30',
                    ], JSON_UNESCAPED_UNICODE),
                    'is_default' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_practice_sessions');
        Schema::dropIfExists('quran_practice_presets');
        Schema::dropIfExists('quran_video_resources');
        Schema::dropIfExists('quran_ayah_timings');
        Schema::dropIfExists('quran_audio_sources');
    }
};
