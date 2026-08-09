<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalCheckIn extends Model
{
    protected $fillable = [
        'institution_id', 'personal_profile_id', 'user_id', 'check_in_on',
        'energy', 'focus', 'intention', 'reflection',
    ];

    protected function casts(): array
    {
        return ['check_in_on' => 'date'];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function profile(): BelongsTo { return $this->belongsTo(PersonalProfile::class, 'personal_profile_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
