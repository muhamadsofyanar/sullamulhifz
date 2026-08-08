<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AcademyQuizOption extends Model {
    protected $guarded = [];
    protected function casts(): array { return ['is_correct' => 'boolean']; }
    public function question(): BelongsTo { return $this->belongsTo(AcademyQuizQuestion::class, 'academy_quiz_question_id'); }
}
