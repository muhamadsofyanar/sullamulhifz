<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranDivisionUnit extends Model
{
    protected $fillable = [
        'unit_type','unit_number','code','label','start_global_number','end_global_number','start_surah_id',
        'start_verse','end_surah_id','end_verse','juz_number','hizb_quarter','description',
    ];
}
