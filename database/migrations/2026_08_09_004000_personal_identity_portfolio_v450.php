<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** @phase 4.5 Personal 2.0 — additive identity and safeguarding fields */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('personal_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('personal_profiles', 'age_group')) {
                $table->string('age_group', 30)->nullable();
            }
            if (! Schema::hasColumn('personal_profiles', 'interests')) {
                $table->json('interests')->nullable();
            }
            if (! Schema::hasColumn('personal_profiles', 'aspiration')) {
                $table->string('aspiration', 150)->nullable();
            }
            if (! Schema::hasColumn('personal_profiles', 'quranic_purpose')) {
                $table->text('quranic_purpose')->nullable();
            }
            if (! Schema::hasColumn('personal_profiles', 'learning_mode')) {
                $table->string('learning_mode', 30)->default('self');
            }
            if (! Schema::hasColumn('personal_profiles', 'safeguarding_acknowledged_at')) {
                $table->timestamp('safeguarding_acknowledged_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('personal_profiles', function (Blueprint $table): void {
            foreach (['age_group', 'interests', 'aspiration', 'quranic_purpose', 'learning_mode', 'safeguarding_acknowledged_at'] as $column) {
                if (Schema::hasColumn('personal_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
