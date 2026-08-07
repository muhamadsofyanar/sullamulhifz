<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $fillable = [
        'institution_id', 'branch_id', 'academic_year_id', 'class_id', 'learning_group_id', 'program_id',
        'teacher_assignment_id', 'day_of_week', 'start_time', 'end_time', 'location', 'effective_from',
        'effective_until', 'status',
    ];

    protected function casts(): array { return ['effective_from' => 'date', 'effective_until' => 'date']; }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function learningGroup(): BelongsTo { return $this->belongsTo(LearningGroup::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function teacherAssignment(): BelongsTo { return $this->belongsTo(TeacherAssignment::class); }
}
