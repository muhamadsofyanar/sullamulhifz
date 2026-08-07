<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $table = 'classes';
    protected $fillable = ['institution_id', 'branch_id', 'academic_year_id', 'level_id', 'name', 'code', 'capacity', 'status', 'notes'];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function level(): BelongsTo { return $this->belongsTo(Level::class); }
    public function enrollments(): HasMany { return $this->hasMany(ClassEnrollment::class, 'class_id'); }
    public function activeEnrollments(): HasMany { return $this->enrollments()->where('status', 'active'); }
    public function meetings(): HasMany { return $this->hasMany(Meeting::class, 'class_id'); }
}
