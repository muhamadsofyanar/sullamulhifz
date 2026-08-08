<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranGuidedSubmissionReview extends Model
{
    protected $fillable = [
        'quran_guided_submission_id', 'reviewer_user_id', 'reviewer_teacher_id',
        'feedback_audio_media_asset_id', 'decision', 'feedback_text',
    ];
    public function submission(): BelongsTo { return $this->belongsTo(QuranGuidedSubmission::class, 'quran_guided_submission_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewer_user_id'); }
    public function feedbackAudio(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'feedback_audio_media_asset_id'); }
}
