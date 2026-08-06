<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = ['meeting_id','student_id','status','arrival_time','notes','recorded_by'];
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function meeting(): BelongsTo { return $this->belongsTo(Meeting::class); }
}
