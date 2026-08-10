<?php

namespace App\Models;

/** @phase 5.3 Mobile, Offline & Global */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'pwa_enabled' => 'boolean',
            'notification_preferences' => 'array',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
