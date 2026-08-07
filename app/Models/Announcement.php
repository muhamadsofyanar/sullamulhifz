<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $fillable = [
        'institution_id', 'created_by_user_id', 'class_id', 'learning_group_id', 'audience_type', 'title', 'content',
        'publish_at', 'expires_at', 'status', 'is_pinned', 'require_acknowledgement', 'attachment_path',
        'attachment_original_name', 'attachment_media_id',
    ];

    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_pinned' => 'boolean',
            'require_acknowledgement' => 'boolean',
        ];
    }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function learningGroup(): BelongsTo { return $this->belongsTo(LearningGroup::class); }
    public function reads(): HasMany { return $this->hasMany(AnnouncementRead::class); }
    public function targets(): HasMany { return $this->hasMany(AnnouncementTarget::class); }
    public function attachmentMedia(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'attachment_media_id'); }
}
