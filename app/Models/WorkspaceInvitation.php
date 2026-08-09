<?php

namespace App\Models;

/** @phase 4.3 Identity & Relationship Core */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceInvitation extends Model
{
    protected $fillable = [
        'institution_id', 'invited_by_user_id', 'accepted_by_user_id', 'role_id',
        'membership_type', 'email', 'phone', 'token_hash', 'status', 'expires_at', 'accepted_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function invitedBy(): BelongsTo { return $this->belongsTo(User::class, 'invited_by_user_id'); }
    public function acceptedBy(): BelongsTo { return $this->belongsTo(User::class, 'accepted_by_user_id'); }
    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
}
