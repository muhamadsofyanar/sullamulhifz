<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalGoal extends Model
{
    protected $fillable = [
        'institution_id', 'personal_profile_id', 'user_id', 'title', 'metric',
        'target_value', 'progress_value', 'starts_on', 'due_on', 'status', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['starts_on'=>'date', 'due_on'=>'date', 'completed_at'=>'datetime'];
    }

    public function profile(): BelongsTo { return $this->belongsTo(PersonalProfile::class, 'personal_profile_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
