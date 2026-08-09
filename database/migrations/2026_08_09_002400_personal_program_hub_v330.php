<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('personal_module_enrollments')) {
            Schema::create('personal_module_enrollments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('personal_profile_id')->constrained('personal_profiles')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('module_key', 60);
                $table->string('status', 30)->default('active');
                $table->string('enrollment_source', 40)->default('self');
                $table->timestamp('enrolled_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['personal_profile_id', 'module_key'], 'personal_module_profile_key_uq');
                $table->index(['institution_id', 'user_id', 'status'], 'personal_module_user_status_idx');
            });
        }

        $now = now();
        foreach (DB::table('personal_profiles')->select(['id', 'institution_id', 'user_id'])->orderBy('id')->get() as $profile) {
            $keys = [];

            if (Schema::hasTable('guided_quran_enrollments') && DB::table('guided_quran_enrollments')
                ->where('learner_institution_id', $profile->institution_id)
                ->where('learner_user_id', $profile->user_id)
                ->where('status', 'active')->exists()) {
                $keys[] = 'guided_learning';
            }

            if (Schema::hasTable('quran_program_enrollments') && DB::table('quran_program_enrollments')
                ->where('institution_id', $profile->institution_id)
                ->where('user_id', $profile->user_id)
                ->where('status', 'active')->exists()) {
                $keys[] = 'quran_journey';
            }

            if (Schema::hasTable('quran_practice_sessions') && DB::table('quran_practice_sessions')
                ->where('institution_id', $profile->institution_id)
                ->where('user_id', $profile->user_id)->exists()) {
                $keys[] = 'quran_practice';
            }

            foreach (array_unique($keys) as $key) {
                DB::table('personal_module_enrollments')->updateOrInsert(
                    ['personal_profile_id' => $profile->id, 'module_key' => $key],
                    [
                        'institution_id' => $profile->institution_id,
                        'user_id' => $profile->user_id,
                        'status' => 'active',
                        'enrollment_source' => 'migration_existing_activity',
                        'enrolled_at' => $now,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }

            if (Schema::hasTable('feature_flags')) {
                DB::table('feature_flags')->updateOrInsert(
                    ['institution_id' => $profile->institution_id, 'feature_key' => 'quran_journey'],
                    ['enabled' => true, 'updated_at' => $now, 'created_at' => $now],
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_module_enrollments');
    }
};
