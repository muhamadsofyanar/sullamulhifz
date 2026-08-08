<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranProgramStep extends Model
{
    protected $fillable = [
        'quran_program_template_id','sequence','mnemonic_letter','label','start_surah_id','end_surah_id',
        'start_juz','end_juz','description',
    ];

    public function template(): BelongsTo { return $this->belongsTo(QuranProgramTemplate::class, 'quran_program_template_id'); }
    public function progress(): HasMany { return $this->hasMany(QuranProgramProgress::class); }
}
