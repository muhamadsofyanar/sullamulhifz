<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademyLearningPath extends Model
{
    protected $guarded = [];
    protected $casts = ['is_featured' => 'boolean', 'metadata' => 'array'];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function items(): HasMany { return $this->hasMany(AcademyLearningPathItem::class)->orderBy('sort_order')->orderBy('id'); }
}
