<?php

namespace App\Services;

/** @phase 6.0 Voluntary infaq service */

use App\Models\InfaqTransaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InfaqService
{
    /** @param array<string, mixed> $data */
    public function createPending(User $user, array $data, string $idempotencyKey): InfaqTransaction
    {
        return DB::transaction(function () use ($user, $data, $idempotencyKey): InfaqTransaction {
            return InfaqTransaction::query()->firstOrCreate([
                'user_id' => $user->id,
                'idempotency_key' => $idempotencyKey,
            ], [
                'public_id' => (string) Str::uuid(),
                'institution_id' => $user->institution_id,
                'purpose' => $data['purpose'],
                'amount' => $data['amount'],
                'currency' => 'IDR',
                'payment_method' => 'bank_transfer',
                'status' => 'pending',
                'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
                'metadata' => ['source' => 'voluntary_infaq_v600'],
            ]);
        }, 3);
    }

    public function verify(InfaqTransaction $transaction, User $reviewer, string $decision): InfaqTransaction
    {
        return DB::transaction(function () use ($transaction, $reviewer, $decision): InfaqTransaction {
            $locked = InfaqTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($locked->status !== 'pending') {
                return $locked;
            }
            $verified = $decision === 'verified';
            $locked->update([
                'status' => $verified ? 'verified' : 'rejected',
                'receipt_number' => $verified ? $this->receiptNumber($locked) : null,
                'verified_by_user_id' => $reviewer->id,
                'verified_at' => now(),
                'paid_at' => $verified ? now() : null,
            ]);

            return $locked->refresh();
        }, 3);
    }

    /** @return Collection<int, object> */
    public function verifiedSummary(?int $institutionId = null): Collection
    {
        return InfaqTransaction::query()
            ->where('status', 'verified')
            ->when($institutionId, fn ($query) => $query->where('institution_id', $institutionId))
            ->selectRaw('purpose, COUNT(*) as transactions_count, SUM(amount) as total_amount')
            ->groupBy('purpose')
            ->orderBy('purpose')
            ->get();
    }

    private function receiptNumber(InfaqTransaction $transaction): string
    {
        return 'INF-'.now()->format('Ym').'-'.Str::upper(substr(str_replace('-', '', (string) $transaction->public_id), 0, 10));
    }
}
