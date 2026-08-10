<?php

namespace App\Models;

/** @phase 6.0 Periodic five-aspect assessment */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMemorizationAssessment extends Model
{
    protected $fillable = [
        'institution_id', 'student_id', 'teacher_id', 'assessment_type', 'assessed_on',
        'accuracy_status', 'fluency_status', 'independence_status',
        'makhraj_tajwid_status', 'retention_status', 'recommended_focus', 'summary',
    ];

    protected function casts(): array { return ['assessed_on' => 'date']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
}
