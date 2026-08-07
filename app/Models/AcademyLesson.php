<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademyLesson extends Model
{
    protected $fillable = ['academy_module_id','title','slug','lesson_type','summary','body','media_url','duration_minutes','sort_order','requires_action','status','metadata'];
    protected function casts(): array { return ['requires_action' => 'boolean', 'duration_minutes' => 'integer', 'metadata' => 'array']; }
    public function module(): BelongsTo { return $this->belongsTo(AcademyModule::class, 'academy_module_id'); }
    public function progress(): HasMany { return $this->hasMany(AcademyLessonProgress::class); }
    public function recommendations(): HasMany { return $this->hasMany(AcademyRecommendation::class); }
}
