<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('memorization_targets') && ! Schema::hasColumn('memorization_targets', 'mushaf_end_page_number')) {
            Schema::table('memorization_targets', function (Blueprint $table): void {
                $table->unsignedSmallInteger('mushaf_end_page_number')->nullable()->after('mushaf_page_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('memorization_targets') && Schema::hasColumn('memorization_targets', 'mushaf_end_page_number')) {
            Schema::table('memorization_targets', function (Blueprint $table): void {
                $table->dropColumn('mushaf_end_page_number');
            });
        }
    }
};
