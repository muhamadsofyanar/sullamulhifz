<?php

namespace App\Models;

/** @phase 4.6 Private Ustadz */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorshipSession extends Model
{
    protected $fillable = [
        'user_relationship_id', 'learner_user_id', 'mentor_user_id', 'requested_by_user_id',
        'focus', 'learner_note', 'mentor_note', 'scheduled_at', 'duration_minutes',
        'status', 'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function relationship(): BelongsTo { return $this->belongsTo(UserRelationship::class, 'user_relationship_id'); }
    public function learner(): BelongsTo { return $this->belongsTo(User::class, 'learner_user_id'); }
    public function mentor(): BelongsTo { return $this->belongsTo(User::class, 'mentor_user_id'); }
    public function requestedBy(): BelongsTo { return $this->belongsTo(User::class, 'requested_by_user_id'); }
}
