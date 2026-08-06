<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiaisonThread extends Model
{
    use SoftDeletes;
    protected $fillable = ['institution_id','student_id','class_id','category','subject','created_by_user_id','assigned_teacher_id','status','last_message_at'];
    protected function casts(): array { return ['last_message_at'=>'datetime']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function messages(): HasMany { return $this->hasMany(LiaisonMessage::class); }
}
