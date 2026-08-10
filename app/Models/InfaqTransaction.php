<?php

namespace App\Models;

/** @phase 6.0 Voluntary infaq ledger */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfaqTransaction extends Model
{
    protected $fillable = [
        'public_id', 'institution_id', 'user_id', 'idempotency_key', 'purpose',
        'amount', 'currency', 'payment_method', 'status', 'is_anonymous',
        'receipt_number', 'verified_by_user_id', 'verified_at', 'paid_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', 'is_anonymous' => 'boolean', 'metadata' => 'array',
            'verified_at' => 'datetime', 'paid_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_user_id'); }
}
