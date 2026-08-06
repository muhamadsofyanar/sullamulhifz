<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MurajaahRecord extends Model
{
    protected $fillable = ['institution_id','meeting_id','student_id','teacher_id','murajaah_type','surah_id','start_verse','end_verse','result','assistance_level','next_review_date','teacher_notes','recorded_at'];
    protected function casts(): array { return ['next_review_date'=>'date','recorded_at'=>'datetime']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
}
