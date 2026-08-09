@extends('layouts.app',['pageTitle'=>'Pembayaran Program'])
@section('content')
@php($statusLabels = ['pending'=>'Menunggu verifikasi','paid'=>'Terverifikasi','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'])
<div class="personal-page personal-v4-page">
    <section class="personal-v4-hero compact"><div><span class="personal-kicker">PEMBAYARAN PROGRAM</span><h1>Transfer ke rekening resmi yayasan</h1><p>Nomor tujuan ditampilkan dari konfigurasi resmi dan disimpan sebagai snapshot pada setiap transaksi.</p></div></section>
    <section class="personal-v4-grid">
        <div class="card v4-bank-card"><span>Bank Tujuan</span><h2>{{ $destination['bank_name'] }}</h2><small>Nomor Rekening Tujuan</small><strong>{{ $destination['account_number'] }}</strong><p>a.n. {{ $destination['account_name'] }}</p><div class="personal-guardrail">Periksa kembali nama penerima sebelum transfer. Admin tidak pernah meminta OTP, PIN, atau kata sandi.</div></div>
        <div class="card"><span class="eyebrow">KONFIRMASI TRANSFER</span><h2>Catat pembayaran Anda</h2><form method="post" action="{{ route('personal.payments.store') }}" class="stack">@csrf
            <label>Keperluan<select name="purpose" required><option value="program_fee">Biaya program</option><option value="registration">Pendaftaran</option><option value="donation">Donasi</option><option value="other">Lainnya</option></select></label>
            <label>Nominal<input type="number" name="amount" min="1000" max="100000000" step="1000" required></label>
            <label>Nama pengirim<input name="sender_name" maxlength="120" required></label>
            <label>Catatan transfer <span class="muted">opsional</span><textarea name="transfer_note" rows="3" maxlength="500"></textarea></label>
            <button class="button primary" type="submit">Kirim konfirmasi</button>
        </form></div>
    </section>
    <section class="card"><div class="section-head"><div><span class="eyebrow">RIWAYAT</span><h2>Status pembayaran</h2></div></div><div class="v4-payment-list">@forelse($transactions as $transaction)<article><div><strong>Rp{{ number_format((float)$transaction->amount,0,',','.') }}</strong><small>{{ str($transaction->purpose)->replace('_',' ')->headline() }} · {{ $transaction->created_at->translatedFormat('d M Y H:i') }}</small></div><span class="badge status-{{ $transaction->status }}">{{ $statusLabels[$transaction->status] ?? $transaction->status }}</span>@if($transaction->rejection_reason)<p>{{ $transaction->rejection_reason }}</p>@endif</article>@empty<p class="muted">Belum ada konfirmasi pembayaran.</p>@endforelse</div></section>
</div>
@endsection
