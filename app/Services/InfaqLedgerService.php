<?php

namespace App\Services;

use App\Models\InfaqAllocation;
use App\Models\InfaqFundTransfer;
use App\Models\InfaqLedgerEntry;
use App\Models\InfaqRealisation;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InfaqLedgerService
{
    public function balance(int $institutionId, string $category): string
    {
        return number_format((float) InfaqLedgerEntry::query()
            ->where('institution_id', $institutionId)
            ->where('category', $category)
            ->sum('amount'), 2, '.', '');
    }

    public function creditAllocation(InfaqAllocation $allocation, ?int $actorId = null): InfaqLedgerEntry
    {
        return InfaqLedgerEntry::query()->firstOrCreate([
            'allocation_id' => $allocation->id,
            'entry_type' => 'receipt_credit',
        ], [
            'entry_uuid' => (string) Str::uuid(),
            'institution_id' => $allocation->institution_id,
            'infaq_transaction_id' => $allocation->infaq_transaction_id,
            'category' => $allocation->category,
            'amount' => $allocation->amount,
            'occurred_at' => now(),
            'created_by_user_id' => $actorId,
            'metadata' => ['source' => $allocation->source],
        ]);
    }

    public function debitRealisation(InfaqRealisation $realisation, User $actor): InfaqLedgerEntry
    {
        return DB::transaction(function () use ($realisation, $actor): InfaqLedgerEntry {
            Institution::query()->lockForUpdate()->findOrFail($realisation->institution_id);
            $existing = InfaqLedgerEntry::query()->where('realisation_id', $realisation->id)->where('entry_type', 'realisation_debit')->first();
            if ($existing) {
                return $existing;
            }
            $balance = (float) InfaqLedgerEntry::query()
                ->where('institution_id', $realisation->institution_id)
                ->where('category', $realisation->category)
                ->lockForUpdate()
                ->get()->sum('amount');
            if ($balance < (float) $realisation->amount) {
                throw ValidationException::withMessages(['amount' => 'Saldo kategori tidak mencukupi untuk realisasi ini.']);
            }

            return InfaqLedgerEntry::create([
                'entry_uuid' => (string) Str::uuid(), 'institution_id' => $realisation->institution_id,
                'realisation_id' => $realisation->id, 'entry_type' => 'realisation_debit',
                'category' => $realisation->category, 'amount' => -1 * (float) $realisation->amount,
                'occurred_at' => now(), 'created_by_user_id' => $actor->id,
                'metadata' => ['program_name' => $realisation->program_name],
            ]);
        }, 3);
    }

    public function approveTransfer(InfaqFundTransfer $transfer, User $approver, string $reviewNote): InfaqFundTransfer
    {
        return DB::transaction(function () use ($transfer, $approver, $reviewNote): InfaqFundTransfer {
            $locked = InfaqFundTransfer::query()->lockForUpdate()->findOrFail($transfer->id);
            abort_unless((int) $locked->institution_id === (int) $approver->institution_id || $approver->hasRole('superadmin'), 404);
            Institution::query()->lockForUpdate()->findOrFail($locked->institution_id);
            abort_unless($locked->status === 'submitted', 422, 'Pemindahan ini sudah selesai diperiksa.');
            abort_if((int) $locked->created_by_user_id === (int) $approver->id, 422, 'Pembuat transfer tidak boleh menyetujui transfernya sendiri.');
            if ((float) $this->balance((int) $locked->institution_id, $locked->from_category) < (float) $locked->amount) {
                throw ValidationException::withMessages(['amount' => 'Saldo kategori asal tidak mencukupi.']);
            }
            foreach ([[$locked->from_category, -1 * (float) $locked->amount, 'transfer_debit'], [$locked->to_category, (float) $locked->amount, 'transfer_credit']] as [$category, $amount, $type]) {
                InfaqLedgerEntry::firstOrCreate([
                    'fund_transfer_id' => $locked->id, 'entry_type' => $type,
                ], [
                    'entry_uuid' => (string) Str::uuid(), 'institution_id' => $locked->institution_id,
                    'category' => $category, 'amount' => $amount, 'occurred_at' => now(),
                    'created_by_user_id' => $approver->id, 'metadata' => ['reason' => $locked->reason],
                ]);
            }
            $locked->update(['status' => 'approved', 'approved_by_user_id' => $approver->id, 'approved_at' => now(), 'review_note' => $reviewNote]);

            return $locked->refresh();
        }, 3);
    }

    public function correction(User $actor, string $category, float $amount, string $reason, string $sourcePeriod): InfaqLedgerEntry
    {
        return DB::transaction(function () use ($actor, $category, $amount, $reason, $sourcePeriod): InfaqLedgerEntry {
            Institution::query()->lockForUpdate()->findOrFail($actor->institution_id);
            if ($amount < 0 && (float) $this->balance((int) $actor->institution_id, $category) < abs($amount)) {
                throw ValidationException::withMessages(['amount' => 'Jurnal koreksi debit tidak boleh membuat saldo kategori negatif.']);
            }

            return InfaqLedgerEntry::create([
                'entry_uuid' => (string) Str::uuid(), 'institution_id' => $actor->institution_id,
                'entry_type' => $amount < 0 ? 'correction_debit' : 'correction_credit',
                'category' => $category, 'amount' => $amount, 'occurred_at' => now(),
                'created_by_user_id' => $actor->id,
                'metadata' => ['reason' => $reason, 'corrects_period' => $sourcePeriod],
            ]);
        }, 3);
    }
}
