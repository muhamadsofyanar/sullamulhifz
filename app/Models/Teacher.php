<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes;
    protected $fillable = ['institution_id','user_id','employee_code','full_name','nickname','gender','phone','email','address','joined_at','specialization','status','notes'];
    protected function casts(): array { return ['joined_at'=>'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function assignments(): HasMany { return $this->hasMany(TeacherAssignment::class); }
}
