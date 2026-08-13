<?php

namespace App\Services;

use App\Models\InfaqReceiptSequence;
use App\Models\InfaqTransaction;
use Illuminate\Support\Facades\DB;

class InfaqReceiptService
{
    public function nextNumber(InfaqTransaction $transaction): string
    {
        abort_unless($transaction->institution_id, 422, 'Transaksi harus terhubung dengan ruang lembaga.');
        $year = (int) now()->format('Y');

        return DB::transaction(function () use ($transaction, $year): string {
            InfaqReceiptSequence::query()->firstOrCreate(
                ['institution_id' => $transaction->institution_id, 'year' => $year],
                ['last_number' => 0],
            );
            $sequence = InfaqReceiptSequence::query()
                ->where('institution_id', $transaction->institution_id)
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();
            $sequence->increment('last_number');

            return sprintf('INF-%d-%d-%06d', $transaction->institution_id, $year, $sequence->fresh()->last_number);
        }, 3);
    }
}
