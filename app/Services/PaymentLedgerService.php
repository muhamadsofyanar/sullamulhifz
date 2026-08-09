<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PaymentLedgerService
{
    public function bankTransferDestination(): array
    {
        return [
            'bank_name' => (string) config('sullam.payment.bank_transfer.bank_name'),
            'account_name' => (string) config('sullam.payment.bank_transfer.account_name'),
            'account_number' => (string) config('sullam.payment.bank_transfer.account_number'),
        ];
    }

    public function createBankTransferPending(Institution $institution, ?User $user, string $purpose, string|int|float $amount, array $metadata = []): PaymentTransaction
    {
        return $this->createPending($institution, $user, $purpose, $amount, array_merge($metadata, [
            'payment_method' => 'bank_transfer',
            'payment_destination' => $this->bankTransferDestination(),
        ]));
    }

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
