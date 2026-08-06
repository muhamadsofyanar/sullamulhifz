<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMembership extends Model
{
    protected $fillable = ['learning_group_id','student_id','joined_at','ended_at','status','notes'];
    protected function casts(): array { return ['joined_at'=>'date','ended_at'=>'date']; }
    public function group(): BelongsTo { return $this->belongsTo(LearningGroup::class, 'learning_group_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
