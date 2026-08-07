<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassEnrollment extends Model
{
    protected $fillable = [
        'class_id', 'student_id', 'academic_year_id', 'enrolled_at', 'ended_at', 'status', 'previous_enrollment_id', 'notes',
    ];

    protected function casts(): array { return ['enrolled_at' => 'date', 'ended_at' => 'date']; }

    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function previousEnrollment(): BelongsTo { return $this->belongsTo(self::class, 'previous_enrollment_id'); }
    public function nextEnrollment(): HasOne { return $this->hasOne(self::class, 'previous_enrollment_id'); }
}
