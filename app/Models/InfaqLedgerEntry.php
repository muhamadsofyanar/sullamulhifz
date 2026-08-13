<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class InfaqLedgerEntry extends Model
{
    protected $fillable = ['entry_uuid', 'institution_id', 'infaq_transaction_id', 'allocation_id', 'realisation_id', 'fund_transfer_id', 'entry_type', 'category', 'amount', 'occurred_at', 'created_by_user_id', 'metadata'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'occurred_at' => 'datetime', 'metadata' => 'array']; }
    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Ledger infak bersifat append-only.'));
        static::deleting(fn () => throw new LogicException('Ledger infak bersifat append-only.'));
    }
}
