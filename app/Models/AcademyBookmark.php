<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyBookmark extends Model
{
    protected $guarded = [];
    protected $casts = ['context' => 'array'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
}
