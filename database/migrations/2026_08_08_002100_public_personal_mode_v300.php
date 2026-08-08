<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table): void {
            $table->string('workspace_type', 30)->default('institution')->after('slug')->index();
            $table->foreignId('owner_user_id')->nullable()->after('workspace_type')->constrained('users')->nullOnDelete();
            $table->string('privacy_mode', 30)->default('institution')->after('owner_user_id');
        });

        Schema::create('personal_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('experience_level', 40)->nullable();
            $table->string('primary_focus', 40)->nullable();
            $table->unsignedSmallInteger('daily_minutes')->default(20);
            $table->unsignedTinyInteger('target_juz')->nullable();
            $table->unsignedSmallInteger('target_surah_id')->nullable();
            $table->date('target_date')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamp('privacy_acknowledged_at')->nullable();
            $table->timestamps();
            $table->unique('user_id');
            $table->unique('student_id');
            $table->unique(['institution_id', 'user_id'], 'personal_profile_workspace_user_uq');
            $table->foreign('target_surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
        });

        Schema::create('personal_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personal_profile_id')->constrained('personal_profiles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 190);
            $table->string('metric', 40);
            $table->unsignedInteger('target_value');
            $table->unsignedInteger('progress_value')->default(0);
            $table->date('starts_on');
            $table->date('due_on')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'user_id', 'status'], 'personal_goal_user_status_idx');
        });

        Schema::create('personal_practice_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personal_profile_id')->constrained('personal_profiles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity_type', 30);
            $table->unsignedSmallInteger('surah_id')->nullable();
            $table->unsignedSmallInteger('start_verse')->nullable();
            $table->unsignedSmallInteger('end_verse')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(0);
            $table->string('self_rating', 30)->nullable();
            $table->text('notes')->nullable();
            $table->date('practiced_on');
            $table->timestamps();
            $table->foreign('surah_id')->references('id')->on('quran_surahs')->nullOnDelete();
            $table->index(['institution_id', 'user_id', 'practiced_on'], 'personal_practice_user_date_idx');
            $table->index(['personal_profile_id', 'activity_type', 'practiced_on'], 'personal_practice_type_date_idx');
        });

        $roleId = DB::table('roles')->where('name', 'personal')->value('id');
        if (! $roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'personal',
                'display_name' => 'Pengguna Personal',
                'description' => 'Pengguna mandiri dengan ruang belajar privat.',
                'scope' => 'personal',
            ]);
        }

        $permissionCatalog = [
            'dashboard.view' => ['Melihat dashboard', 'Membuka dashboard sesuai peran akun.'],
            'learning.view' => ['Melihat catatan pembelajaran', 'Melihat catatan pembelajaran sesuai lingkup akses.'],
            'personal.use' => ['Menggunakan ruang Personal', 'Mengelola perjalanan Al-Qur’an pribadi milik akun sendiri.'],
        ];
        foreach ($permissionCatalog as $name => [$displayName, $description]) {
            if (! DB::table('permissions')->where('name', $name)->exists()) {
                DB::table('permissions')->insert([
                    'name' => $name,
                    'display_name' => $displayName,
                    'description' => $description,
                ]);
            }
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', ['dashboard.view', 'learning.view', 'personal.use'])
            ->pluck('id');
        foreach ($permissionIds as $permissionId) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_practice_entries');
        Schema::dropIfExists('personal_goals');
        Schema::dropIfExists('personal_profiles');

        Schema::table('institutions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('owner_user_id');
            $table->dropColumn(['workspace_type', 'privacy_mode']);
        });
    }
};
