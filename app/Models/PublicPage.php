<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicPage extends Model
{
    use SoftDeletes;

    protected $fillable = ['institution_id','slug','title','summary','content','status','seo_title','seo_description','sort_order'];
}
