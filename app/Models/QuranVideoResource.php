<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranVideoResource extends Model
{
    protected $fillable = ['institution_id','created_by_user_id','title','source_type','video_url','thumbnail_url','surah_id','start_verse','end_verse','start_seconds','end_seconds','default_repeat','notes','status'];

    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }

    public function youtubeId(): ?string
    {
        if ($this->source_type !== 'youtube') return null;
        $url = (string) $this->video_url;
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return $m[1];
        }
        return null;
    }
}
