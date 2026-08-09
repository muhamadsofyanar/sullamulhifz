<?php

namespace App\Models;

/** @phase 4.8 Family & Parent Portal */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilySupportNote extends Model
{
    protected $fillable = [
        'user_relationship_id', 'child_user_id', 'author_user_id',
        'note_type', 'body', 'observed_on', 'status',
    ];

    protected function casts(): array
    {
        return ['observed_on' => 'date'];
    }

    public function relationship(): BelongsTo { return $this->belongsTo(UserRelationship::class, 'user_relationship_id'); }
    public function child(): BelongsTo { return $this->belongsTo(User::class, 'child_user_id'); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_user_id'); }
}
