<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfaqAllocationPolicyItem extends Model
{
    protected $fillable = ['policy_id', 'category', 'basis_points'];
    protected function casts(): array { return ['basis_points' => 'integer']; }
    public function policy(): BelongsTo { return $this->belongsTo(InfaqAllocationPolicy::class, 'policy_id'); }
}
