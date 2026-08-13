<?php

namespace App\Services;

/** @phase 6.0 Voluntary infaq service */

use App\Models\InfaqTransaction;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InfaqService
{
    public function __construct(
        private readonly InfaqReceiptService $receipts,
        private readonly InfaqAllocationService $allocations,
    ) {}

    /** @param array<string, mixed> $data */
    public function createPending(User $user, array $data, string $idempotencyKey): InfaqTransaction
    {
        return DB::transaction(function () use ($user, $data, $idempotencyKey): InfaqTransaction {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $showDonorName = array_key_exists('show_donor_name', $data)
                ? (bool) $data['show_donor_name']
                : ! (bool) ($data['is_anonymous'] ?? true);

            $transaction = InfaqTransaction::query()->firstOrCreate([
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
                'is_anonymous' => ! $showDonorName,
                'show_donor_name' => $showDonorName,
                'donor_consent_at' => $showDonorName ? now() : null,
                'transfer_proof_media_asset_id' => $data['transfer_proof_media_asset_id'] ?? null,
                'metadata' => ['source' => 'voluntary_infaq_v600'],
            ]);

            if (! $transaction->wasRecentlyCreated) {
                $samePayload = $transaction->purpose === (string) $data['purpose']
                    && round((float) $transaction->amount, 2) === round((float) $data['amount'], 2)
                    && $transaction->show_donor_name === $showDonorName;
                if (! $samePayload) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => 'Kunci transaksi ini sudah dipakai untuk infak yang berbeda. Muat ulang halaman sebelum mencoba lagi.',
                    ]);
                }
            }

            return $transaction;
        }, 3);
    }

    public function verify(InfaqTransaction $transaction, User $reviewer, string $decision, ?string $note = null): InfaqTransaction
    {
        return DB::transaction(function () use ($transaction, $reviewer, $decision, $note): InfaqTransaction {
            $locked = InfaqTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            abort_unless(
                $reviewer->hasRole('superadmin')
                || (int) $locked->institution_id === (int) $reviewer->institution_id,
                404,
            );
            if ($locked->status !== 'pending') {
                return $locked;
            }
            $verified = $decision === 'verified';
            $locked->update([
                'status' => $verified ? 'verified' : 'rejected',
                'receipt_number' => $verified ? $this->receipts->nextNumber($locked) : null,
                'verified_by_user_id' => $reviewer->id,
                'verified_at' => now(),
                'paid_at' => $verified ? now() : null,
                'mutation_match_note' => $verified ? ($note ?: 'Mutasi rekening telah dicocokkan oleh admin.') : null,
                'rejection_reason' => $verified ? null : ($note ?: 'Transaksi tidak dapat dicocokkan.'),
            ]);

            if ($verified) {
                $this->allocations->allocate($locked->refresh(), $reviewer->id);
            }
            ActivityLog::create([
                'institution_id' => $locked->institution_id, 'user_id' => $reviewer->id,
                'action' => 'infaq.transaction_'.$locked->status, 'subject_type' => $locked::class,
                'subject_id' => $locked->id, 'old_values' => ['status' => 'pending'],
                'new_values' => ['status' => $locked->status, 'receipt_number' => $locked->receipt_number],
                'reason' => $note, 'created_at' => now(),
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

}
