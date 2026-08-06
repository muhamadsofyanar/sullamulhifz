<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarhalahType extends Model
{
    public $timestamps = false;
    protected $fillable = ['code','name','sequence','line_count','description','status'];
}
