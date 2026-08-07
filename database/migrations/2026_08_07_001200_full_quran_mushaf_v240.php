<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('quran_ayahs')) {
            Schema::create('quran_ayahs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('global_number')->unique();
                $table->unsignedSmallInteger('surah_id');
                $table->unsignedSmallInteger('verse_number');
                $table->longText('arabic_text');
                $table->unsignedTinyInteger('juz_number')->nullable();
                $table->unsignedSmallInteger('hizb_quarter')->nullable();
                $table->unsignedSmallInteger('page_number')->nullable();
                $table->unsignedSmallInteger('ruku_number')->nullable();
                $table->unsignedTinyInteger('manzil_number')->nullable();
                $table->boolean('sajda')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('surah_id')->references('id')->on('quran_surahs')->cascadeOnDelete();
                $table->unique(['surah_id', 'verse_number'], 'quran_ayah_surah_verse_uq');
                $table->index(['juz_number', 'global_number']);
                $table->index(['page_number', 'global_number']);
                $table->index(['hizb_quarter', 'global_number']);
            });
        }

        if (! Schema::hasTable('quran_reading_progress')) {
            Schema::create('quran_reading_progress', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedSmallInteger('surah_id')->nullable();
                $table->unsignedSmallInteger('verse_number')->nullable();
                $table->unsignedInteger('global_number')->nullable();
                $table->unsignedTinyInteger('juz_number')->nullable();
                $table->unsignedSmallInteger('page_number')->nullable();
                $table->unsignedSmallInteger('hizb_quarter')->nullable();
                $table->foreignId('quran_audio_source_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamp('last_read_at')->nullable();
                $table->unsignedInteger('visit_count')->default(0);
                $table->timestamps();

                $table->foreign('surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
                $table->unique(['institution_id', 'user_id'], 'quran_reading_progress_user_uq');
            });
        }

        if (Schema::hasTable('quran_practice_presets')) {
            Schema::table('quran_practice_presets', function (Blueprint $table): void {
                if (! Schema::hasColumn('quran_practice_presets', 'juz_number')) {
                    $table->unsignedTinyInteger('juz_number')->nullable()->after('page_number');
                }
                if (! Schema::hasColumn('quran_practice_presets', 'hizb_quarter')) {
                    $table->unsignedSmallInteger('hizb_quarter')->nullable()->after('juz_number');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('quran_practice_presets')) {
            Schema::table('quran_practice_presets', function (Blueprint $table): void {
                foreach (['hizb_quarter', 'juz_number'] as $column) {
                    if (Schema::hasColumn('quran_practice_presets', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('quran_reading_progress');
        Schema::dropIfExists('quran_ayahs');
    }
};
