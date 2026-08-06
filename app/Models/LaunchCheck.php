<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaunchCheck extends Model
{
    protected $fillable = [
        'institution_id', 'check_key', 'category', 'label', 'status', 'notes',
        'checked_by_user_id', 'checked_at',
    ];

    protected function casts(): array
    {
        return ['checked_at' => 'datetime'];
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_user_id');
    }
}
