<?php

namespace App\Console\Commands;

/** @phase 4.3 Identity Core; @phase 4.4 Multi-tenant verification */

use App\Support\InstitutionType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyIdentityCore extends Command
{
    protected $signature = 'sullam:verify-identity-core';

    protected $description = 'Memverifikasi membership, relasi, dan metadata multi-tenant setelah migration.';

    public function handle(): int
    {
        $requiredTables = ['workspace_memberships', 'user_relationships', 'workspace_invitations'];
        $requiredColumns = [
            'institution_type', 'onboarding_status', 'registration_source', 'custom_domain',
            'brand_primary_color', 'brand_secondary_color', 'terminology', 'approved_at', 'approved_by_user_id',
        ];
        $issues = [];

        foreach ($requiredTables as $table) {
            if (! Schema::hasTable($table)) {
                $issues[] = 'Tabel belum tersedia: '.$table;
            }
        }
        foreach ($requiredColumns as $column) {
            if (! Schema::hasColumn('institutions', $column)) {
                $issues[] = 'Kolom institutions belum tersedia: '.$column;
            }
        }

        if (! $issues) {
            $missingMemberships = DB::table('users as u')
                ->whereNotNull('u.institution_id')
                ->whereNull('u.deleted_at')
                ->whereNotExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('workspace_memberships as wm')
                        ->whereColumn('wm.user_id', 'u.id')
                        ->whereColumn('wm.institution_id', 'u.institution_id')
                        ->where('wm.status', 'active');
                })
                ->count();
            if ($missingMemberships > 0) {
                $issues[] = $missingMemberships.' akun legacy belum memiliki membership aktif.';
            }

            $unsupportedTypes = DB::table('institutions')
                ->whereNull('deleted_at')
                ->whereNotIn('institution_type', InstitutionType::keys(true))
                ->count();
            if ($unsupportedTypes > 0) {
                $issues[] = $unsupportedTypes.' lembaga memakai institution_type yang tidak didukung.';
            }

            $multipleDefaults = DB::table('workspace_memberships')
                ->where('status', 'active')
                ->where('is_default', true)
                ->get(['user_id', 'institution_id'])
                ->groupBy('user_id')
                ->filter(fn ($rows): bool => $rows->pluck('institution_id')->unique()->count() > 1)
                ->count();
            if ($multipleDefaults > 0) {
                $issues[] = $multipleDefaults.' akun mempunyai lebih dari satu workspace default.';
            }
        }

        if ($issues) {
            foreach ($issues as $issue) {
                $this->error($issue);
            }

            return self::FAILURE;
        }

        $this->info('Identity Core sehat: schema, membership legacy, tipe lembaga, dan default workspace valid.');

        return self::SUCCESS;
    }
}
