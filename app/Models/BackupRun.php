<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupRun extends Model
{
    protected $fillable = ['public_id', 'backup_type', 'status', 'storage_reference', 'checksum', 'size_bytes', 'retention_tier', 'started_at', 'completed_at', 'manifest', 'failure_reason', 'recorded_by_user_id'];
    protected function casts(): array { return ['size_bytes' => 'integer', 'started_at' => 'datetime', 'completed_at' => 'datetime', 'manifest' => 'array']; }
}
