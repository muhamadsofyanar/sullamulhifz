<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuidedQuranProgram extends Model
{
    protected $fillable = [
        'provider_institution_id', 'academy_program_id', 'created_by_user_id', 'title', 'slug',
        'program_type', 'delivery_mode', 'target_juz', 'summary', 'description', 'submission_guidance',
        'accepts_audio', 'accepts_text', 'is_public', 'status', 'enrollment_opens_at', 'enrollment_closes_at',
    ];

    protected function casts(): array
    {
        return [
            'accepts_audio' => 'boolean', 'accepts_text' => 'boolean', 'is_public' => 'boolean',
            'enrollment_opens_at' => 'datetime', 'enrollment_closes_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo { return $this->belongsTo(Institution::class, 'provider_institution_id'); }
    public function academyProgram(): BelongsTo { return $this->belongsTo(AcademyProgram::class); }
    public function enrollments(): HasMany { return $this->hasMany(GuidedQuranEnrollment::class); }
    public function reviewers(): HasMany { return $this->hasMany(GuidedQuranProgramReviewer::class); }
}
