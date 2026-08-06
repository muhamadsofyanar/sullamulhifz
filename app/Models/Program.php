<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;
    protected $fillable = ['institution_id','name','code','category','description','status'];
}
