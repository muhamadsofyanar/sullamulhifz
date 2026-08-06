<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranAudioSource extends Model
{
    protected $fillable = ['institution_id','name','provider','external_id','reciter_name','rewaya','base_url','metadata','is_default','status'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'is_default' => 'boolean'];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function timings(): HasMany { return $this->hasMany(QuranAyahTiming::class); }
    public function presets(): HasMany { return $this->hasMany(QuranPracticePreset::class); }
}
