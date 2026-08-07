<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_recipient_id', 'submitted_by_user_id', 'attempt_number', 'guardian_notes',
        'guardian_checklist_completed', 'submitted_at', 'review_status', 'reviewed_by_teacher_id',
        'teacher_feedback', 'reviewed_at', 'file_path', 'original_name', 'mime_type', 'file_size', 'media_asset_id',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'guardian_checklist_completed' => 'boolean', 'reviewed_at' => 'datetime'];
    }

    public function recipient(): BelongsTo { return $this->belongsTo(AssignmentRecipient::class, 'assignment_recipient_id'); }
    public function mediaAsset(): BelongsTo { return $this->belongsTo(MediaAsset::class); }
}
