<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranLearningErrorItem extends Model
{
    protected $fillable = [
        'institution_id','student_id','teacher_id','meeting_id','record_type','record_id',
        'category','severity','ayah_number','note','resolved_at',
    ];

    protected function casts(): array { return ['resolved_at'=>'datetime']; }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function meeting(): BelongsTo { return $this->belongsTo(Meeting::class); }
}
