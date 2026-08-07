<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyLearningPathItem extends Model
{
    protected $guarded = [];
    protected $casts = ['is_required' => 'boolean', 'metadata' => 'array'];

    public function path(): BelongsTo { return $this->belongsTo(AcademyLearningPath::class, 'academy_learning_path_id'); }
}
