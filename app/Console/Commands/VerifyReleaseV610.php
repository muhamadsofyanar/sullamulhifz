<?php

namespace App\Console\Commands;

use App\Models\BackupRun;
use App\Models\InfaqAllocationPolicy;
use App\Models\InfaqEvidence;
use App\Models\InfaqLedgerEntry;
use App\Models\InfaqMonthlyReport;
use App\Models\InfaqRealisation;
use App\Services\InfaqReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class VerifyReleaseV610 extends Command
{
    protected $signature = 'sullam:verify-release-v610 {--require-backup : Gagal bila belum ada backup sukses dalam 36 jam}';
    protected $description = 'Memeriksa gerbang aplikasi Sullamul Hifz v6.1.0';

    public function handle(): int
    {
        $failures = [];
        $tables = ['infaq_allocation_policies', 'infaq_allocations', 'infaq_ledger_entries', 'infaq_realisations', 'infaq_evidences', 'infaq_fund_transfers', 'infaq_monthly_reports', 'backup_runs', 'restore_requests'];
        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                $failures[] = "Tabel {$table} belum tersedia";
            }
        }
        foreach (['admin.infaq.realisations.index', 'admin.infaq.policy.edit', 'admin.infaq.reports.index', 'public.infaq.show', 'admin.recovery.index'] as $route) {
            if (! Route::has($route)) {
                $failures[] = "Rute {$route} belum tersedia";
            }
        }
        foreach (['infaq.verify', 'infaq.policy.manage', 'infaq.realisation.manage', 'infaq.realisation.approve', 'infaq.audit.view', 'infaq.report.manage', 'backup.manage'] as $permission) {
            if (! DB::table('permissions')->where('name', $permission)->exists()) {
                $failures[] = "Izin {$permission} belum tersedia";
            }
        }
        if (Schema::hasTable('infaq_allocation_policies')) {
            foreach (InfaqAllocationPolicy::with('items')->where('status', 'active')->get() as $policy) {
                if ((int) $policy->items->sum('basis_points') !== 10000) {
                    $failures[] = "Kebijakan {$policy->id} tidak berjumlah 100%";
                }
            }
        }
        if (Schema::hasTable('infaq_ledger_entries')) {
            $negative = InfaqLedgerEntry::selectRaw('institution_id, category, SUM(amount) balance')->groupBy('institution_id', 'category')->havingRaw('SUM(amount) < 0')->exists();
            if ($negative) {
                $failures[] = 'Terdapat saldo kategori negatif';
            }
            $unbalancedTransfer = InfaqLedgerEntry::query()->whereNotNull('fund_transfer_id')
                ->groupBy('fund_transfer_id')->havingRaw('SUM(amount) <> 0')->exists();
            if ($unbalancedTransfer) {
                $failures[] = 'Terdapat jurnal pemindahan kategori yang tidak seimbang';
            }
        }
        if (Schema::hasTable('infaq_evidences')) {
            $unsafe = InfaqEvidence::whereHas('originalAsset', fn ($query) => $query->where('visibility', 'public'))->exists();
            if ($unsafe) {
                $failures[] = 'Terdapat bukti asli dengan visibility public';
            }
        }
        if (Schema::hasTable('infaq_realisations')) {
            $sameMakerChecker = InfaqRealisation::query()->where('status', 'verified')->whereColumn('created_by_user_id', 'reviewed_by_user_id')->exists();
            if ($sameMakerChecker) {
                $failures[] = 'Terdapat realisasi dengan pencatat dan pemeriksa yang sama';
            }
            $missingEvidence = InfaqRealisation::query()->where('status', 'verified')->whereDoesntHave('evidences', fn ($query) => $query
                ->whereNotNull('original_media_asset_id')->whereNotNull('public_media_asset_id')->where('public_review_status', 'approved'))->exists();
            if ($missingEvidence) {
                $failures[] = 'Terdapat realisasi terverifikasi tanpa bukti privat dan publik yang disetujui';
            }
        }
        if (Schema::hasTable('infaq_monthly_reports')) {
            foreach (InfaqMonthlyReport::query()->get() as $report) {
                if (! is_array($report->snapshot) || ! hash_equals($report->checksum, InfaqReportService::checksum($report->snapshot))) {
                    $failures[] = "Checksum laporan {$report->period} tidak cocok";
                }
            }
        }
        if ($this->option('require-backup') && (! Schema::hasTable('backup_runs') || ! BackupRun::where('status', 'completed')->where('completed_at', '>=', now()->subHours(36))->exists())) {
            $failures[] = 'Belum ada backup sukses dalam 36 jam terakhir';
        }

        if ($failures !== []) {
            foreach ($failures as $failure) {
                $this->error($failure);
            }
            $this->error('v6.1.0 NO-GO. Perbaiki seluruh temuan sebelum pilot/deploy.');
            return self::FAILURE;
        }
        $this->info('v6.1.0 lulus pemeriksaan aplikasi. Lanjutkan migration drill, restore drill, smoke test peran, dan audit UI ponsel.');
        return self::SUCCESS;
    }
}
