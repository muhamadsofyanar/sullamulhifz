{{-- @phase 6.1 Verified infaq receipt --}}
@extends('layouts.app',['pageTitle'=>'Bukti Penerimaan Infak'])
@section('content')
<div class="v610-page-head"><div><span class="eyebrow">BUKTI PENERIMAAN TERVERIFIKASI</span><h1>{{ $transaction->receipt_number }}</h1><p>Dokumen ini diterbitkan setelah mutasi rekening resmi dicocokkan oleh petugas.</p></div><button class="button secondary" type="button" onclick="window.print()">Cetak / simpan PDF</button></div>
<section class="card v610-receipt" aria-label="Rincian bukti penerimaan">
    <img src="/brand/logo-horizontal.svg" alt="Sullamul Hifz">
    <dl class="v610-detail-list">
        <div><dt>Lembaga</dt><dd>{{ $transaction->institution?->name }}</dd></div>
        <div><dt>Nomor bukti</dt><dd>{{ $transaction->receipt_number }}</dd></div>
        <div><dt>Tanggal verifikasi</dt><dd>{{ $transaction->verified_at?->translatedFormat('d F Y H:i') }}</dd></div>
        <div><dt>Tujuan</dt><dd>{{ config('sullam.infaq.purposes.'.$transaction->purpose, $transaction->purpose) }}</dd></div>
        <div><dt>Jumlah</dt><dd><strong>Rp{{ number_format((float)$transaction->amount,0,',','.') }}</strong></dd></div>
        <div><dt>Status</dt><dd><span class="v610-status is-success">Terverifikasi</span></dd></div>
    </dl>
    <p class="hint">Infak bersifat sukarela dan tidak memengaruhi hak akses aplikasi.</p>
</section>
@endsection
