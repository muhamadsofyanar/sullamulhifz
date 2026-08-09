<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiAssistDraft extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['evidence_snapshot' => 'array', 'generated_at' => 'datetime']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function review(): HasOne { return $this->hasOne(AiAssistReview::class); }
}
