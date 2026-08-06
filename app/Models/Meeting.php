<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = ['institution_id','schedule_id','class_id','learning_group_id','program_id','teacher_id','closed_by_user_id','meeting_date','started_at','ended_at','topic','meeting_type','general_notes','guardian_summary','status','attendance_completed_at','learning_completed_at','summary_published_at'];
    protected function casts(): array { return ['meeting_date'=>'date','started_at'=>'datetime','ended_at'=>'datetime','attendance_completed_at'=>'datetime','learning_completed_at'=>'datetime','summary_published_at'=>'datetime']; }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function learningGroup(): BelongsTo { return $this->belongsTo(LearningGroup::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by_user_id'); }
    public function attendanceRecords(): HasMany { return $this->hasMany(AttendanceRecord::class); }
    public function tahsinRecords(): HasMany { return $this->hasMany(TahsinRecord::class); }
    public function memorizationRecords(): HasMany { return $this->hasMany(MemorizationRecord::class); }
    public function murajaahRecords(): HasMany { return $this->hasMany(MurajaahRecord::class); }
}
