<?php

namespace App\Models;

/** @phase 5.0 Business, Payment & Integrations */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingInvoice extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function plan(): BelongsTo { return $this->belongsTo(BillingPlan::class, 'billing_plan_id'); }
    public function subscription(): BelongsTo { return $this->belongsTo(BillingSubscription::class, 'billing_subscription_id'); }
    public function payments(): HasMany { return $this->hasMany(PaymentTransaction::class, 'billing_invoice_id'); }
}
