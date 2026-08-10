<?php

namespace App\Models;

/** @phase 5.0 Business, Payment & Integrations */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPlan extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'entitlements' => 'array',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function subscriptions(): HasMany { return $this->hasMany(BillingSubscription::class); }
    public function invoices(): HasMany { return $this->hasMany(BillingInvoice::class); }
}
