<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalProfile extends Model
{
    protected $fillable = [
        'institution_id', 'user_id', 'student_id', 'experience_level', 'primary_focus',
        'daily_minutes', 'target_juz', 'target_surah_id', 'target_date',
        'onboarding_completed_at', 'privacy_acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'onboarding_completed_at' => 'datetime',
            'privacy_acknowledged_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function targetSurah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'target_surah_id'); }
    public function goals(): HasMany { return $this->hasMany(PersonalGoal::class); }
    public function practiceEntries(): HasMany { return $this->hasMany(PersonalPracticeEntry::class); }
    public function moduleEnrollments(): HasMany { return $this->hasMany(PersonalModuleEnrollment::class); }
    public function checkIns(): HasMany { return $this->hasMany(PersonalCheckIn::class); }
}
