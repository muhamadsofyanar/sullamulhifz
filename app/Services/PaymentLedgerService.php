<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PaymentLedgerService
{
    public function createPending(Institution $institution, ?User $user, string $purpose, string|int|float $amount, array $metadata = []): PaymentTransaction
    {
        if ($user) {
            abort_unless((int) $user->institution_id === (int) $institution->id, 403);
        }
        if ((float) $amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Nominal pembayaran harus lebih dari nol.']);
        }

        return PaymentTransaction::create([
            'institution_id' => $institution->id,
            'user_id' => $user?->id,
            'purpose' => $purpose,
            'amount' => $amount,
            'currency' => 'IDR',
            'status' => 'pending',
            'metadata' => $metadata,
        ]);
    }

    public function markPaid(PaymentTransaction $transaction, string $provider, string $externalReference, array $metadata = []): PaymentTransaction
    {
        if ($transaction->status === 'paid') {
            return $transaction;
        }

        $transaction->update([
            'provider' => $provider,
            'external_reference' => $externalReference,
            'status' => 'paid',
            'paid_at' => now(),
            'metadata' => array_merge($transaction->metadata ?? [], $metadata),
        ]);

        return $transaction->refresh();
    }
}
