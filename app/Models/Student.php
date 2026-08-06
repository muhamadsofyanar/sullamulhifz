<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;
    protected $fillable = ['institution_id','student_code','full_name','nickname','gender','birth_place','birth_date','photo_path','address','joined_at','status','special_needs_notes','stifin_status','stifin_result','stifin_tested_at','stifin_notes'];
    protected function casts(): array { return ['birth_date'=>'date','joined_at'=>'date','stifin_tested_at'=>'date']; }
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardians')
            ->withPivot(['relationship','is_primary_contact','can_receive_notifications','can_submit_assignments','can_view_learning_records'])
            ->withTimestamps();
    }
    public function enrollments(): HasMany { return $this->hasMany(ClassEnrollment::class); }
    public function groupMemberships(): HasMany { return $this->hasMany(GroupMembership::class); }
    public function currentEnrollment() { return $this->hasOne(ClassEnrollment::class)->where('status','active')->latestOfMany(); }
    public function memorizationRecords(): HasMany { return $this->hasMany(MemorizationRecord::class); }
    public function murajaahRecords(): HasMany { return $this->hasMany(MurajaahRecord::class); }
    public function tahsinRecords(): HasMany { return $this->hasMany(TahsinRecord::class); }
    public function attendanceRecords(): HasMany { return $this->hasMany(AttendanceRecord::class); }
    public function reportCards(): HasMany { return $this->hasMany(ReportCard::class); }
    public function memorizationTargets(): HasMany { return $this->hasMany(MemorizationTarget::class); }
    public function learningObservations(): HasMany { return $this->hasMany(LearningObservation::class); }
}
