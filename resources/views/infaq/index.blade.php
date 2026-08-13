{{-- @phase 6.0 Voluntary infaq; @phase 6.1 transparent UX --}}
@extends('layouts.app',['pageTitle'=>'Infak Sukarela'])
@section('content')
@php
    $statusLabels=['pending'=>'Menunggu verifikasi','verified'=>'Terverifikasi','rejected'=>'Perlu diperbaiki','refunded'=>'Dikembalikan'];
@endphp
<div class="v610-page-head"><div><span class="eyebrow">GRATIS · SUKARELA · TRANSPARAN</span><h1>Dukung keberlanjutan, tanpa mengubah hak akses.</h1><p>Aplikasi tetap gratis. Bukti transfer boleh dilampirkan untuk membantu pencocokan, tetapi verifikasi tetap dilakukan melalui mutasi rekening resmi.</p></div><span class="v610-status is-info">v6.1</span></div>
<div class="grid two">
<section class="card"><div class="section-head"><div><h2>Catat infak</h2><p class="hint">Nama anonim secara bawaan.</p></div></div>
    <form class="stack" method="post" action="{{ route('infaq.store') }}" enctype="multipart/form-data">@csrf
        <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <label>Tujuan<select name="purpose" required>@foreach($purposes as $key=>$label)<option value="{{ $key }}" @selected(old('purpose')===$key)>{{ $label }}</option>@endforeach</select><small>Tujuan khusus digunakan 100% untuk tujuan tersebut. Infak umum mengikuti kebijakan lembaga.</small></label>
        <label>Jumlah (Rp)<input type="number" name="amount" min="1000" step="1000" value="{{ old('amount') }}" required inputmode="numeric"></label>
        <label>Bukti transfer <span class="hint">(opsional)</span><input type="file" name="transfer_proof" accept="image/jpeg,image/png,image/webp,application/pdf"><small>PDF/JPG/PNG/WebP, maksimal 5 MB. File disimpan privat.</small></label>
        <label class="check-row"><input type="checkbox" name="show_donor_name" value="1" @checked(old('show_donor_name'))><span>Saya mengizinkan nama saya ditampilkan dalam daftar ucapan terima kasih. Nama tidak akan dikaitkan dengan nominal.</span></label>
        <label class="check-row"><input type="checkbox" name="voluntary_acknowledgement" value="1" required><span>Saya memahami infak ini sukarela dan bukan syarat memakai aplikasi.</span></label>
        <button class="button primary" type="submit">Catat infak</button>
    </form>
</section>
<section class="card"><span class="eyebrow">REKENING RESMI</span><h2>{{ $destination['bank_name'] }}</h2><strong class="v530-bank-number">{{ $destination['account_number'] }}</strong><p>a.n. {{ $destination['account_name'] }}</p><div class="v610-privacy-note"><strong>Keamanan transaksi</strong><p>Jangan pernah memberikan OTP, PIN, atau kata sandi perbankan. Status hanya berubah setelah admin mencocokkan mutasi rekening.</p></div></section>
</div>
<section class="card"><div class="section-head"><div><h2>Dana yang sudah terverifikasi</h2><p class="hint">Ringkasan agregat; tidak menampilkan nominal per pemberi.</p></div></div><div class="v610-metrics">@forelse($summary as $item)<div class="v610-metric"><span>{{ $purposes[$item->purpose] ?? $item->purpose }}</span><strong>Rp{{ number_format((float)$item->total_amount,0,',','.') }}</strong><small>{{ $item->transactions_count }} transaksi</small></div>@empty<div class="empty">Belum ada infak terverifikasi.</div>@endforelse</div></section>
<section class="card"><div class="section-head"><h2>Riwayat saya</h2><span>{{ $transactions->count() }} transaksi</span></div><div class="v610-worklist">@forelse($transactions as $transaction)<article class="v610-workitem"><div><div class="v610-actions"><strong>Rp{{ number_format((float)$transaction->amount,0,',','.') }}</strong><span class="v610-status {{ $transaction->status==='verified'?'is-success':($transaction->status==='rejected'?'is-danger':'is-warning') }}">{{ $statusLabels[$transaction->status] ?? $transaction->status }}</span></div><small>{{ $purposes[$transaction->purpose] ?? $transaction->purpose }} · {{ $transaction->created_at->translatedFormat('d M Y H:i') }}</small>@if($transaction->rejection_reason)<p>{{ $transaction->rejection_reason }}</p>@endif<form method="post" action="{{ route('infaq.consent',$transaction) }}">@csrf @method('PUT')<input type="hidden" name="show_donor_name" value="{{ $transaction->show_donor_name ? 0 : 1 }}"><button class="link-button" type="submit">{{ $transaction->show_donor_name ? 'Cabut izin tampilkan nama' : 'Izinkan nama tampil tanpa nominal' }}</button></form></div>@if($transaction->receipt_number)<a class="button small secondary" href="{{ route('infaq.receipt',$transaction) }}">Buka bukti penerimaan</a>@endif</article>@empty<p class="empty">Belum ada dukungan. Akses aplikasi tetap lengkap.</p>@endforelse</div></section>
@endsection
