<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends Model
{
    protected $fillable = ['institution_id','academic_year_id','teacher_id','class_id','learning_group_id','program_id','assignment_role','valid_from','valid_until','status','notes'];
    protected function casts(): array { return ['valid_from'=>'date','valid_until'=>'date']; }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function learningGroup(): BelongsTo { return $this->belongsTo(LearningGroup::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
}
