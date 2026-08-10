<?php

namespace App\Models;

/** @phase 5.1 SaaS Production Readiness */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalCheckRun extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
