<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityModerationAction extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function space(): BelongsTo { return $this->belongsTo(CommunitySpace::class, 'community_space_id'); }
    public function post(): BelongsTo { return $this->belongsTo(CommunityPost::class, 'community_post_id'); }
    public function moderator(): BelongsTo { return $this->belongsTo(User::class, 'moderator_user_id'); }
}
