<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAssistReview extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['reviewed_at' => 'datetime']; }
    public function draft(): BelongsTo { return $this->belongsTo(AiAssistDraft::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewer_user_id'); }
}
