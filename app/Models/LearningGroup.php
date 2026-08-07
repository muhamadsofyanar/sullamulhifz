<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningGroup extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $fillable = ['institution_id', 'branch_id', 'academic_year_id', 'program_id', 'name', 'code', 'capacity', 'status'];

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function memberships(): HasMany { return $this->hasMany(GroupMembership::class); }
    public function activeMemberships(): HasMany { return $this->memberships()->where('status', 'active'); }
    public function memorizationTargets(): HasMany { return $this->hasMany(MemorizationTarget::class); }
}
