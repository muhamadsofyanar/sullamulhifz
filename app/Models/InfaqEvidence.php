<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfaqEvidence extends Model
{
    protected $fillable = ['institution_id', 'realisation_id', 'evidence_type', 'original_media_asset_id', 'public_media_asset_id', 'public_review_status', 'public_reviewed_by_user_id', 'public_reviewed_at', 'review_note'];
    protected function casts(): array { return ['public_reviewed_at' => 'datetime']; }
    public function realisation(): BelongsTo { return $this->belongsTo(InfaqRealisation::class, 'realisation_id'); }
    public function originalAsset(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'original_media_asset_id'); }
    public function publicAsset(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'public_media_asset_id'); }
}
