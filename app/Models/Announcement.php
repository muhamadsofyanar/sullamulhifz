<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;
    protected $fillable = ['institution_id','created_by_user_id','class_id','title','content','publish_at','expires_at','status'];
    protected function casts(): array { return ['publish_at'=>'datetime','expires_at'=>'datetime']; }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
}
