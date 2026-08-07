<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiaisonMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'liaison_thread_id', 'sender_user_id', 'message', 'message_type', 'file_path', 'original_name',
        'mime_type', 'file_size', 'media_asset_id',
    ];

    public function thread(): BelongsTo { return $this->belongsTo(LiaisonThread::class, 'liaison_thread_id'); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_user_id'); }
    public function mediaAsset(): BelongsTo { return $this->belongsTo(MediaAsset::class); }
}
