<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AcademyQuiz extends Model {
    protected $guarded = [];
    public function lesson(): BelongsTo { return $this->belongsTo(AcademyLesson::class, 'academy_lesson_id'); }
    public function questions(): HasMany { return $this->hasMany(AcademyQuizQuestion::class)->orderBy('sort_order')->orderBy('id'); }
    public function attempts(): HasMany { return $this->hasMany(AcademyQuizAttempt::class); }
}
