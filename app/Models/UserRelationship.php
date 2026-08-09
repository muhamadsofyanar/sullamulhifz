<?php

namespace App\Models;

/** @phase 4.3 Identity & Relationship Core */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRelationship extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institution_id', 'context_key', 'from_user_id', 'to_user_id', 'created_by_user_id',
        'relationship_type', 'status', 'visibility_scope', 'starts_at', 'ends_at', 'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'visibility_scope' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function fromUser(): BelongsTo { return $this->belongsTo(User::class, 'from_user_id'); }
    public function toUser(): BelongsTo { return $this->belongsTo(User::class, 'to_user_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
