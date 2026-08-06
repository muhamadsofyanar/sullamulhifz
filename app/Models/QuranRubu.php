<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranRubu extends Model
{
    protected $fillable = ['juz_number','rubu_number','name','start_surah_id','end_surah_id','description','status'];

    public function startSurah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'start_surah_id'); }
    public function endSurah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'end_surah_id'); }
    public function targets(): HasMany { return $this->hasMany(MemorizationTarget::class); }
}
