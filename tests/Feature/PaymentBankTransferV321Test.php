<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Services\PaymentLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentBankTransferV321Test extends TestCase
{
    use RefreshDatabase;

    public function test_official_bank_destination_is_snapshotted_on_pending_transfer(): void
    {
        $institution = Institution::create([
            'name' => 'TPA Al-Insyirah',
            'code' => 'ALINSYIRAH',
            'slug' => 'al-insyirah-v321',
            'status' => 'active',
        ]);

        $transaction = app(PaymentLedgerService::class)->createBankTransferPending(
            $institution,
            null,
            'program_fee',
            100000,
        );

        $this->assertSame('bank_transfer', $transaction->metadata['payment_method']);
        $this->assertSame([
            'bank_name' => 'BSI (Bank Syariah Indonesia)',
            'account_name' => 'YYS INSAN QURAN MADANI',
            'account_number' => '7350451147',
        ], $transaction->metadata['payment_destination']);
    }
}
