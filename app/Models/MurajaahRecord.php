<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MurajaahRecord extends Model
{
    protected $fillable = ['institution_id','meeting_id','learning_cycle_id','review_plan_id','student_id','teacher_id','murajaah_type','surah_id','start_verse','end_verse','result','strength_status','assistance_level','prompt_count','self_correction_count','next_review_date','review_recommendation','teacher_notes','recorded_at'];
    protected function casts(): array { return ['next_review_date'=>'date','recorded_at'=>'datetime']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function learningCycle(): BelongsTo { return $this->belongsTo(TahfizhLearningCycle::class, 'learning_cycle_id'); }
    public function reviewPlan(): BelongsTo { return $this->belongsTo(MemorizationReviewPlan::class, 'review_plan_id'); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
}
