<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningObservation extends Model
{
    protected $fillable = [
        'institution_id','academic_year_id','student_id','teacher_id','category','method_name',
        'context','response','effectiveness','observed_at','notes',
    ];

    protected function casts(): array { return ['observed_at'=>'datetime']; }

    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
}
