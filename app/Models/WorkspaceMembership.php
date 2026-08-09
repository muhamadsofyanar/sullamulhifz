<?php

namespace App\Models;

/** @phase 4.3 Identity & Relationship Core */

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceMembership extends Model
{
    protected $fillable = [
        'institution_id', 'user_id', 'role_id', 'branch_id', 'membership_type',
        'display_label', 'status', 'is_default', 'joined_at', 'left_at', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereNull('left_at');
    }
}
