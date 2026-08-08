<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranJourneyPortion extends Model
{
    protected $fillable = [
        'institution_id','student_id','marhalah_type_id','assigned_by_teacher_id','journey_juz_number',
        'portion_unit','portion_value','portion_label','start_global_number','end_global_number',
        'start_surah_id','start_verse','end_surah_id','end_verse','start_page_number','end_page_number',
        'mushaf_layout_code','start_line_number','end_line_number','start_word_location','end_word_location','line_block_key','selection_source',
        'teacher_confirmed','status','scheduled_for','due_date','completed_at','notes',
    ];

    protected function casts(): array
    {
        return [
            'portion_value'=>'decimal:2','teacher_confirmed'=>'boolean','scheduled_for'=>'date',
            'due_date'=>'date','completed_at'=>'datetime',
        ];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function marhalah(): BelongsTo { return $this->belongsTo(MarhalahType::class, 'marhalah_type_id'); }
    public function assignedByTeacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'assigned_by_teacher_id'); }
    public function startSurah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'start_surah_id'); }
    public function endSurah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'end_surah_id'); }
    public function targets(): HasMany { return $this->hasMany(MemorizationTarget::class); }
}
