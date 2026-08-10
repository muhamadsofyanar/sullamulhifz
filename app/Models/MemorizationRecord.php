<?php

namespace App\Models;

/** @phase 6.0 Daily decision compatibility fields */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizationRecord extends Model
{
    protected $fillable = ['institution_id','meeting_id','memorization_target_id','learning_cycle_id','student_id','teacher_id','marhalah_type_id','record_type','delivery_mode','surah_id','start_verse','end_verse','result','daily_decision','short_note','submission_key','fluency_status','tajwid_status','error_count','prompt_count','self_correction_count','assistance_level','follow_up','review_recommendation','next_review_date','teacher_notes','recorded_at'];
    protected function casts(): array { return ['recorded_at'=>'datetime','next_review_date'=>'date']; }
    public function target(): BelongsTo { return $this->belongsTo(MemorizationTarget::class, 'memorization_target_id'); }
    public function learningCycle(): BelongsTo { return $this->belongsTo(TahfizhLearningCycle::class, 'learning_cycle_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
}
