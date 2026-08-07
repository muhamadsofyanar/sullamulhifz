<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TahfizhLearningCycle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institution_id','academic_year_id','student_id','teacher_id','memorization_target_id',
        'cycle_type','preparation_method','status','teacher_guidance','guardian_guidance',
        'started_at','ready_at','completed_at',
    ];

    protected function casts(): array
    {
        return ['started_at'=>'datetime','ready_at'=>'datetime','completed_at'=>'datetime'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function target(): BelongsTo { return $this->belongsTo(MemorizationTarget::class, 'memorization_target_id'); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function memorizationRecords(): HasMany { return $this->hasMany(MemorizationRecord::class, 'learning_cycle_id'); }
    public function murajaahRecords(): HasMany { return $this->hasMany(MurajaahRecord::class, 'learning_cycle_id'); }
}
