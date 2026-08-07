<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyLessonProgress extends Model
{
    protected $table = 'academy_lesson_progress';
    protected $fillable = ['institution_id','user_id','academy_lesson_id','status','progress_percent','started_at','completed_at','notes'];
    protected function casts(): array { return ['progress_percent'=>'integer','started_at'=>'datetime','completed_at'=>'datetime']; }
    public function lesson(): BelongsTo { return $this->belongsTo(AcademyLesson::class, 'academy_lesson_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
