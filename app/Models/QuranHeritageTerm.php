<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranHeritageTerm extends Model
{
    protected $fillable = [
        'code','name','arabic_name','short_description','practical_use','context_note','sort_order','status',
    ];
}
