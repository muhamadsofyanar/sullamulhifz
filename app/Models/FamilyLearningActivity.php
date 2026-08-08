<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyLearningActivity extends Model
{
    protected $fillable = [
        'institution_id','student_id','academy_lesson_id','created_by_user_id','completed_by_user_id',
        'title','activity_type','instructions','due_at','status','guardian_reflection','completed_at',
        'reviewed_at','teacher_follow_up',
    ];

    protected function casts(): array
    {
        return ['due_at'=>'datetime','completed_at'=>'datetime','reviewed_at'=>'datetime'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function lesson(): BelongsTo { return $this->belongsTo(AcademyLesson::class, 'academy_lesson_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function completedBy(): BelongsTo { return $this->belongsTo(User::class, 'completed_by_user_id'); }
}
