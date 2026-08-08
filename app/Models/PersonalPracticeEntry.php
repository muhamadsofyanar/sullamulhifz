<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalPracticeEntry extends Model
{
    protected $fillable = [
        'institution_id', 'personal_profile_id', 'user_id', 'activity_type', 'surah_id',
        'start_verse', 'end_verse', 'duration_minutes', 'self_rating', 'notes', 'practiced_on',
    ];

    protected function casts(): array
    {
        return ['practiced_on' => 'date'];
    }

    public function profile(): BelongsTo { return $this->belongsTo(PersonalProfile::class, 'personal_profile_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }

    public function verseCount(): int
    {
        if (! $this->start_verse || ! $this->end_verse) return 0;
        return max(0, ((int) $this->end_verse - (int) $this->start_verse) + 1);
    }
}
