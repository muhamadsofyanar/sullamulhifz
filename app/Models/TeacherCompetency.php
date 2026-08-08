<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherCompetency extends Model
{
    protected $fillable = ['institution_id','academy_lesson_id','code','title','category','description','evidence_guidance','sort_order','status'];

    public function lesson(): BelongsTo { return $this->belongsTo(AcademyLesson::class, 'academy_lesson_id'); }
    public function progress(): HasMany { return $this->hasMany(TeacherCompetencyProgress::class); }
}
