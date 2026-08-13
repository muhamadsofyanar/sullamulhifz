<?php

namespace App\Services;

use App\Models\InfaqAllocation;
use App\Models\InfaqAllocationPolicy;
use App\Models\InfaqTransaction;
use App\Models\Institution;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InfaqAllocationService
{
    public const DEFAULT_BASIS_POINTS = [
        'teacher_development' => 4000,
        'foundation_operations' => 3000,
        'technology' => 2000,
        'scholarship' => 1000,
    ];

    public function __construct(private readonly InfaqLedgerService $ledger) {}

    public function activePolicy(int $institutionId): InfaqAllocationPolicy
    {
        $policy = InfaqAllocationPolicy::query()->with('items')
            ->where('institution_id', $institutionId)->where('status', 'active')
            ->where('effective_from', '<=', now())->latest('version')->first();
        if ($policy) {
            return $policy;
        }

        return DB::transaction(function () use ($institutionId): InfaqAllocationPolicy {
            Institution::query()->whereKey($institutionId)->lockForUpdate()->firstOrFail();
            $existing = InfaqAllocationPolicy::query()->with('items')->where('institution_id', $institutionId)->latest('version')->first();
            if ($existing) {
                return $existing;
            }
            $policy = InfaqAllocationPolicy::create([
                'institution_id' => $institutionId, 'version' => 1, 'effective_from' => now(),
                'status' => 'active', 'change_reason' => 'Kebijakan bawaan v6.1.0',
            ]);
            foreach (self::DEFAULT_BASIS_POINTS as $category => $basisPoints) {
                $policy->items()->create(['category' => $category, 'basis_points' => $basisPoints]);
            }

            return $policy->load('items');
        }, 3);
    }

    /** @param array<string,int> $basisPoints */
    public function replacePolicy(User $actor, array $basisPoints, string $reason): InfaqAllocationPolicy
    {
        $institutionId = (int) $actor->institution_id;
        $expected = array_keys(self::DEFAULT_BASIS_POINTS);
        sort($expected);
        $actual = array_keys($basisPoints);
        sort($actual);
        if ($actual !== $expected || array_sum($basisPoints) !== 10000 || collect($basisPoints)->contains(fn ($value) => ! is_int($value) || $value < 0 || $value > 10000)) {
            throw ValidationException::withMessages(['allocations' => 'Empat kategori wajib tersedia dan total alokasi harus tepat 100%.']);
        }

        return DB::transaction(function () use ($actor, $institutionId, $basisPoints, $reason): InfaqAllocationPolicy {
            Institution::query()->whereKey($institutionId)->lockForUpdate()->firstOrFail();
            $version = ((int) InfaqAllocationPolicy::query()->where('institution_id', $institutionId)->max('version')) + 1;
            InfaqAllocationPolicy::query()->where('institution_id', $institutionId)->where('status', 'active')->update(['status' => 'superseded']);
            $policy = InfaqAllocationPolicy::create([
                'institution_id' => $institutionId, 'version' => $version, 'effective_from' => now(),
                'status' => 'active', 'change_reason' => $reason, 'created_by_user_id' => $actor->id,
            ]);
            foreach ($basisPoints as $category => $points) {
                $policy->items()->create(['category' => $category, 'basis_points' => $points]);
            }
            ActivityLog::create([
                'institution_id' => $institutionId, 'user_id' => $actor->id, 'action' => 'infaq.policy_changed',
                'subject_type' => $policy::class, 'subject_id' => $policy->id,
                'old_values' => ['version' => $version - 1], 'new_values' => ['version' => $version, 'basis_points' => $basisPoints],
                'reason' => $reason, 'created_at' => now(),
            ]);

            return $policy->load('items');
        }, 3);
    }

    public function allocate(InfaqTransaction $transaction, ?int $actorId = null): void
    {
        abort_unless($transaction->status === 'verified' && $transaction->institution_id, 422, 'Hanya transaksi terverifikasi yang dapat dialokasikan.');
        Institution::query()->whereKey($transaction->institution_id)->lockForUpdate()->firstOrFail();
        if ($transaction->allocations()->exists()) {
            return;
        }
        $policy = $transaction->purpose === 'general' ? $this->activePolicy((int) $transaction->institution_id) : null;
        $items = $policy
            ? $policy->items->mapWithKeys(fn ($item) => [$item->category => (int) $item->basis_points])->all()
            : [$transaction->purpose => 10000];
        $totalCents = (int) round((float) $transaction->amount * 100);
        $allocatedCents = 0;
        $lastCategory = array_key_last($items);
        foreach ($items as $category => $points) {
            $cents = $category === $lastCategory ? $totalCents - $allocatedCents : intdiv($totalCents * $points, 10000);
            $allocatedCents += $cents;
            $allocation = InfaqAllocation::create([
                'institution_id' => $transaction->institution_id, 'infaq_transaction_id' => $transaction->id,
                'policy_id' => $policy?->id, 'category' => $category, 'basis_points' => $points,
                'amount' => $cents / 100, 'source' => 'verified_transaction',
            ]);
            $this->ledger->creditAllocation($allocation, $actorId);
        }
    }
}
