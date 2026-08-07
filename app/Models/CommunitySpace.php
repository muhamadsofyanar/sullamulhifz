<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunitySpace extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['settings' => 'array'];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function posts(): HasMany { return $this->hasMany(CommunityPost::class); }
}
