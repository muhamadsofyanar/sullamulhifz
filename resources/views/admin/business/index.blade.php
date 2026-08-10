{{-- @phase 5.0 Business, Payment & Integrations; @phase 6.0 Legacy billing archive --}}
@extends('layouts.app',['pageTitle'=>'Pusat Bisnis'])
@section('content')
<div class="v530-page">
    <section class="v530-hero">
        <div><span class="personal-kicker">ARSIP v5 · BUSINESS CONTROL</span><h1>Riwayat Paket & Integrasi</h1><p>Mulai v6.0, paket, subscription, invoice, dan pembayaran langganan dipertahankan sebagai histori dan tidak menentukan akses fungsi inti. Superadmin melihat ledger lintas workspace; admin lembaga hanya ruangnya sendiri.</p></div>
        <span class="v530-badge">v5.0</span>
    </section>

    <section class="card">
        <span class="eyebrow">KATALOG</span><h2>Paket layanan</h2>
        <div class="v530-grid v530-grid-2">
            @foreach($plans as $plan)
            <article class="v530-card">
                <div class="v530-row"><strong>{{ $plan->name }}</strong><small>{{ $plan->code }}</small></div>
                <p>{{ $plan->description }}</p>
                @if($globalView || $plan->institution_id)
                <form method="post" action="{{ route('admin.business.plans.update',$plan) }}" class="stack">@csrf @method('PUT')
                    <label>Harga<input type="number" name="price" min="0" step="1000" value="{{ (float)$plan->price }}" required></label>
                    <label>Status<select name="status"><option value="active" @selected($plan->status==='active')>Aktif</option><option value="inactive" @selected($plan->status==='inactive')>Nonaktif</option></select></label>
                    <button class="button" type="submit">Simpan paket</button>
                </form>
                @else
                <div class="v530-row"><strong>Rp{{ number_format((float)$plan->price,0,',','.') }}</strong><span class="badge status-{{ $plan->status }}">{{ $plan->status }}</span></div>
                @endif
            </article>
            @endforeach
        </div>
    </section>

    <section class="v530-grid v530-grid-2">
        <article class="card"><span class="eyebrow">SUBSCRIPTION</span><h2>{{ $subscriptions->count() }} subscription terbaru</h2><div class="v530-listing">@forelse($subscriptions as $subscription)<article><div><strong>{{ $subscription->user?->name ?? 'Lembaga' }}</strong><small>{{ $subscription->plan?->name }}@if($globalView) · {{ $subscription->institution?->name }}@endif</small></div><span class="badge status-{{ $subscription->status }}">{{ $subscription->status }}</span></article>@empty<p class="muted">Belum ada subscription.</p>@endforelse</div></article>
        <article class="card"><span class="eyebrow">INVOICE</span><h2>{{ $invoices->count() }} invoice terbaru</h2><div class="v530-listing">@forelse($invoices as $invoice)<article><div><strong>{{ $invoice->invoice_number }}</strong><small>{{ $invoice->user?->name }} · {{ $invoice->plan?->name }}@if($globalView) · {{ $invoice->institution?->name }}@endif</small></div><div class="v530-listing-right"><strong>Rp{{ number_format((float)$invoice->total,0,',','.') }}</strong><span class="badge status-{{ $invoice->status }}">{{ $invoice->status }}</span></div></article>@empty<p class="muted">Belum ada invoice.</p>@endforelse</div></article>
    </section>

    <section class="card">
        <span class="eyebrow">PAYMENT LEDGER</span><h2>Pembayaran terbaru</h2>
        <div class="v530-listing">
            @forelse($payments as $payment)
            <article>
                <div><strong>Rp{{ number_format((float)$payment->amount,0,',','.') }}</strong><small>{{ $payment->user?->name }} · {{ $payment->billingInvoice?->invoice_number ?? $payment->purpose }}@if($globalView) · {{ $payment->institution?->name }}@endif</small>@if($payment->rejection_reason)<p>{{ $payment->rejection_reason }}</p>@endif</div>
                <div class="v530-listing-right">
                    <span class="badge status-{{ $payment->status }}">{{ $payment->status }}</span>
                    @if($payment->status === 'pending')
                    <form method="post" action="{{ route('admin.business.payments.update',$payment) }}" class="v530-inline-actions">@csrf @method('PUT')<input type="hidden" name="decision" value="paid"><button class="button" type="submit">Verifikasi</button></form>
                    <form method="post" action="{{ route('admin.business.payments.update',$payment) }}" class="v530-inline-actions">@csrf @method('PUT')<input type="hidden" name="decision" value="rejected"><input name="reason" placeholder="Alasan penolakan" required><button class="button secondary" type="submit">Tolak</button></form>
                    @endif
                </div>
            </article>
            @empty<p class="muted">Belum ada pembayaran.</p>@endforelse
        </div>
    </section>

    <section class="card"><span class="eyebrow">INTEGRATIONS</span><h2>Koneksi layanan</h2><div class="v530-listing">@forelse($integrations as $integration)<article><div><strong>{{ $integration->display_name }}</strong><small>{{ $integration->provider }}@if($globalView) · {{ $integration->institution?->name }}@endif</small></div><span class="badge status-{{ $integration->status }}">{{ $integration->status }}</span></article>@empty<p class="muted">Belum ada koneksi integrasi.</p>@endforelse</div></section>
</div>
@endsection
