<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AcademyQuizAnswer extends Model {
    protected $guarded = [];
    protected function casts(): array { return ['is_correct' => 'boolean']; }
    public function attempt(): BelongsTo { return $this->belongsTo(AcademyQuizAttempt::class, 'academy_quiz_attempt_id'); }
    public function question(): BelongsTo { return $this->belongsTo(AcademyQuizQuestion::class, 'academy_quiz_question_id'); }
    public function option(): BelongsTo { return $this->belongsTo(AcademyQuizOption::class, 'academy_quiz_option_id'); }
}
