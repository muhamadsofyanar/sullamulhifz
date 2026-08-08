<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranProgramEnrollment extends Model
{
    protected $fillable = [
        'institution_id','quran_program_template_id','student_id','user_id','assigned_by_teacher_id','purpose',
        'schedule_mode','start_date','target_end_date','status','current_step','notes',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'target_end_date' => 'date'];
    }

    public function template(): BelongsTo { return $this->belongsTo(QuranProgramTemplate::class, 'quran_program_template_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function assignedByTeacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'assigned_by_teacher_id'); }
    public function progress(): HasMany { return $this->hasMany(QuranProgramProgress::class); }
}
