<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaAsset extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $fillable = [
        'institution_id', 'uploaded_by_user_id', 'disk', 'directory', 'file_name', 'original_name',
        'mime_type', 'extension', 'file_size', 'checksum', 'visibility', 'processing_status', 'retention_until',
    ];

    protected function casts(): array
    {
        return ['file_size' => 'integer', 'retention_until' => 'datetime'];
    }

    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by_user_id'); }
    public function links(): HasMany { return $this->hasMany(MediaLink::class); }

    public function storagePath(): string
    {
        return trim($this->directory, '/').'/'.$this->file_name;
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }
}
