<?php

namespace App\Models;

/** @phase 6.0 Single active memorization focus */

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMemorizationFocus extends Model
{
    protected $fillable = [
        'institution_id', 'student_id', 'set_by_teacher_id', 'focus_key', 'status',
        'notes', 'started_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereNull('ended_at');
    }

    public static function activeFor(int $institutionId, int $studentId): ?self
    {
        return static::query()->active()
            ->where('institution_id', $institutionId)
            ->where('student_id', $studentId)
            ->latest('started_at')
            ->first();
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'set_by_teacher_id'); }
}
