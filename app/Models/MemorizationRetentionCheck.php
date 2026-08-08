<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizationRetentionCheck extends Model
{
    protected $fillable = [
        'institution_id','memorization_milestone_id','student_id','teacher_id','result','assistance_level',
        'checked_at','next_check_date','notes',
    ];

    protected function casts(): array
    {
        return ['checked_at' => 'datetime', 'next_check_date' => 'date'];
    }

    public function milestone(): BelongsTo { return $this->belongsTo(MemorizationMilestone::class, 'memorization_milestone_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
}
