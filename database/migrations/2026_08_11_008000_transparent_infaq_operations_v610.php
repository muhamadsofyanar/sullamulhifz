<?php

/** @phase 6.1 Transparent infaq, production operations, and pilot controls */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('infaq_transactions', function (Blueprint $table): void {
            $table->foreignId('transfer_proof_media_asset_id')->nullable()->after('is_anonymous')->constrained('media_assets')->nullOnDelete();
            $table->boolean('show_donor_name')->default(false)->after('is_anonymous');
            $table->timestamp('donor_consent_at')->nullable()->after('show_donor_name');
            $table->text('mutation_match_note')->nullable()->after('paid_at');
            $table->text('rejection_reason')->nullable()->after('mutation_match_note');
        });

        Schema::create('infaq_allocation_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->timestamp('effective_from');
            $table->string('status', 20)->default('active');
            $table->text('change_reason');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['institution_id', 'version'], 'infaq_policy_scope_version_uq');
            $table->index(['institution_id', 'status', 'effective_from'], 'infaq_policy_active_idx');
        });

        Schema::create('infaq_allocation_policy_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('policy_id')->constrained('infaq_allocation_policies')->cascadeOnDelete();
            $table->string('category', 40);
            $table->unsignedSmallInteger('basis_points');
            $table->timestamps();
            $table->unique(['policy_id', 'category'], 'infaq_policy_item_uq');
        });

        Schema::create('infaq_receipt_sequences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['institution_id', 'year'], 'infaq_receipt_scope_year_uq');
        });

        Schema::create('infaq_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('infaq_transaction_id')->constrained('infaq_transactions')->restrictOnDelete();
            $table->foreignId('policy_id')->nullable()->constrained('infaq_allocation_policies')->restrictOnDelete();
            $table->string('category', 40);
            $table->unsignedSmallInteger('basis_points');
            $table->decimal('amount', 14, 2);
            $table->string('source', 30)->default('verified_transaction');
            $table->timestamps();
            $table->unique(['infaq_transaction_id', 'category'], 'infaq_allocation_transaction_category_uq');
            $table->index(['institution_id', 'category'], 'infaq_allocation_balance_idx');
        });

        Schema::create('infaq_realisations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->string('program_name', 190);
            $table->text('purpose');
            $table->decimal('amount', 14, 2);
            $table->unsignedInteger('beneficiary_count')->default(0);
            $table->text('impact_summary')->nullable();
            $table->date('realised_on');
            $table->string('status', 24)->default('draft');
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'status', 'realised_on'], 'infaq_realisation_scope_status_idx');
        });

        Schema::create('infaq_evidences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('realisation_id')->constrained('infaq_realisations')->cascadeOnDelete();
            $table->string('evidence_type', 30);
            $table->foreignId('original_media_asset_id')->constrained('media_assets')->restrictOnDelete();
            $table->foreignId('public_media_asset_id')->nullable()->constrained('media_assets')->restrictOnDelete();
            $table->string('public_review_status', 24)->default('pending');
            $table->foreignId('public_reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('public_reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'public_review_status'], 'infaq_evidence_public_idx');
        });

        Schema::create('infaq_fund_transfers', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('from_category', 40);
            $table->string('to_category', 40);
            $table->decimal('amount', 14, 2);
            $table->text('reason');
            $table->string('status', 24)->default('submitted');
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'status', 'created_at'], 'infaq_transfer_scope_status_idx');
        });

        Schema::create('infaq_ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('entry_uuid')->unique();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('infaq_transaction_id')->nullable()->constrained('infaq_transactions')->restrictOnDelete();
            $table->foreignId('allocation_id')->nullable()->constrained('infaq_allocations')->restrictOnDelete();
            $table->foreignId('realisation_id')->nullable()->constrained('infaq_realisations')->restrictOnDelete();
            $table->foreignId('fund_transfer_id')->nullable()->constrained('infaq_fund_transfers')->restrictOnDelete();
            $table->string('entry_type', 30);
            $table->string('category', 40);
            $table->decimal('amount', 14, 2);
            $table->timestamp('occurred_at');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'category', 'occurred_at'], 'infaq_ledger_balance_idx');
            $table->unique(['allocation_id', 'entry_type'], 'infaq_ledger_allocation_type_uq');
            $table->unique(['realisation_id', 'entry_type'], 'infaq_ledger_realisation_type_uq');
        });

        Schema::create('infaq_monthly_reports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->char('period', 7);
            $table->json('snapshot');
            $table->string('status', 20)->default('locked');
            $table->foreignId('locked_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('locked_at');
            $table->string('checksum', 64);
            $table->timestamps();
            $table->unique(['institution_id', 'period'], 'infaq_report_scope_period_uq');
        });

        Schema::create('backup_runs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('backup_type', 24);
            $table->string('status', 24);
            $table->string('storage_reference', 500);
            $table->string('checksum', 64);
            $table->unsignedBigInteger('size_bytes');
            $table->string('retention_tier', 20);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('manifest')->nullable();
            $table->text('failure_reason')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'completed_at'], 'backup_run_status_idx');
        });

        Schema::create('restore_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('backup_run_id')->constrained('backup_runs')->restrictOnDelete();
            $table->text('reason');
            $table->string('status', 30)->default('requested');
            $table->foreignId('requested_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('simulation_result')->nullable();
            $table->timestamp('simulation_completed_at')->nullable();
            $table->text('operator_note')->nullable();
            $table->timestamps();
        });

        $permissions = [
            'infaq.view_own' => 'Melihat infak dan bukti penerimaan sendiri',
            'infaq.verify' => 'Memverifikasi penerimaan infak',
            'infaq.policy.manage' => 'Mengelola kebijakan alokasi infak',
            'infaq.realisation.manage' => 'Mencatat realisasi penggunaan infak',
            'infaq.realisation.approve' => 'Menyetujui realisasi dan transfer dana',
            'infaq.audit.view' => 'Membaca transaksi, bukti asli, dan audit infak',
            'infaq.report.manage' => 'Mengunci laporan bulanan infak',
        ];
        foreach ($permissions as $name => $displayName) {
            DB::table('permissions')->updateOrInsert(['name' => $name], ['display_name' => $displayName, 'description' => $displayName]);
        }
        DB::table('permissions')->updateOrInsert(['name' => 'backup.manage'], [
            'display_name' => 'Mengelola backup dan simulasi pemulihan',
            'description' => 'Mencatat backup serta mengelola persetujuan dan simulasi pemulihan data',
        ]);
        DB::table('roles')->updateOrInsert(['name' => 'auditor'], [
            'display_name' => 'Auditor', 'description' => 'Akses baca audit tanpa hak mengubah data', 'scope' => 'institution',
        ]);
        $roleMap = [
            'superadmin' => [...array_keys($permissions), 'backup.manage'],
            'institution_admin' => ['infaq.view_own', 'infaq.verify', 'infaq.policy.manage', 'infaq.realisation.manage', 'infaq.audit.view', 'infaq.report.manage'],
            'head' => ['infaq.view_own', 'infaq.realisation.approve', 'infaq.audit.view', 'infaq.report.manage'],
            'auditor' => ['infaq.audit.view'],
        ];
        foreach ($roleMap as $roleName => $permissionNames) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if (! $roleId) {
                continue;
            }
            foreach (DB::table('permissions')->whereIn('name', $permissionNames)->pluck('id') as $permissionId) {
                DB::table('role_permissions')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }
        }
        $this->backfillVerifiedInfaq();
        DB::table('feature_flags')->updateOrInsert(
            ['institution_id' => null, 'feature_key' => 'v610_pilot'],
            ['enabled' => false, 'config' => json_encode(['release' => 'v6.1.0']), 'updated_at' => now(), 'created_at' => now()],
        );
    }

    private function backfillVerifiedInfaq(): void
    {
        $defaults = ['teacher_development' => 4000, 'foundation_operations' => 3000, 'technology' => 2000, 'scholarship' => 1000];
        $policyIds = [];
        DB::table('infaq_transactions')->where('status', 'verified')->whereNotNull('institution_id')->orderBy('id')->chunkById(200, function ($transactions) use ($defaults, &$policyIds): void {
            foreach ($transactions as $transaction) {
                $items = [$transaction->purpose => 10000];
                $policyId = null;
                if ($transaction->purpose === 'general') {
                    $institutionId = (int) $transaction->institution_id;
                    if (! isset($policyIds[$institutionId])) {
                        $policyId = DB::table('infaq_allocation_policies')->insertGetId([
                            'institution_id' => $institutionId, 'version' => 1, 'effective_from' => now(),
                            'status' => 'active', 'change_reason' => 'Saldo pembuka migrasi transaksi v6.0.0',
                            'created_by_user_id' => null, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                        foreach ($defaults as $category => $points) {
                            DB::table('infaq_allocation_policy_items')->insert([
                                'policy_id' => $policyId, 'category' => $category, 'basis_points' => $points,
                                'created_at' => now(), 'updated_at' => now(),
                            ]);
                        }
                        $policyIds[$institutionId] = $policyId;
                    }
                    $policyId = $policyIds[$institutionId];
                    $items = $defaults;
                }
                $totalCents = (int) round((float) $transaction->amount * 100);
                $allocated = 0;
                $last = array_key_last($items);
                foreach ($items as $category => $points) {
                    $cents = $category === $last ? $totalCents - $allocated : intdiv($totalCents * $points, 10000);
                    $allocated += $cents;
                    $allocationId = DB::table('infaq_allocations')->insertGetId([
                        'institution_id' => $transaction->institution_id, 'infaq_transaction_id' => $transaction->id,
                        'policy_id' => $policyId, 'category' => $category, 'basis_points' => $points,
                        'amount' => $cents / 100, 'source' => 'historical_v600_migration',
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    DB::table('infaq_ledger_entries')->insert([
                        'entry_uuid' => (string) Str::uuid(), 'institution_id' => $transaction->institution_id,
                        'infaq_transaction_id' => $transaction->id, 'allocation_id' => $allocationId,
                        'entry_type' => 'receipt_credit', 'category' => $category, 'amount' => $cents / 100,
                        'occurred_at' => $transaction->verified_at ?: $transaction->paid_at ?: $transaction->created_at,
                        'created_by_user_id' => $transaction->verified_by_user_id,
                        'metadata' => json_encode(['source' => 'v6.0.0_backfill']),
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }, 'id');
    }

    public function down(): void
    {
        foreach (['restore_requests', 'backup_runs', 'infaq_monthly_reports', 'infaq_ledger_entries', 'infaq_fund_transfers', 'infaq_evidences', 'infaq_realisations', 'infaq_allocations', 'infaq_receipt_sequences', 'infaq_allocation_policy_items', 'infaq_allocation_policies'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('infaq_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('transfer_proof_media_asset_id');
            $table->dropColumn(['show_donor_name', 'donor_consent_at', 'mutation_match_note', 'rejection_reason']);
        });
        DB::table('feature_flags')->where('feature_key', 'v610_pilot')->delete();
        DB::table('permissions')->whereIn('name', ['infaq.view_own', 'infaq.verify', 'infaq.policy.manage', 'infaq.realisation.manage', 'infaq.realisation.approve', 'infaq.audit.view', 'infaq.report.manage'])->delete();
        DB::table('roles')->where('name', 'auditor')->delete();
    }
};
