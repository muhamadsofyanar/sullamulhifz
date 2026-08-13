<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfaqAllocationPolicy extends Model
{
    protected $fillable = ['institution_id', 'version', 'effective_from', 'status', 'change_reason', 'created_by_user_id'];
    protected function casts(): array { return ['effective_from' => 'datetime', 'version' => 'integer']; }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function items(): HasMany { return $this->hasMany(InfaqAllocationPolicyItem::class, 'policy_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
