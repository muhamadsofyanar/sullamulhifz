<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AcademyLesson extends Model
{
    protected $fillable = ['academy_module_id','title','slug','lesson_type','summary','body','media_url','duration_minutes','sort_order','requires_action','status','metadata'];
    protected function casts(): array { return ['requires_action' => 'boolean', 'duration_minutes' => 'integer', 'metadata' => 'array']; }
    public function module(): BelongsTo { return $this->belongsTo(AcademyModule::class, 'academy_module_id'); }
    public function progress(): HasMany { return $this->hasMany(AcademyLessonProgress::class); }
    public function recommendations(): HasMany { return $this->hasMany(AcademyRecommendation::class); }
    public function quiz(): HasOne { return $this->hasOne(AcademyQuiz::class, 'academy_lesson_id'); }
    public function worksheet(): HasOne { return $this->hasOne(AcademyWorksheet::class, 'academy_lesson_id'); }
}
