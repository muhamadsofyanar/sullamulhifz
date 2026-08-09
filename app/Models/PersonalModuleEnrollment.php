<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalModuleEnrollment extends Model
{
    protected $fillable = [
        'institution_id', 'personal_profile_id', 'user_id', 'module_key', 'status',
        'enrollment_source', 'enrolled_at', 'expires_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function profile(): BelongsTo { return $this->belongsTo(PersonalProfile::class, 'personal_profile_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
