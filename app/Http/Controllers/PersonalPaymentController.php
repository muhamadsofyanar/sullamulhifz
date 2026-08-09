<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Services\PaymentLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalPaymentController extends Controller
{
    public function __construct(private readonly PaymentLedgerService $ledger) {}

    public function index(Request $request): View
    {
        return view('personal.payments', [
            'destination' => $this->ledger->bankTransferDestination(),
            'transactions' => PaymentTransaction::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('user_id', $request->user()->id)->latest()->limit(30)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'purpose' => ['required', Rule::in(['program_fee', 'registration', 'donation', 'other'])],
            'amount' => ['required', 'numeric', 'min:1000', 'max:100000000'],
            'sender_name' => ['required', 'string', 'max:120'],
            'transfer_note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->ledger->createBankTransferPending(
            $request->user()->institution,
            $request->user(),
            $data['purpose'],
            $data['amount'],
            ['sender_name' => $data['sender_name'], 'transfer_note' => $data['transfer_note'] ?? null],
        );

        return back()->with('success', 'Konfirmasi transfer tercatat dan menunggu pemeriksaan admin.');
    }
}
