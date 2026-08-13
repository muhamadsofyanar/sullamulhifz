<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestoreRequest extends Model
{
    protected $fillable = ['public_id', 'backup_run_id', 'reason', 'status', 'requested_by_user_id', 'approved_by_user_id', 'approved_at', 'simulation_result', 'simulation_completed_at', 'operator_note'];
    protected function casts(): array { return ['approved_at' => 'datetime', 'simulation_result' => 'array', 'simulation_completed_at' => 'datetime']; }
    public function backupRun(): BelongsTo { return $this->belongsTo(BackupRun::class); }
}
