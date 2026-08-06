<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassEnrollment extends Model
{
    protected $fillable = ['class_id','student_id','academic_year_id','enrolled_at','ended_at','status','notes'];
    protected function casts(): array { return ['enrolled_at'=>'date','ended_at'=>'date']; }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
