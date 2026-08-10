<?php

namespace App\Services;

/** @phase 5.0 Business, Payment & Integrations; @phase 6.0 Free core access */

use App\Models\BillingInvoice;
use App\Models\BillingPlan;
use App\Models\BillingSubscription;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BusinessBillingService
{
    public function __construct(private readonly PaymentLedgerService $ledger) {}

    public function catalogFor(User $user): Collection
    {
        return BillingPlan::query()
            ->where('status', 'active')
            ->where(function ($query) use ($user): void {
                $query->whereNull('institution_id')
                    ->orWhere('institution_id', $user->institution_id);
            })
            ->where(function ($query) use ($user): void {
                $query->where('audience', 'all');
                if ($user->hasRole('personal')) {
                    $query->orWhere('audience', 'personal');
                }
                if ($user->hasRole('teacher')) {
                    $query->orWhere('audience', 'teacher');
                }
                if ($user->hasAnyRole(['institution_admin', 'superadmin'])) {
                    $query->orWhere('audience', 'institution');
                }
            })
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
    }

    public function activeSubscriptions(User $user): Collection
    {
        return BillingSubscription::query()
            ->with('plan')
            ->where('institution_id', $user->institution_id)
            ->where(function ($query) use ($user): void {
                $query->where(function ($userScope) use ($user): void {
                    $userScope->where('scope_type', 'user')->where('user_id', $user->id);
                })->orWhere('scope_type', 'institution');
            })
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->latest('starts_at')
            ->get();
    }

    public function createSubscriptionInvoice(User $user, BillingPlan $plan): BillingInvoice
    {
        if (! config('sullam.subscriptions_enabled', false)) {
            throw ValidationException::withMessages([
                'plan' => 'Langganan baru sudah ditutup. Seluruh fungsi inti kini gratis; dukungan dapat diberikan secara sukarela melalui menu Infak.',
            ]);
        }
        abort_unless($plan->status === 'active', 404);
        abort_unless($plan->institution_id === null || (int) $plan->institution_id === (int) $user->institution_id, 404);

        $allowedAudiences = ['all'];
        if ($user->hasRole('personal')) $allowedAudiences[] = 'personal';
        if ($user->hasRole('teacher')) $allowedAudiences[] = 'teacher';
        if ($user->hasAnyRole(['institution_admin', 'superadmin'])) $allowedAudiences[] = 'institution';
        abort_unless(in_array($plan->audience, $allowedAudiences, true), 403);

        $institutionScope = $plan->audience === 'institution';

        $active = BillingSubscription::query()
            ->where('institution_id', $user->institution_id)
            ->where('billing_plan_id', $plan->id)
            ->where('scope_type', $institutionScope ? 'institution' : 'user')
            ->when(! $institutionScope, fn ($query) => $query->where('user_id', $user->id))
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->exists();
        if ($active) {
            throw ValidationException::withMessages([
                'plan' => $institutionScope
                    ? 'Paket ini masih aktif untuk lembaga Anda.'
                    : 'Paket ini masih aktif untuk akun Anda.',
            ]);
        }

        $pending = BillingInvoice::query()
            ->where('institution_id', $user->institution_id)
            ->where('billing_plan_id', $plan->id)
            ->where('status', 'pending')
            ->when(! $institutionScope, fn ($query) => $query->where('user_id', $user->id))
            ->where('created_at', '>=', now()->subDay())
            ->first();
        if ($pending) {
            return $pending;
        }

        return DB::transaction(function () use ($user, $plan): BillingInvoice {
            $subscription = BillingSubscription::create([
                'institution_id' => $user->institution_id,
                'user_id' => $user->id,
                'billing_plan_id' => $plan->id,
                'scope_type' => $plan->audience === 'institution' ? 'institution' : 'user',
                'status' => (float) $plan->price <= 0 ? 'active' : 'pending',
                'starts_at' => (float) $plan->price <= 0 ? now() : null,
                'ends_at' => (float) $plan->price <= 0 ? $this->endDate($plan->billing_cycle) : null,
                'auto_renew' => false,
                'entitlement_snapshot' => $plan->entitlements ?? [],
                'metadata' => ['plan_code' => $plan->code, 'created_from' => 'business_center_v500'],
            ]);

            $invoice = BillingInvoice::create([
                'institution_id' => $user->institution_id,
                'user_id' => $user->id,
                'billing_plan_id' => $plan->id,
                'billing_subscription_id' => $subscription->id,
                'invoice_number' => $this->invoiceNumber(),
                'purpose' => 'subscription',
                'subtotal' => $plan->price,
                'total' => $plan->price,
                'currency' => $plan->currency,
                'status' => (float) $plan->price <= 0 ? 'paid' : 'pending',
                'due_at' => (float) $plan->price <= 0 ? now() : now()->addDays(3),
                'paid_at' => (float) $plan->price <= 0 ? now() : null,
                'metadata' => ['plan_code' => $plan->code],
            ]);

            if ((float) $plan->price > 0) {
                $transaction = $this->ledger->createBankTransferPending(
                    $user->institution,
                    $user,
                    'subscription',
                    $plan->price,
                    [
                        'billing_invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'plan_code' => $plan->code,
                    ],
                );
                $transaction->update(['billing_invoice_id' => $invoice->id]);
            }

            return $invoice->refresh()->load(['plan', 'subscription', 'payments']);
        });
    }

    public function syncFromVerifiedPayment(PaymentTransaction $transaction): void
    {
        if ($transaction->status !== 'paid' || ! $transaction->billing_invoice_id) {
            return;
        }

        DB::transaction(function () use ($transaction): void {
            $invoice = BillingInvoice::query()->lockForUpdate()->find($transaction->billing_invoice_id);
            if (! $invoice || (int) $invoice->institution_id !== (int) $transaction->institution_id) {
                return;
            }

            $invoice->update([
                'status' => 'paid',
                'paid_at' => $invoice->paid_at ?? now(),
            ]);

            $subscription = $invoice->subscription;
            if (! $subscription) {
                return;
            }

            $plan = $invoice->plan;
            $subscription->update([
                'status' => 'active',
                'starts_at' => $subscription->starts_at ?? now(),
                'ends_at' => $subscription->ends_at ?? $this->endDate($plan?->billing_cycle ?? 'monthly'),
                'entitlement_snapshot' => $subscription->entitlement_snapshot ?: ($plan?->entitlements ?? []),
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'activated_by_payment_id' => $transaction->id,
                    'activated_at' => now()->toIso8601String(),
                ]),
            ]);
        });
    }

    public function syncFromRejectedPayment(PaymentTransaction $transaction): void
    {
        if ($transaction->status !== 'rejected' || ! $transaction->billing_invoice_id) {
            return;
        }

        DB::transaction(function () use ($transaction): void {
            $invoice = BillingInvoice::query()->lockForUpdate()->find($transaction->billing_invoice_id);
            if (! $invoice || (int) $invoice->institution_id !== (int) $transaction->institution_id) {
                return;
            }

            $invoice->update(['status' => 'rejected']);
            $invoice->subscription?->update(['status' => 'cancelled']);
        });
    }

    public function entitlements(User $user): array
    {
        $historical = $this->activeSubscriptions($user)
            ->flatMap(fn (BillingSubscription $subscription) => $subscription->entitlement_snapshot ?? [])
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->all();

        return collect(array_merge([
            'personal_core', 'learning_hub', 'quran_practice', 'quran_journey',
            'guided_learning', 'academy', 'mentorship', 'guided_review',
            'institution_suite', 'communications', 'reports', 'operations',
        ], $historical))->unique()->values()->all();
    }

    private function endDate(string $cycle)
    {
        return match ($cycle) {
            'yearly' => now()->addYear(),
            'lifetime' => null,
            'weekly' => now()->addWeek(),
            default => now()->addMonth(),
        };
    }

    private function invoiceNumber(): string
    {
        do {
            $number = 'INV-'.now()->format('Ym').'-'.Str::upper(Str::random(8));
        } while (BillingInvoice::query()->where('invoice_number', $number)->exists());

        return $number;
    }
}
