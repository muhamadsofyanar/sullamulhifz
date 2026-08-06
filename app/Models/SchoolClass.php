<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use SoftDeletes;
    protected $table = 'classes';
    protected $fillable = ['institution_id','academic_year_id','level_id','name','code','capacity','status','notes'];
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function level(): BelongsTo { return $this->belongsTo(Level::class); }
    public function enrollments(): HasMany { return $this->hasMany(ClassEnrollment::class, 'class_id'); }
    public function activeEnrollments(): HasMany { return $this->enrollments()->where('status','active'); }
    public function meetings(): HasMany { return $this->hasMany(Meeting::class, 'class_id'); }
}
