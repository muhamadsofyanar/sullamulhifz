<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyRecommendation extends Model
{
    protected $fillable = ['institution_id','student_id','academy_lesson_id','created_by_user_id','message','status','recommended_at','completed_at'];
    protected function casts(): array { return ['recommended_at'=>'datetime','completed_at'=>'datetime']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function lesson(): BelongsTo { return $this->belongsTo(AcademyLesson::class, 'academy_lesson_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
