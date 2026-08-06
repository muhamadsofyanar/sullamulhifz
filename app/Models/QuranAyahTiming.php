<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranAyahTiming extends Model
{
    protected $fillable = ['quran_audio_source_id','surah_id','verse_number','start_ms','end_ms','page_number','page_image_url','audio_url','polygon','x','y'];

    public function source(): BelongsTo { return $this->belongsTo(QuranAudioSource::class, 'quran_audio_source_id'); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
}
