<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranReadingProgress extends Model
{
    protected $table = 'quran_reading_progress';
    protected $guarded = [];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function surah(): BelongsTo
    {
        return $this->belongsTo(QuranSurah::class, 'surah_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(QuranAudioSource::class, 'quran_audio_source_id');
    }
}
