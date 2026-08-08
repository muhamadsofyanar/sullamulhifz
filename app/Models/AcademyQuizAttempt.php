<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AcademyQuizAttempt extends Model {
    protected $guarded = [];
    protected function casts(): array { return ['passed' => 'boolean', 'completed_at' => 'datetime']; }
    public function quiz(): BelongsTo { return $this->belongsTo(AcademyQuiz::class, 'academy_quiz_id'); }
    public function answers(): HasMany { return $this->hasMany(AcademyQuizAnswer::class); }
}
