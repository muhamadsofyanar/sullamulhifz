<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranJourneyProfile extends Model
{
    protected $fillable = [
        'institution_id','student_id','current_marhalah_type_id','current_juz_number','stage_code',
        'cadence_mode','cadence_notes','started_at','foundation_completed_at','updated_by_teacher_id','status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'foundation_completed_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function marhalah(): BelongsTo { return $this->belongsTo(MarhalahType::class, 'current_marhalah_type_id'); }
    public function updatedByTeacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'updated_by_teacher_id'); }
}
