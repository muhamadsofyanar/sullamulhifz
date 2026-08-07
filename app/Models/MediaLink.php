<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaLink extends Model
{
    protected $fillable = ['media_asset_id', 'attachable_type', 'attachable_id', 'purpose', 'sort_order'];

    public function mediaAsset(): BelongsTo { return $this->belongsTo(MediaAsset::class); }
    public function attachable(): MorphTo { return $this->morphTo(); }
}
