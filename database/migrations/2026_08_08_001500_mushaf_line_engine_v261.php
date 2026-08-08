<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quran_mushaf_lines')) {
            Schema::create('quran_mushaf_lines', function (Blueprint $table): void {
                $table->id();
                $table->string('layout_code', 80)->default('kfgqpc-v2-1421h');
                $table->unsignedSmallInteger('page_number');
                $table->unsignedTinyInteger('line_number');
                $table->string('line_type', 30);
                $table->boolean('is_centered')->default(false);
                $table->text('text')->nullable();
                $table->text('qpc_v2')->nullable();
                $table->unsignedSmallInteger('surah_number')->nullable();
                $table->string('verse_range', 80)->nullable();
                $table->string('first_word_location', 40)->nullable();
                $table->string('last_word_location', 40)->nullable();
                $table->unsignedSmallInteger('first_surah_id')->nullable();
                $table->unsignedSmallInteger('first_verse')->nullable();
                $table->unsignedSmallInteger('first_word_position')->nullable();
                $table->unsignedSmallInteger('last_surah_id')->nullable();
                $table->unsignedSmallInteger('last_verse')->nullable();
                $table->unsignedSmallInteger('last_word_position')->nullable();
                $table->string('source_name', 120)->nullable();
                $table->string('source_ref', 120)->nullable();
                $table->timestamps();

                $table->unique(['layout_code', 'page_number', 'line_number'], 'quran_mushaf_line_slot_uq');
                $table->index(['layout_code', 'page_number'], 'quran_mushaf_line_page_idx');
                $table->index(['first_surah_id', 'first_verse'], 'quran_mushaf_line_first_ayah_idx');
                $table->index(['last_surah_id', 'last_verse'], 'quran_mushaf_line_last_ayah_idx');
            });
        }

        if (Schema::hasTable('quran_journey_portions')) {
            Schema::table('quran_journey_portions', function (Blueprint $table): void {
                if (! Schema::hasColumn('quran_journey_portions', 'mushaf_layout_code')) {
                    $table->string('mushaf_layout_code', 80)->nullable()->after('end_page_number');
                }
                if (! Schema::hasColumn('quran_journey_portions', 'start_line_number')) {
                    $table->unsignedTinyInteger('start_line_number')->nullable()->after('mushaf_layout_code');
                }
                if (! Schema::hasColumn('quran_journey_portions', 'end_line_number')) {
                    $table->unsignedTinyInteger('end_line_number')->nullable()->after('start_line_number');
                }
                if (! Schema::hasColumn('quran_journey_portions', 'start_word_location')) {
                    $table->string('start_word_location', 40)->nullable()->after('end_line_number');
                }
                if (! Schema::hasColumn('quran_journey_portions', 'end_word_location')) {
                    $table->string('end_word_location', 40)->nullable()->after('start_word_location');
                }
                if (! Schema::hasColumn('quran_journey_portions', 'line_block_key')) {
                    $table->string('line_block_key', 80)->nullable()->after('end_word_location');
                }
                if (! Schema::hasColumn('quran_journey_portions', 'selection_source')) {
                    $table->string('selection_source', 40)->nullable()->after('line_block_key');
                }
            });
        }

        if (Schema::hasTable('memorization_targets')) {
            Schema::table('memorization_targets', function (Blueprint $table): void {
                if (! Schema::hasColumn('memorization_targets', 'mushaf_page_number')) {
                    $table->unsignedSmallInteger('mushaf_page_number')->nullable()->after('portion_note');
                }
                if (! Schema::hasColumn('memorization_targets', 'mushaf_start_line')) {
                    $table->unsignedTinyInteger('mushaf_start_line')->nullable()->after('mushaf_page_number');
                }
                if (! Schema::hasColumn('memorization_targets', 'mushaf_end_line')) {
                    $table->unsignedTinyInteger('mushaf_end_line')->nullable()->after('mushaf_start_line');
                }
                if (! Schema::hasColumn('memorization_targets', 'start_word_location')) {
                    $table->string('start_word_location', 40)->nullable()->after('mushaf_end_line');
                }
                if (! Schema::hasColumn('memorization_targets', 'end_word_location')) {
                    $table->string('end_word_location', 40)->nullable()->after('start_word_location');
                }
            });
        }

    }

    public function down(): void
    {

        if (Schema::hasTable('memorization_targets')) {
            Schema::table('memorization_targets', function (Blueprint $table): void {
                foreach (['end_word_location','start_word_location','mushaf_end_line','mushaf_start_line','mushaf_page_number'] as $column) {
                    if (Schema::hasColumn('memorization_targets', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('quran_journey_portions')) {
            Schema::table('quran_journey_portions', function (Blueprint $table): void {
                foreach ([
                    'selection_source','line_block_key','end_word_location','start_word_location',
                    'end_line_number','start_line_number','mushaf_layout_code',
                ] as $column) {
                    if (Schema::hasColumn('quran_journey_portions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('quran_mushaf_lines');
    }
};
