<?php

namespace App\Models;

/** @phase 6.0 Voluntary infaq ledger */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfaqTransaction extends Model
{
    protected $fillable = [
        'public_id', 'institution_id', 'user_id', 'idempotency_key', 'purpose',
        'amount', 'currency', 'payment_method', 'status', 'is_anonymous',
        'show_donor_name', 'donor_consent_at', 'transfer_proof_media_asset_id',
        'receipt_number', 'verified_by_user_id', 'verified_at', 'paid_at',
        'mutation_match_note', 'rejection_reason', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', 'is_anonymous' => 'boolean', 'show_donor_name' => 'boolean', 'metadata' => 'array',
            'donor_consent_at' => 'datetime', 'verified_at' => 'datetime', 'paid_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by_user_id'); }
    public function transferProof(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'transfer_proof_media_asset_id'); }
    public function allocations(): HasMany { return $this->hasMany(InfaqAllocation::class); }
}
