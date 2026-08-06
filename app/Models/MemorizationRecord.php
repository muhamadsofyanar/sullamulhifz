<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizationRecord extends Model
{
    protected $fillable = ['institution_id','meeting_id','memorization_target_id','student_id','teacher_id','marhalah_type_id','record_type','surah_id','start_verse','end_verse','result','fluency_status','tajwid_status','error_count','assistance_level','follow_up','review_recommendation','teacher_notes','recorded_at'];
    protected function casts(): array { return ['recorded_at'=>'datetime']; }
    public function target(): BelongsTo { return $this->belongsTo(MemorizationTarget::class, 'memorization_target_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
}
