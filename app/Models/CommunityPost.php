<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPost extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['published_at' => 'datetime'];

    public function space(): BelongsTo { return $this->belongsTo(CommunitySpace::class, 'community_space_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
