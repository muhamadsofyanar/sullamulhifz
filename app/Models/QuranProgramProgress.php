<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranProgramProgress extends Model
{
    protected $table = 'quran_program_progress';

    protected $fillable = [
        'quran_program_enrollment_id','quran_program_step_id','status','started_at','completed_at',
        'last_surah_id','last_verse','last_global_number','notes',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function enrollment(): BelongsTo { return $this->belongsTo(QuranProgramEnrollment::class, 'quran_program_enrollment_id'); }
    public function step(): BelongsTo { return $this->belongsTo(QuranProgramStep::class, 'quran_program_step_id'); }
}
