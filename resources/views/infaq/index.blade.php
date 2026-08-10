{{-- @phase 6.0 Voluntary infaq --}}
@extends('layouts.app',['pageTitle'=>'Dukung melalui Infak'])
@section('content')
@php($statusLabels=['pending'=>'Menunggu verifikasi','verified'=>'Terverifikasi','rejected'=>'Ditolak','refunded'=>'Dikembalikan'])
<div class="page-head"><div><span class="eyebrow">GRATIS · SUKARELA · TRANSPARAN</span><h1>Aplikasi tetap gratis, infak membantu keberlanjutan.</h1><p>Tidak berinfak tidak mengurangi fitur, pelayanan, atau perhatian ustadz. Dukungan hanya dilakukan atas kesadaran sendiri.</p></div><span class="phase-chip">v6.0</span></div>
<div class="grid two">
<section class="card"><h2>Catat dukungan sukarela</h2>
    <form class="stack compact" method="post" action="{{ route('infaq.store') }}">@csrf
        <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <label>Tujuan<select name="purpose" required>@foreach($purposes as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
        <label>Jumlah (Rp)<input type="number" name="amount" min="1000" step="1000" required></label>
        <label class="check-row"><input type="checkbox" name="is_anonymous" value="1"><span>Tampilkan sebagai anonim pada laporan publik.</span></label>
        <label class="check-row"><input type="checkbox" name="voluntary_acknowledgement" value="1" required><span>Saya memahami dukungan ini sukarela dan bukan syarat memakai aplikasi.</span></label>
        <button class="button primary">Catat niat infak</button>
    </form>
</section>
<section class="card"><span class="eyebrow">REKENING RESMI</span><h2>{{ $destination['bank_name'] }}</h2><strong class="v530-bank-number">{{ $destination['account_number'] }}</strong><p>a.n. {{ $destination['account_name'] }}</p><div class="alert">Jangan pernah memberikan OTP, PIN, atau kata sandi perbankan. Verifikasi dilakukan setelah transfer terlihat pada rekening resmi.</div></section>
</div>
<section class="card"><div class="section-head"><div><h2>Laporan dana terverifikasi</h2><p class="hint">Ringkasan ini hanya menghitung transaksi yang sudah diverifikasi.</p></div></div><div class="stats-grid four">@forelse($summary as $item)<div class="stat-card"><span>{{ $purposes[$item->purpose] ?? $item->purpose }}</span><strong>Rp{{ number_format((float)$item->total_amount,0,',','.') }}</strong><small>{{ $item->transactions_count }} transaksi</small></div>@empty<div class="empty">Belum ada infak terverifikasi.</div>@endforelse</div></section>
<section class="card"><h2>Riwayat dukungan saya</h2>@forelse($transactions as $transaction)<div class="list-row"><div><strong>Rp{{ number_format((float)$transaction->amount,0,',','.') }} · {{ $purposes[$transaction->purpose] ?? $transaction->purpose }}</strong><small>{{ $transaction->created_at->format('d M Y H:i') }} · {{ $statusLabels[$transaction->status] ?? $transaction->status }}</small>@if($transaction->receipt_number)<p>Bukti penerimaan: {{ $transaction->receipt_number }}</p>@endif</div></div>@empty<p class="empty">Belum ada dukungan. Akses aplikasi tetap lengkap.</p>@endforelse</section>
@endsection
