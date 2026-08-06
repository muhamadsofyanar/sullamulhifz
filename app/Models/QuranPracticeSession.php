<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranPracticeSession extends Model
{
    protected $fillable = ['institution_id','user_id','student_id','quran_practice_preset_id','mode','selection','repeat_target','repeat_completed','started_at','completed_at','duration_seconds','status'];

    protected function casts(): array
    {
        return ['selection' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function preset(): BelongsTo { return $this->belongsTo(QuranPracticePreset::class, 'quran_practice_preset_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
