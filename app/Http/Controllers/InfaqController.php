<?php

namespace App\Http\Controllers;

/** @phase 6.0 Voluntary infaq */

use App\Models\InfaqTransaction;
use App\Services\InfaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InfaqController extends Controller
{
    public function __construct(private readonly InfaqService $infaq) {}

    public function index(Request $request): View
    {
        return view('infaq.index', [
            'transactions' => InfaqTransaction::query()->where('user_id', $request->user()->id)->latest()->limit(30)->get(),
            'summary' => $this->infaq->verifiedSummary($request->user()->institution_id),
            'destination' => config('sullam.payment.bank_transfer'),
            'purposes' => config('sullam.infaq.purposes'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $purposeKeys = array_keys((array) config('sullam.infaq.purposes'));
        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid', 'max:80'],
            'purpose' => ['required', Rule::in($purposeKeys)],
            'amount' => ['required', 'numeric', 'min:1000', 'max:1000000000'],
            'is_anonymous' => ['nullable', 'boolean'],
            'voluntary_acknowledgement' => ['accepted'],
        ]);
        $transaction = $this->infaq->createPending($request->user(), $data, $data['idempotency_key']);

        return back()->with('success', "Niat infak {$transaction->public_id} tercatat. Transfer bersifat sukarela dan akses aplikasi Anda tidak berubah.");
    }
}
