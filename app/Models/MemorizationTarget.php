<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemorizationTarget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institution_id','academic_year_id','student_id','learning_group_id','assigned_by_teacher_id',
        'quran_rubu_id','quran_journey_portion_id','surah_id','start_verse','end_verse','marhalah_type_id','journey_juz_number','portion_confirmed','portion_note',
        'mushaf_page_number','mushaf_end_page_number','mushaf_start_line','mushaf_end_line','start_word_location','end_word_location','target_type','status',
        'target_date','due_date','completed_at','notes',
    ];

    protected function casts(): array
    {
        return ['target_date'=>'date','due_date'=>'date','completed_at'=>'datetime','portion_confirmed'=>'boolean'];
    }

    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function learningGroup(): BelongsTo { return $this->belongsTo(LearningGroup::class); }
    public function assignedByTeacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'assigned_by_teacher_id'); }
    public function rubu(): BelongsTo { return $this->belongsTo(QuranRubu::class, 'quran_rubu_id'); }
    public function journeyPortion(): BelongsTo { return $this->belongsTo(QuranJourneyPortion::class, 'quran_journey_portion_id'); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
    public function marhalah(): BelongsTo { return $this->belongsTo(MarhalahType::class, 'marhalah_type_id'); }
    public function learningCycles(): HasMany { return $this->hasMany(TahfizhLearningCycle::class); }
    public function reviewPlans(): HasMany { return $this->hasMany(MemorizationReviewPlan::class); }
}
