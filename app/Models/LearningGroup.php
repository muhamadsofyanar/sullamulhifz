<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningGroup extends Model
{
    use SoftDeletes;

    protected $fillable = ['institution_id','academic_year_id','program_id','name','code','capacity','status'];

    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function memberships(): HasMany { return $this->hasMany(GroupMembership::class); }
    public function activeMemberships(): HasMany { return $this->memberships()->where('status','active'); }
    public function memorizationTargets(): HasMany { return $this->hasMany(MemorizationTarget::class); }
}
