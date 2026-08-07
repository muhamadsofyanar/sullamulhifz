<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranPracticePreset extends Model
{
    protected $fillable = ['institution_id','created_by_user_id','quran_audio_source_id','quran_video_resource_id','quran_rubu_id','title','description','mode','page_number','juz_number','hizb_quarter','start_surah_id','end_surah_id','start_verse','end_verse','repeat_count','repeat_scope','gap_seconds','playback_rate','audience','is_featured','status','metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'is_featured' => 'boolean', 'playback_rate' => 'float'];
    }

    public function source(): BelongsTo { return $this->belongsTo(QuranAudioSource::class, 'quran_audio_source_id'); }
    public function video(): BelongsTo { return $this->belongsTo(QuranVideoResource::class, 'quran_video_resource_id'); }
    public function rubu(): BelongsTo { return $this->belongsTo(QuranRubu::class, 'quran_rubu_id'); }
    public function startSurah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'start_surah_id'); }
    public function endSurah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'end_surah_id'); }
}
