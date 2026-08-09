<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('personal_check_ins')) {
            Schema::create('personal_check_ins', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('personal_profile_id')->constrained('personal_profiles')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('check_in_on');
                $table->string('energy', 24);
                $table->string('focus', 40);
                $table->string('intention', 190)->nullable();
                $table->text('reflection')->nullable();
                $table->timestamps();
                $table->unique(['personal_profile_id', 'check_in_on'], 'personal_checkin_profile_date_uq');
                $table->index(['institution_id', 'user_id', 'check_in_on'], 'personal_checkin_user_date_idx');
            });
        }

        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table): void {
                if (! Schema::hasColumn('payment_transactions', 'verified_by_user_id')) {
                    $table->foreignId('verified_by_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('payment_transactions', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('paid_at');
                }
                if (! Schema::hasColumn('payment_transactions', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('verified_at');
                }
            });
        }

        if (Schema::hasTable('feature_flags') && Schema::hasTable('institutions')) {
            $now = now();
            foreach (DB::table('institutions')->pluck('id') as $institutionId) {
                foreach (['community', 'payments'] as $featureKey) {
                    DB::table('feature_flags')->insertOrIgnore([
                        'institution_id' => $institutionId, 'feature_key' => $featureKey,
                        'enabled' => false, 'updated_at' => $now, 'created_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_transactions')) {
            Schema::table('payment_transactions', function (Blueprint $table): void {
                if (Schema::hasColumn('payment_transactions', 'verified_by_user_id')) {
                    $table->dropForeign(['verified_by_user_id']);
                }
                $columns = array_values(array_filter(
                    ['verified_by_user_id', 'verified_at', 'rejection_reason'],
                    fn (string $column): bool => Schema::hasColumn('payment_transactions', $column),
                ));
                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        }

        Schema::dropIfExists('personal_check_ins');
    }
};
