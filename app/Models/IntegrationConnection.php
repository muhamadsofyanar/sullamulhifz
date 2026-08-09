<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationConnection extends Model
{
    protected $fillable = [
        'institution_id', 'provider', 'display_name', 'status', 'configuration',
        'last_checked_at', 'last_error', 'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'last_checked_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

}
