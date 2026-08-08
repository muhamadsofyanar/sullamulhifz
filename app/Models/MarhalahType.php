<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarhalahType extends Model
{
    public $timestamps = false;
    protected $fillable = ['code','name','sequence','line_count','juz_from','juz_to','portion_unit','portion_value','portion_label','description','journey_note','status'];
}
