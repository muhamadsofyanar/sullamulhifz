<?php

namespace App\Services;

/** @phase 5.1 SaaS Production Readiness */

use App\Models\OperationalCheckRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaasReadinessService
{
    public function checks(?int $institutionId = null): array
    {
        $checks = [];
        $requiredTables = [
            'institutions', 'workspace_memberships', 'user_relationships', 'activity_logs',
            'payment_transactions', 'billing_plans', 'billing_subscriptions', 'billing_invoices',
            'integration_connections', 'communication_deliveries', 'operational_check_runs',
        ];

        $missing = array_values(array_filter($requiredTables, fn (string $table): bool => ! Schema::hasTable($table)));
        $checks[] = $this->row('database_schema', $missing === [] ? 'pass' : 'fail', $missing === []
            ? 'Schema produksi inti tersedia.'
            : 'Tabel inti belum lengkap: '.implode(', ', $missing), ['missing_tables' => $missing]);

        $dbOk = true;
        try {
            DB::select('select 1');
        } catch (\Throwable $exception) {
            $dbOk = false;
        }
        $checks[] = $this->row('database_connection', $dbOk ? 'pass' : 'fail', $dbOk ? 'Koneksi database merespons.' : 'Koneksi database gagal.');

        $storageWritable = is_writable(storage_path()) && is_writable(storage_path('framework'));
        $checks[] = $this->row('storage_writable', $storageWritable ? 'pass' : 'fail', $storageWritable
            ? 'Storage aplikasi dapat ditulis.'
            : 'Storage aplikasi tidak dapat ditulis.');

        $membershipIssues = 0;
        if (Schema::hasTable('workspace_memberships') && Schema::hasTable('institutions')) {
            $membershipIssues = DB::table('workspace_memberships as wm')
                ->leftJoin('institutions as i', 'i.id', '=', 'wm.institution_id')
                ->where('wm.status', 'active')
                ->whereNull('i.id')
                ->count();
        }
        $checks[] = $this->row('tenant_membership_integrity', $membershipIssues === 0 ? 'pass' : 'fail',
            $membershipIssues === 0 ? 'Membership aktif terhubung ke workspace yang valid.' : "Ditemukan {$membershipIssues} membership tanpa workspace valid.",
            ['issues' => $membershipIssues]);

        $relationshipIssues = 0;
        if (Schema::hasTable('user_relationships')) {
            $relationshipIssues = DB::table('user_relationships')
                ->whereColumn('from_user_id', 'to_user_id')
                ->where('status', 'accepted')
                ->count();
        }
        $checks[] = $this->row('relationship_integrity', $relationshipIssues === 0 ? 'pass' : 'fail',
            $relationshipIssues === 0 ? 'Tidak ada hubungan aktif yang menunjuk akun yang sama.' : "Ditemukan {$relationshipIssues} hubungan tidak valid.",
            ['issues' => $relationshipIssues]);

        $queue = (string) config('queue.default');
        $checks[] = $this->row('queue_driver', $queue === 'sync' ? 'warning' : 'pass', $queue === 'sync'
            ? 'Queue masih sync; aman untuk fungsi dasar, tetapi worker terpisah direkomendasikan untuk skala produksi.'
            : "Queue driver aktif: {$queue}.", ['driver' => $queue]);

        $backupMarker = trim((string) env('SULLAM_BACKUP_VERIFIED_AT', ''));
        $restoreMarker = trim((string) env('SULLAM_RESTORE_DRILL_VERIFIED_AT', ''));
        $checks[] = $this->row('backup_restore_drill', ($backupMarker !== '' && $restoreMarker !== '') ? 'pass' : 'warning',
            ($backupMarker !== '' && $restoreMarker !== '')
                ? 'Backup dan restore drill memiliki marker verifikasi operator.'
                : 'Kode siap, tetapi backup/restore nyata belum diberi marker verifikasi operator.',
            ['backup_verified_at' => $backupMarker ?: null, 'restore_verified_at' => $restoreMarker ?: null]);

        $loadTest = trim((string) env('SULLAM_LOAD_TEST_VERIFIED_AT', ''));
        $checks[] = $this->row('load_test', $loadTest !== '' ? 'pass' : 'warning', $loadTest !== ''
            ? 'Uji beban memiliki marker verifikasi operator.'
            : 'Uji beban produksi belum diberi marker verifikasi operator.', ['verified_at' => $loadTest ?: null]);

        if ($institutionId && Schema::hasTable('integration_connections')) {
            $failedIntegrations = DB::table('integration_connections')
                ->where('institution_id', $institutionId)
                ->where('status', 'active')
                ->whereNotNull('last_error')
                ->count();
            $checks[] = $this->row('active_integrations', $failedIntegrations === 0 ? 'pass' : 'warning',
                $failedIntegrations === 0 ? 'Integrasi aktif tidak memiliki error tersimpan.' : "Ada {$failedIntegrations} integrasi aktif dengan error terakhir.",
                ['failed_active_integrations' => $failedIntegrations]);
        }

        return $checks;
    }

    public function persist(User $actor): array
    {
        $checks = $this->checks((int) $actor->institution_id);
        foreach ($checks as $check) {
            OperationalCheckRun::create([
                'institution_id' => $actor->institution_id,
                'created_by_user_id' => $actor->id,
                'check_key' => $check['key'],
                'status' => $check['status'],
                'message' => $check['message'],
                'metrics' => $check['metrics'],
                'checked_at' => now(),
            ]);
        }
        return $checks;
    }

    public function summary(array $checks): array
    {
        $counts = ['pass' => 0, 'warning' => 0, 'fail' => 0];
        foreach ($checks as $check) {
            $counts[$check['status']] = ($counts[$check['status']] ?? 0) + 1;
        }
        return [
            'counts' => $counts,
            'critical_ready' => $counts['fail'] === 0,
            'fully_verified' => $counts['fail'] === 0 && $counts['warning'] === 0,
        ];
    }

    private function row(string $key, string $status, string $message, array $metrics = []): array
    {
        return compact('key', 'status', 'message', 'metrics');
    }
}
