<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMarhalahHistory extends Model
{
    protected $fillable = [
        'student_id', 'marhalah_type_id', 'effective_from', 'effective_until', 'decision', 'reason',
        'decided_by_teacher_id', 'evidence_notes', 'status',
    ];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_until' => 'date'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function marhalahType(): BelongsTo { return $this->belongsTo(MarhalahType::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'decided_by_teacher_id'); }
}
