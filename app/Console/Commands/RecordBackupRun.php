<?php

namespace App\Console\Commands;

use App\Services\BackupManifestService;
use Illuminate\Console\Command;

class RecordBackupRun extends Command
{
    protected $signature = 'sullam:record-backup {type : database atau private-files} {path : Path absolut artefak backup}';
    protected $description = 'Memverifikasi artefak backup dan mencatat manifest/checksum v6.1';

    public function handle(BackupManifestService $backups): int
    {
        $type = (string) $this->argument('type');
        if (! in_array($type, ['database', 'private-files'], true)) {
            $this->error('Tipe harus database atau private-files.');
            return self::INVALID;
        }
        $run = $backups->record($type, (string) $this->argument('path'));
        $this->info("Backup {$run->public_id} tercatat: {$run->retention_tier}, {$run->size_bytes} byte, sha256 {$run->checksum}");

        return self::SUCCESS;
    }
}
