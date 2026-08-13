<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfaqAllocation extends Model
{
    protected $fillable = ['institution_id', 'infaq_transaction_id', 'policy_id', 'category', 'basis_points', 'amount', 'source'];
    protected function casts(): array { return ['basis_points' => 'integer', 'amount' => 'decimal:2']; }
    public function transaction(): BelongsTo { return $this->belongsTo(InfaqTransaction::class, 'infaq_transaction_id'); }
    public function policy(): BelongsTo { return $this->belongsTo(InfaqAllocationPolicy::class, 'policy_id'); }
}
