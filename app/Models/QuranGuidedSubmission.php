<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranGuidedSubmission extends Model
{
    protected $fillable = [
        'guided_quran_enrollment_id', 'learner_institution_id', 'learner_user_id', 'submitted_by_user_id',
        'student_id', 'surah_id', 'audio_media_asset_id', 'submission_type', 'start_verse', 'end_verse',
        'attempt_number', 'evidence_text', 'learner_notes', 'review_status', 'submitted_at', 'last_reviewed_at',
    ];
    protected function casts(): array { return ['submitted_at' => 'datetime', 'last_reviewed_at' => 'datetime']; }
    public function enrollment(): BelongsTo { return $this->belongsTo(GuidedQuranEnrollment::class, 'guided_quran_enrollment_id'); }
    public function learner(): BelongsTo { return $this->belongsTo(User::class, 'learner_user_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class); }
    public function audioAsset(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'audio_media_asset_id'); }
    public function reviews(): HasMany { return $this->hasMany(QuranGuidedSubmissionReview::class); }
}
