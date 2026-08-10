<?php

namespace App\Models;

/** @phase 6.0 Memorization focus and assessment relations */

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $fillable = [
        'institution_id', 'branch_id', 'student_code', 'full_name', 'nickname', 'gender', 'birth_place', 'birth_date',
        'photo_path', 'photo_media_id', 'address', 'joined_at', 'status', 'special_needs_notes', 'stifin_status',
        'stifin_result', 'stifin_tested_at', 'stifin_notes',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'joined_at' => 'date', 'stifin_tested_at' => 'date'];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function photoMedia(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'photo_media_id'); }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardians')
            ->withPivot([
                'relationship', 'is_primary_contact', 'can_receive_notifications',
                'can_submit_assignments', 'can_view_learning_records', 'started_at', 'ended_at',
            ])
            ->where(function ($query): void {
                $query->whereNull('student_guardians.started_at')
                    ->orWhereDate('student_guardians.started_at', '<=', today());
            })
            ->where(function ($query): void {
                $query->whereNull('student_guardians.ended_at')
                    ->orWhereDate('student_guardians.ended_at', '>=', today());
            })
            ->withTimestamps();
    }

    public function enrollments(): HasMany { return $this->hasMany(ClassEnrollment::class); }
    public function groupMemberships(): HasMany { return $this->hasMany(GroupMembership::class); }
    public function currentEnrollment() { return $this->hasOne(ClassEnrollment::class)->where('status', 'active')->latestOfMany(); }
    public function tahfizhLearningCycles(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(TahfizhLearningCycle::class); }
    public function memorizationReviewPlans(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(MemorizationReviewPlan::class); }
    public function quranLearningErrors(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(QuranLearningErrorItem::class); }
    public function memorizationRecords(): HasMany { return $this->hasMany(MemorizationRecord::class); }
    public function murajaahRecords(): HasMany { return $this->hasMany(MurajaahRecord::class); }
    public function tahsinRecords(): HasMany { return $this->hasMany(TahsinRecord::class); }
    public function attendanceRecords(): HasMany { return $this->hasMany(AttendanceRecord::class); }
    public function reportCards(): HasMany { return $this->hasMany(ReportCard::class); }
    public function memorizationTargets(): HasMany { return $this->hasMany(MemorizationTarget::class); }
    public function learningObservations(): HasMany { return $this->hasMany(LearningObservation::class); }
    public function marhalahHistories(): HasMany { return $this->hasMany(StudentMarhalahHistory::class); }
    public function quranJourneyPortions(): HasMany { return $this->hasMany(QuranJourneyPortion::class); }
    public function quranJourneyProfile() { return $this->hasOne(QuranJourneyProfile::class); }
    public function memorizationMilestones(): HasMany { return $this->hasMany(MemorizationMilestone::class); }
    public function quranProgramEnrollments(): HasMany { return $this->hasMany(QuranProgramEnrollment::class); }
    public function talentProgressRecords(): HasMany { return $this->hasMany(TalentProgressRecord::class); }
    public function portfolios(): HasMany { return $this->hasMany(StudentPortfolio::class); }
    public function memorizationFocuses(): HasMany { return $this->hasMany(StudentMemorizationFocus::class); }
    public function memorizationAssessments(): HasMany { return $this->hasMany(StudentMemorizationAssessment::class); }
}
