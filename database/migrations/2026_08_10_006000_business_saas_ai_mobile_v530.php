<?php

/** @phase 5.0 Business, Payment & Integrations; @phase 5.1 SaaS Production Readiness; @phase 5.2 Smart Assistant; @phase 5.3 Mobile, Offline & Global */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('billing_plans')) {
            Schema::create('billing_plans', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('code', 80)->unique();
                $table->string('audience', 30)->default('personal');
                $table->string('name', 160);
                $table->text('description')->nullable();
                $table->string('billing_cycle', 24)->default('monthly');
                $table->decimal('price', 14, 2)->default(0);
                $table->char('currency', 3)->default('IDR');
                $table->json('entitlements')->nullable();
                $table->string('status', 24)->default('active');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['institution_id', 'audience', 'status'], 'billing_plans_listing_idx');
            });
        }

        if (! Schema::hasTable('billing_subscriptions')) {
            Schema::create('billing_subscriptions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('billing_plan_id')->constrained('billing_plans')->restrictOnDelete();
                $table->string('scope_type', 30)->default('user');
                $table->string('status', 24)->default('pending');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('auto_renew')->default(false);
                $table->json('entitlement_snapshot')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'status', 'ends_at'], 'billing_subscriptions_status_idx');
                $table->index(['user_id', 'status'], 'billing_subscriptions_user_idx');
            });
        }

        if (! Schema::hasTable('billing_invoices')) {
            Schema::create('billing_invoices', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('billing_plan_id')->nullable()->constrained('billing_plans')->nullOnDelete();
                $table->foreignId('billing_subscription_id')->nullable()->constrained('billing_subscriptions')->nullOnDelete();
                $table->string('invoice_number', 80)->unique();
                $table->string('purpose', 100)->default('subscription');
                $table->decimal('subtotal', 14, 2);
                $table->decimal('total', 14, 2);
                $table->char('currency', 3)->default('IDR');
                $table->string('status', 24)->default('pending');
                $table->timestamp('due_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['institution_id', 'status', 'due_at'], 'billing_invoices_status_idx');
                $table->index(['user_id', 'created_at'], 'billing_invoices_user_idx');
            });
        }

        if (Schema::hasTable('payment_transactions') && ! Schema::hasColumn('payment_transactions', 'billing_invoice_id')) {
            Schema::table('payment_transactions', function (Blueprint $table): void {
                $table->foreignId('billing_invoice_id')->nullable()->after('user_id')->constrained('billing_invoices')->nullOnDelete();
                $table->index(['billing_invoice_id', 'status'], 'payment_invoice_status_idx');
            });
        }

        if (! Schema::hasTable('operational_check_runs')) {
            Schema::create('operational_check_runs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('institution_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('check_key', 100);
                $table->string('status', 24);
                $table->text('message')->nullable();
                $table->json('metrics')->nullable();
                $table->timestamp('checked_at');
                $table->timestamps();
                $table->index(['institution_id', 'status', 'checked_at'], 'ops_checks_scope_idx');
                $table->index(['check_key', 'checked_at'], 'ops_checks_key_idx');
            });
        }

        if (! Schema::hasTable('user_preferences')) {
            Schema::create('user_preferences', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('locale', 10)->default('id');
                $table->string('timezone', 64)->default('Asia/Jakarta');
                $table->boolean('pwa_enabled')->default(true);
                $table->json('notification_preferences')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('feature_flags') && Schema::hasTable('institutions')) {
            $now = now();
            foreach (DB::table('institutions')->pluck('id') as $institutionId) {
                foreach (['business_center', 'smart_assistant', 'user_preferences'] as $featureKey) {
                    DB::table('feature_flags')->insertOrIgnore([
                        'institution_id' => $institutionId,
                        'feature_key' => $featureKey,
                        'enabled' => true,
                        'config' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        $this->seedDefaultPlans();
    }

    private function seedDefaultPlans(): void
    {
        if (! Schema::hasTable('billing_plans')) {
            return;
        }

        $now = now();
        $plans = [
            [
                'code' => 'personal-free',
                'audience' => 'personal',
                'name' => 'Personal Gratis',
                'description' => 'Fondasi belajar mandiri, target, jurnal, dan Ruang Belajar.',
                'billing_cycle' => 'monthly',
                'price' => 0,
                'status' => 'active',
                'entitlements' => json_encode(['personal_core', 'learning_hub', 'quran_practice']),
                'sort_order' => 10,
            ],
            [
                'code' => 'personal-guided',
                'audience' => 'personal',
                'name' => 'Personal Terbimbing',
                'description' => 'Personal dengan akses program terbimbing dan dukungan ekosistem.',
                'billing_cycle' => 'monthly',
                'price' => 0,
                'status' => 'inactive',
                'entitlements' => json_encode(['personal_core', 'learning_hub', 'guided_learning', 'academy', 'smart_assistant']),
                'sort_order' => 20,
            ],
            [
                'code' => 'ustadz-pro',
                'audience' => 'teacher',
                'name' => 'Ustadz Pro',
                'description' => 'Bimbingan privat, review setoran, dan alat pendampingan.',
                'billing_cycle' => 'monthly',
                'price' => 0,
                'status' => 'inactive',
                'entitlements' => json_encode(['mentorship', 'guided_review', 'smart_assistant_review']),
                'sort_order' => 30,
            ],
            [
                'code' => 'institution-core',
                'audience' => 'institution',
                'name' => 'Lembaga Core',
                'description' => 'Workspace lembaga, anggota, pembelajaran, laporan, dan integrasi operasional.',
                'billing_cycle' => 'monthly',
                'price' => 0,
                'status' => 'inactive',
                'entitlements' => json_encode(['institution_suite', 'academy', 'communications', 'reports', 'operations']),
                'sort_order' => 40,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('billing_plans')->updateOrInsert(
                ['code' => $plan['code']],
                $plan + [
                    'institution_id' => null,
                    'currency' => 'IDR',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_transactions') && Schema::hasColumn('payment_transactions', 'billing_invoice_id')) {
            Schema::table('payment_transactions', function (Blueprint $table): void {
                $table->dropForeign(['billing_invoice_id']);
                $table->dropIndex('payment_invoice_status_idx');
                $table->dropColumn('billing_invoice_id');
            });
        }

        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('operational_check_runs');
        Schema::dropIfExists('billing_invoices');
        Schema::dropIfExists('billing_subscriptions');
        Schema::dropIfExists('billing_plans');
    }
};
