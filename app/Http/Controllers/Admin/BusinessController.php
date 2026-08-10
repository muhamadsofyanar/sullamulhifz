<?php

namespace App\Http\Controllers\Admin;

/** @phase 5.0 Business, Payment & Integrations */

use App\Http\Controllers\Controller;
use App\Models\BillingInvoice;
use App\Models\BillingPlan;
use App\Models\BillingSubscription;
use App\Models\ActivityLog;
use App\Models\IntegrationConnection;
use App\Models\PaymentTransaction;
use App\Services\BusinessBillingService;
use App\Services\PaymentLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function __construct(
        private readonly PaymentLedgerService $payments,
        private readonly BusinessBillingService $billing,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $institutionId = (int) $user->institution_id;
        $global = $user->hasRole('superadmin');

        $invoices = BillingInvoice::query()->with(['user', 'plan', 'institution']);
        $subscriptions = BillingSubscription::query()->with(['user', 'plan', 'institution']);
        $payments = PaymentTransaction::query()->with(['user', 'billingInvoice', 'institution']);
        $integrations = IntegrationConnection::query()->with('institution');

        if (! $global) {
            $invoices->where('institution_id', $institutionId);
            $subscriptions->where('institution_id', $institutionId);
            $payments->where('institution_id', $institutionId);
            $integrations->where('institution_id', $institutionId);
        }

        return view('admin.business.index', [
            'globalView' => $global,
            'plans' => BillingPlan::query()
                ->where(fn ($query) => $query->whereNull('institution_id')->orWhere('institution_id', $institutionId))
                ->orderBy('sort_order')->get(),
            'invoices' => $invoices->latest()->limit(100)->get(),
            'subscriptions' => $subscriptions->latest()->limit(100)->get(),
            'payments' => $payments->latest()->limit(100)->get(),
            'integrations' => $integrations->orderBy('provider')->limit(100)->get(),
        ]);
    }

    public function updatePlan(Request $request, BillingPlan $plan): RedirectResponse
    {
        abort_unless(
            $request->user()->hasRole('superadmin') ||
            ($plan->institution_id !== null && (int) $plan->institution_id === (int) $request->user()->institution_id),
            403,
        );
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'price' => ['required', 'numeric', 'min:0', 'max:1000000000'],
        ]);
        $before = ['status' => $plan->status, 'price' => (string) $plan->price];
        $plan->update($data);
        ActivityLog::create([
            'institution_id' => $request->user()->institution_id,
            'user_id' => $request->user()->id,
            'action' => 'billing.plan.updated',
            'subject_type' => 'billing_plan',
            'subject_id' => $plan->id,
            'old_values' => $before,
            'new_values' => ['status' => $plan->status, 'price' => (string) $plan->price],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Paket bisnis diperbarui.');
    }

    public function payment(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        abort_unless($transaction->status === 'pending', 409, 'Pembayaran sudah diproses dan tidak dapat direview ulang dari layar ini.');
        $user = $request->user();
        abort_unless($user->hasRole('superadmin') || (int) $transaction->institution_id === (int) $user->institution_id, 403);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['paid', 'rejected'])],
            'reason' => ['nullable', 'required_if:decision,rejected', 'string', 'max:1000'],
        ]);

        $oldStatus = $transaction->status;
        if ($data['decision'] === 'paid') {
            $this->payments->markPaid(
                $transaction,
                'manual_bank_transfer',
                'MANUAL-'.$transaction->id.'-'.now()->format('YmdHis'),
                ['verified_by_user_id' => $user->id],
            );
            $transaction->update([
                'verified_by_user_id' => $user->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);
            $this->billing->syncFromVerifiedPayment($transaction->refresh());
        } else {
            $transaction->update([
                'status' => 'rejected',
                'verified_by_user_id' => $user->id,
                'verified_at' => now(),
                'rejection_reason' => $data['reason'],
            ]);
            $this->billing->syncFromRejectedPayment($transaction->refresh());
        }

        ActivityLog::create([
            'institution_id' => $transaction->institution_id,
            'user_id' => $user->id,
            'action' => 'billing.payment.reviewed',
            'subject_type' => 'payment_transaction',
            'subject_id' => $transaction->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => $transaction->fresh()->status,
                'billing_invoice_id' => $transaction->billing_invoice_id,
                'decision' => $data['decision'],
            ],
            'reason' => $data['reason'] ?? null,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Keputusan pembayaran tersimpan dan lifecycle tagihan diperbarui.');
    }
}
