<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizationRecord extends Model
{
    protected $fillable = ['institution_id','meeting_id','student_id','teacher_id','marhalah_type_id','record_type','surah_id','start_verse','end_verse','result','assistance_level','follow_up','teacher_notes','recorded_at'];
    protected function casts(): array { return ['recorded_at'=>'datetime']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
}
