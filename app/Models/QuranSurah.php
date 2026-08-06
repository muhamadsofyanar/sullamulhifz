<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranSurah extends Model
{
    public $timestamps = false;
    protected $fillable = ['id','name_arabic','name_latin','revelation_place','verse_count','sequence'];
}
