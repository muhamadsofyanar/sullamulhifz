<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureFlag extends Model
{
    protected $fillable = ['institution_id', 'feature_key', 'enabled', 'config'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'config' => 'array'];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
}
