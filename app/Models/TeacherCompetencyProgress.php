<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCompetencyProgress extends Model
{
    protected $table = 'teacher_competency_progress';

    protected $fillable = [
        'institution_id','teacher_id','teacher_competency_id','status','reflection','evidence_note',
        'reviewed_by_user_id','review_note','submitted_at','reviewed_at',
    ];

    protected function casts(): array { return ['submitted_at'=>'datetime','reviewed_at'=>'datetime']; }

    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function competency(): BelongsTo { return $this->belongsTo(TeacherCompetency::class, 'teacher_competency_id'); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
}
