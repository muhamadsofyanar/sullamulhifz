<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningRecommendationReview extends Model
{
    protected $fillable = [
        'institution_id','learning_insight_id','student_id','teacher_id','decision',
        'original_recommendation','final_recommendation','review_note','reviewed_at',
    ];

    protected function casts(): array { return ['reviewed_at' => 'datetime']; }

    public function insight(): BelongsTo { return $this->belongsTo(LearningInsight::class, 'learning_insight_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
}
