<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahsinRecord extends Model
{
    protected $fillable = ['institution_id','meeting_id','student_id','teacher_id','material_text','surah_id','start_verse','end_verse','overall_status','fluency_status','makhraj_status','tajwid_status','adab_status','decision','focus_categories','teacher_notes','follow_up'];
    protected function casts(): array { return ['focus_categories'=>'array']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
}
