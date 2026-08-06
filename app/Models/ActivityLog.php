<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['institution_id','user_id','action','subject_type','subject_id','old_values','new_values','reason','ip_address','user_agent','created_at'];
    protected function casts(): array { return ['old_values'=>'array','new_values'=>'array','created_at'=>'datetime']; }
}
