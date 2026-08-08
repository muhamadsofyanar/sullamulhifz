<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuidedQuranEnrollment extends Model
{
    protected $fillable = [
        'guided_quran_program_id', 'learner_institution_id', 'learner_user_id', 'student_id',
        'learner_mode', 'status', 'enrolled_at', 'completed_at', 'metadata',
    ];
    protected function casts(): array { return ['enrolled_at' => 'datetime', 'completed_at' => 'datetime', 'metadata' => 'array']; }
    public function program(): BelongsTo { return $this->belongsTo(GuidedQuranProgram::class, 'guided_quran_program_id'); }
    public function learner(): BelongsTo { return $this->belongsTo(User::class, 'learner_user_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function submissions(): HasMany { return $this->hasMany(QuranGuidedSubmission::class); }
}
