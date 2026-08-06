<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FridayDevelopmentSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institution_id','academic_year_id','created_by_user_id','class_id','session_date','category','title',
        'objectives','summary','quran_surah_id','quran_start_verse','quran_end_verse','home_follow_up','media_url',
        'worksheet_path','worksheet_original_name','family_response_enabled','status','published_at',
    ];

    protected function casts(): array
    {
        return ['session_date'=>'date','published_at'=>'datetime','family_response_enabled'=>'boolean'];
    }

    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'quran_surah_id'); }
}
