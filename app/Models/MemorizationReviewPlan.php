<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizationReviewPlan extends Model
{
    protected $fillable = [
        'institution_id','student_id','created_by_teacher_id','memorization_target_id',
        'source_memorization_record_id','completed_by_murajaah_record_id','surah_id',
        'start_verse','end_verse','review_date','reminder_sent_at','review_type','priority','status','notes',
    ];

    protected function casts(): array { return ['review_date'=>'date','reminder_sent_at'=>'datetime']; }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function creator(): BelongsTo { return $this->belongsTo(Teacher::class, 'created_by_teacher_id'); }
    public function target(): BelongsTo { return $this->belongsTo(MemorizationTarget::class, 'memorization_target_id'); }
    public function sourceRecord(): BelongsTo { return $this->belongsTo(MemorizationRecord::class, 'source_memorization_record_id'); }
    public function completedRecord(): BelongsTo { return $this->belongsTo(MurajaahRecord::class, 'completed_by_murajaah_record_id'); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
}
