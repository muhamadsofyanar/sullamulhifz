<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AcademyQuizQuestion extends Model {
    protected $guarded = [];
    public function quiz(): BelongsTo { return $this->belongsTo(AcademyQuiz::class, 'academy_quiz_id'); }
    public function options(): HasMany { return $this->hasMany(AcademyQuizOption::class)->orderBy('sort_order')->orderBy('id'); }
}
