{{-- @phase 5.0 Business, Payment & Integrations --}}
@extends('layouts.app',['pageTitle'=>'Paket & Layanan'])
@section('content')
@php
    $invoiceLabels = [
        'pending' => 'Menunggu pembayaran/verifikasi',
        'paid' => 'Lunas',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];
@endphp
<div class="v530-page">
    <section class="v530-hero">
        <div>
            <span class="personal-kicker">FASE 9 · BUSINESS & PAYMENT</span>
            <h1>Paket dan layanan dalam satu tempat</h1>
            <p>Pilih layanan sesuai kebutuhan. Tagihan transfer memakai rekening resmi yang sama dan aktivasi berbayar baru berlaku setelah pembayaran diverifikasi.</p>
        </div>
        <span class="v530-badge">v5.0</span>
    </section>

    @if($subscriptions->isNotEmpty())
    <section class="card">
        <span class="eyebrow">AKTIF SEKARANG</span>
        <div class="v530-grid v530-grid-3">
            @foreach($subscriptions as $subscription)
            <article class="v530-card">
                <h3>{{ $subscription->plan?->name ?? 'Paket aktif' }}</h3>
                <p>{{ $subscription->ends_at ? 'Aktif sampai '.$subscription->ends_at->translatedFormat('d M Y') : 'Aktif tanpa tanggal berakhir' }}</p>
            </article>
            @endforeach
        </div>
    </section>
    @endif

    <section class="v530-grid v530-grid-2">
        @foreach($plans as $plan)
        <article class="card v530-plan">
            <div class="v530-row"><span class="eyebrow">{{ strtoupper($plan->audience) }}</span><span class="v530-badge">{{ $plan->billing_cycle }}</span></div>
            <h2>{{ $plan->name }}</h2>
            <p>{{ $plan->description }}</p>
            <strong class="v530-price">{{ (float)$plan->price > 0 ? 'Rp'.number_format((float)$plan->price,0,',','.') : 'Gratis' }}</strong>
            @if(!empty($plan->entitlements))
            <ul class="v530-list">
                @foreach($plan->entitlements as $entitlement)
                <li>{{ str($entitlement)->replace('_',' ')->headline() }}</li>
                @endforeach
            </ul>
            @endif
            <form method="post" action="{{ route('business.subscribe',$plan) }}">@csrf
                <button class="button primary" type="submit">{{ (float)$plan->price > 0 ? 'Buat tagihan' : 'Aktifkan gratis' }}</button>
            </form>
        </article>
        @endforeach
    </section>

    <section class="v530-grid v530-grid-2">
        <article class="card">
            <span class="eyebrow">REKENING RESMI</span>
            <h2>{{ $destination['bank_name'] }}</h2>
            <strong class="v530-bank-number">{{ $destination['account_number'] }}</strong>
            <p>a.n. {{ $destination['account_name'] }}</p>
            <div class="personal-guardrail">Sullamul Hifz tidak pernah meminta OTP, PIN, atau kata sandi perbankan.</div>
        </article>
        <article class="card">
            <span class="eyebrow">HAK LAYANAN PAKET</span>
            <h2>Entitlement yang tercatat</h2>
            @if($entitlements === [])
                <p class="muted">Belum ada entitlement berbayar. Modul gratis dan akses yang diberikan lembaga tetap mengikuti hak akun Anda.</p>
            @else
                <div class="v530-chips">@foreach($entitlements as $item)<span>{{ str($item)->replace('_',' ')->headline() }}</span>@endforeach</div>
            @endif
        <div class="personal-guardrail">Entitlement paket tidak melewati permission, enrollment, consent Ustadz/Keluarga, atau aturan workspace yang sudah berlaku.</div>
        </article>
    </section>

    <section class="card">
        <span class="eyebrow">TAGIHAN SAYA</span>
        <h2>Riwayat invoice</h2>
        <div class="v530-listing">
            @forelse($invoices as $invoice)
            <article>
                <div><strong>{{ $invoice->invoice_number }}</strong><small>{{ $invoice->plan?->name ?? str($invoice->purpose)->headline() }} · {{ $invoice->created_at->translatedFormat('d M Y H:i') }}</small></div>
                <div class="v530-listing-right"><strong>Rp{{ number_format((float)$invoice->total,0,',','.') }}</strong><span class="badge status-{{ $invoice->status }}">{{ $invoiceLabels[$invoice->status] ?? $invoice->status }}</span></div>
            </article>
            @empty
            <p class="muted">Belum ada tagihan.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
