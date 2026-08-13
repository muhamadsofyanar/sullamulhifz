<?php

namespace App\Services;

use App\Models\BackupRun;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BackupManifestService
{
    public function record(string $type, string $absolutePath, ?int $actorId = null): BackupRun
    {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw ValidationException::withMessages(['path' => 'Artefak backup tidak ditemukan atau tidak dapat dibaca.']);
        }
        $checksum = hash_file('sha256', $absolutePath);
        $size = filesize($absolutePath);
        if (! $checksum || $size === false || $size < 1) {
            throw ValidationException::withMessages(['path' => 'Artefak backup kosong atau checksum gagal dibuat.']);
        }
        $tier = now()->day === 1 ? 'monthly' : (now()->isMonday() ? 'weekly' : 'daily');
        $retention = [
            'daily' => (int) config('sullam.backup.retention_daily', 14),
            'weekly' => (int) config('sullam.backup.retention_weekly', 8),
            'monthly' => (int) config('sullam.backup.retention_monthly', 12),
        ];

        return BackupRun::create([
            'public_id' => (string) Str::uuid(), 'backup_type' => $type, 'status' => 'completed',
            'storage_reference' => basename($absolutePath), 'checksum' => $checksum, 'size_bytes' => $size,
            'retention_tier' => $tier, 'started_at' => now(), 'completed_at' => now(),
            'manifest' => [
                'schema' => 1, 'type' => $type, 'file' => basename($absolutePath),
                'checksum_algorithm' => 'sha256', 'retention' => $retention,
                'recorded_at' => now()->toIso8601String(),
            ],
            'recorded_by_user_id' => $actorId,
        ]);
    }
}
