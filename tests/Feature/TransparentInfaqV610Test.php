<?php

namespace Tests\Feature;

use App\Models\InfaqLedgerEntry;
use App\Models\InfaqFundTransfer;
use App\Models\InfaqMonthlyReport;
use App\Models\Institution;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\BackupManifestService;
use App\Services\InfaqAllocationService;
use App\Services\InfaqRealisationService;
use App\Services\InfaqService;
use App\Services\InfaqLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TransparentInfaqV610Test extends TestCase
{
    use RefreshDatabase;

    public function test_v610_schema_routes_and_permissions_are_additive(): void
    {
        foreach (['infaq_allocation_policies', 'infaq_allocation_policy_items', 'infaq_receipt_sequences', 'infaq_allocations', 'infaq_ledger_entries', 'infaq_realisations', 'infaq_evidences', 'infaq_fund_transfers', 'infaq_monthly_reports', 'backup_runs', 'restore_requests'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} belum tersedia.");
        }
        foreach (['show_donor_name', 'transfer_proof_media_asset_id', 'mutation_match_note', 'rejection_reason'] as $column) {
            $this->assertTrue(Schema::hasColumn('infaq_transactions', $column));
        }
        foreach (['admin.infaq.realisations.index', 'admin.infaq.policy.edit', 'admin.infaq.reports.index', 'public.infaq.show', 'admin.recovery.index'] as $name) {
            $this->assertTrue(Route::has($name), "Rute {$name} belum tersedia.");
        }
        $this->assertDatabaseHas('feature_flags', ['feature_key' => 'v610_pilot', 'enabled' => false]);
        $this->assertDatabaseHas('permissions', ['name' => 'infaq.realisation.approve']);
    }

    public function test_general_infaq_uses_exact_default_snapshot_and_special_infaq_stays_restricted(): void
    {
        [$institution, $donor, $reviewer] = $this->actors('allocation');
        $service = app(InfaqService::class);
        $general = $service->createPending($donor, ['purpose' => 'general', 'amount' => 100000, 'show_donor_name' => false], (string) Str::uuid());
        $service->verify($general, $reviewer, 'verified', 'Mutasi BSI cocok 11 Agustus 2026.');

        $this->assertDatabaseHas('infaq_allocations', ['infaq_transaction_id' => $general->id, 'category' => 'teacher_development', 'basis_points' => 4000, 'amount' => 40000]);
        $this->assertDatabaseHas('infaq_allocations', ['infaq_transaction_id' => $general->id, 'category' => 'foundation_operations', 'basis_points' => 3000, 'amount' => 30000]);
        $this->assertDatabaseHas('infaq_allocations', ['infaq_transaction_id' => $general->id, 'category' => 'technology', 'basis_points' => 2000, 'amount' => 20000]);
        $this->assertDatabaseHas('infaq_allocations', ['infaq_transaction_id' => $general->id, 'category' => 'scholarship', 'basis_points' => 1000, 'amount' => 10000]);
        $this->assertSame(100000.0, (float) InfaqLedgerEntry::where('institution_id', $institution->id)->sum('amount'));
        $this->assertMatchesRegularExpression('/^INF-'.$institution->id.'-\d{4}-\d{6}$/', $general->fresh()->receipt_number);

        $special = $service->createPending($donor, ['purpose' => 'technology', 'amount' => 75000, 'show_donor_name' => true], (string) Str::uuid());
        $service->verify($special, $reviewer, 'verified', 'Mutasi tujuan teknologi cocok.');
        $this->assertCount(1, $special->fresh()->allocations);
        $this->assertSame('technology', $special->fresh()->allocations->first()->category);
        $this->assertSame('75000.00', $special->fresh()->allocations->first()->amount);
    }

    public function test_policy_changes_are_prospective_and_require_exactly_one_hundred_percent(): void
    {
        [, $actor] = $this->actors('policy');
        $policies = app(InfaqAllocationService::class);
        $first = $policies->activePolicy((int) $actor->institution_id);
        $second = $policies->replacePolicy($actor, ['teacher_development' => 2500, 'foundation_operations' => 2500, 'technology' => 2500, 'scholarship' => 2500], 'Pemerataan program pada periode pilot.');
        $this->assertSame(1, $first->version);
        $this->assertSame(2, $second->version);
        $this->assertSame('superseded', $first->fresh()->status);

        $this->expectException(ValidationException::class);
        $policies->replacePolicy($actor, ['teacher_development' => 4000, 'foundation_operations' => 3000, 'technology' => 2000, 'scholarship' => 999], 'Total salah untuk regression test.');
    }

    public function test_realisation_requires_a_different_reviewer_and_cannot_overdraw_category(): void
    {
        [, $donor, $reviewer] = $this->actors('maker-checker');
        $service = app(InfaqService::class);
        $transaction = $service->createPending($donor, ['purpose' => 'technology', 'amount' => 50000], (string) Str::uuid());
        $service->verify($transaction, $reviewer, 'verified', 'Mutasi cocok untuk saldo teknologi.');
        [$original, $public] = $this->evidence($donor);
        $realisation = app(InfaqRealisationService::class)->create($donor, [
            'category' => 'technology', 'program_name' => 'Server Agustus', 'purpose' => 'Biaya server',
            'amount' => 40000, 'beneficiary_count' => 120, 'impact_summary' => 'Aplikasi aktif untuk 120 pengguna.',
            'realised_on' => today()->toDateString(), 'evidence_type' => 'invoice',
        ], $original, $public);

        try {
            app(InfaqRealisationService::class)->review($realisation, $donor, 'verified', 'Bukti sudah disamarkan dan lengkap.');
            $this->fail('Maker seharusnya tidak dapat menjadi checker.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
        app(InfaqRealisationService::class)->review($realisation, $reviewer, 'verified', 'Faktur dan versi publik sudah diperiksa.');
        $this->assertDatabaseHas('infaq_ledger_entries', ['realisation_id' => $realisation->id, 'entry_type' => 'realisation_debit', 'amount' => -40000]);

        [$original2, $public2] = $this->evidence($donor, 'second');
        $tooLarge = app(InfaqRealisationService::class)->create($donor, [
            'category' => 'technology', 'program_name' => 'Perangkat', 'purpose' => 'Perangkat baru',
            'amount' => 20000, 'beneficiary_count' => 10, 'realised_on' => today()->toDateString(), 'evidence_type' => 'receipt',
        ], $original2, $public2);
        $this->expectException(ValidationException::class);
        app(InfaqRealisationService::class)->review($tooLarge, $reviewer, 'verified', 'Bukti sudah diperiksa tetapi saldo kurang.');
    }

    public function test_locked_report_and_ledger_are_immutable(): void
    {
        [$institution, $actor] = $this->actors('immutable');
        $report = InfaqMonthlyReport::create([
            'public_id' => (string) Str::uuid(), 'institution_id' => $institution->id, 'period' => '2026-08',
            'snapshot' => ['received' => '0'], 'status' => 'locked', 'locked_by_user_id' => $actor->id,
            'locked_at' => now(), 'checksum' => str_repeat('a', 64),
        ]);
        $this->expectException(LogicException::class);
        $report->update(['period' => '2026-07']);
    }

    public function test_category_transfer_is_balanced_and_a_terminal_decision_cannot_be_replayed(): void
    {
        [$institution, $maker, $checker] = $this->actors('transfer-terminal');
        $transaction = app(InfaqService::class)->createPending($maker, ['purpose' => 'technology', 'amount' => 100000], (string) Str::uuid());
        app(InfaqService::class)->verify($transaction, $checker, 'verified', 'Mutasi cocok untuk pengujian pemindahan.');
        $transfer = InfaqFundTransfer::create([
            'public_id' => (string) Str::uuid(), 'institution_id' => $institution->id,
            'from_category' => 'technology', 'to_category' => 'scholarship', 'amount' => 25000,
            'reason' => 'Kebutuhan beasiswa pilot yang telah disetujui.', 'status' => 'submitted',
            'created_by_user_id' => $maker->id,
        ]);

        app(InfaqLedgerService::class)->approveTransfer($transfer, $checker, 'Dokumen dan saldo sumber sudah diperiksa.');
        $this->assertSame(75000.0, (float) app(InfaqLedgerService::class)->balance($institution->id, 'technology'));
        $this->assertSame(25000.0, (float) app(InfaqLedgerService::class)->balance($institution->id, 'scholarship'));
        $this->assertSame(0.0, (float) InfaqLedgerEntry::where('fund_transfer_id', $transfer->id)->sum('amount'));

        $this->expectException(HttpException::class);
        app(InfaqLedgerService::class)->approveTransfer($transfer, $checker, 'Keputusan kedua harus ditolak.');
    }

    public function test_backup_manifest_uses_sha256_and_retention_tier(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'sullam-backup-');
        file_put_contents($path, 'encrypted-backup-fixture');
        $run = app(BackupManifestService::class)->record('database', $path);
        $this->assertSame(hash_file('sha256', $path), $run->checksum);
        $this->assertContains($run->retention_tier, ['daily', 'weekly', 'monthly']);
        $this->assertSame(['daily' => 14, 'weekly' => 8, 'monthly' => 12], $run->manifest['retention']);
        unlink($path);
    }

    /** @return array{0:Institution,1:User,2:User} */
    private function actors(string $suffix): array
    {
        $institution = Institution::create(['name' => 'Lembaga '.Str::headline($suffix), 'code' => 'V61-'.strtoupper(substr(sha1($suffix), 0, 8)), 'slug' => 'v61-'.$suffix, 'status' => 'active']);
        $maker = User::create(['institution_id' => $institution->id, 'name' => 'Maker '.$suffix, 'email' => 'maker-'.$suffix.'@example.test', 'password' => 'RahasiaAman123', 'status' => 'active']);
        $checker = User::create(['institution_id' => $institution->id, 'name' => 'Checker '.$suffix, 'email' => 'checker-'.$suffix.'@example.test', 'password' => 'RahasiaAman123', 'status' => 'active']);
        return [$institution, $maker, $checker];
    }

    /** @return array{0:MediaAsset,1:MediaAsset} */
    private function evidence(User $user, string $suffix = 'first'): array
    {
        $base = ['institution_id' => $user->institution_id, 'uploaded_by_user_id' => $user->id, 'disk' => 'local', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'file_size' => 100, 'checksum' => str_repeat('b', 64), 'visibility' => 'restricted', 'processing_status' => 'ready'];
        return [
            MediaAsset::create([...$base, 'directory' => 'test/original', 'file_name' => $suffix.'-original.pdf', 'original_name' => 'original.pdf']),
            MediaAsset::create([...$base, 'directory' => 'test/redacted', 'file_name' => $suffix.'-redacted.pdf', 'original_name' => 'redacted.pdf']),
        ];
    }
}
