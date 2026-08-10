<?php

namespace App\Http\Controllers;

/** @phase 5.0 Business, Payment & Integrations */

use App\Models\BillingInvoice;
use App\Models\BillingPlan;
use App\Services\BusinessBillingService;
use App\Services\PaymentLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalBusinessController extends Controller
{
    public function __construct(
        private readonly BusinessBillingService $billing,
        private readonly PaymentLedgerService $ledger,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('personal.business', [
            'plans' => $this->billing->catalogFor($user),
            'subscriptions' => $this->billing->activeSubscriptions($user),
            'entitlements' => $this->billing->entitlements($user),
            'invoices' => BillingInvoice::query()
                ->with(['plan', 'payments', 'subscription'])
                ->where('institution_id', $user->institution_id)
                ->where(function ($query) use ($user): void {
                    $query->where('user_id', $user->id);
                    if ($user->hasAnyRole(['institution_admin', 'superadmin'])) {
                        $query->orWhereHas('subscription', fn ($subscription) => $subscription->where('scope_type', 'institution'));
                    }
                })
                ->latest()
                ->limit(30)
                ->get(),
            'destination' => $this->ledger->bankTransferDestination(),
        ]);
    }

    public function subscribe(Request $request, BillingPlan $plan): RedirectResponse
    {
        $invoice = $this->billing->createSubscriptionInvoice($request->user(), $plan);

        return back()->with('success', (float) $invoice->total <= 0
            ? 'Paket gratis berhasil diaktifkan.'
            : "Tagihan {$invoice->invoice_number} dibuat. Transfer ke rekening resmi lalu tunggu verifikasi admin.");
    }
}
