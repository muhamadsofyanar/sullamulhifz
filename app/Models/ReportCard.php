<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCard extends Model
{
    protected $fillable = [
        'institution_id','academic_year_id','student_id','semester','period_start','period_end',
        'teacher_summary','guardian_note','status','prepared_by_user_id','published_at',
    ];

    protected function casts(): array
    {
        return ['period_start'=>'date','period_end'=>'date','published_at'=>'datetime'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function preparedBy(): BelongsTo { return $this->belongsTo(User::class, 'prepared_by_user_id'); }
    public function items(): HasMany { return $this->hasMany(ReportCardItem::class)->orderBy('sort_order'); }
}
