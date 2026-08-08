<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuidedQuranProgramReviewer extends Model
{
    protected $fillable = ['guided_quran_program_id', 'reviewer_user_id', 'reviewer_teacher_id', 'added_by_user_id', 'status'];
    public function program(): BelongsTo { return $this->belongsTo(GuidedQuranProgram::class, 'guided_quran_program_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewer_user_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'reviewer_teacher_id'); }
}
