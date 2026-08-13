{{-- @phase 6.1 Public transparent infaq report --}}
@extends('layouts.public')
@section('title','Transparansi Infak '.$institution->name)
@section('description','Laporan penerimaan dan realisasi infak terverifikasi '.$institution->name)
@section('content')
<section class="public-hero compact"><div class="public-container"><span class="public-kicker">TRANSPARANSI INFAK</span><h1>{{ $institution->name }}</h1><p>Angka diperbarui setelah penerimaan atau realisasi diverifikasi. Identitas dan bukti sensitif tetap dilindungi.</p></div></section>
<section class="public-section"><div class="public-container">
    <div class="public-card-grid three">
        <article class="public-card"><span>Total diterima</span><h2>Rp{{ number_format($received,0,',','.') }}</h2></article>
        <article class="public-card"><span>Total dialokasikan</span><h2>Rp{{ number_format($allocated,0,',','.') }}</h2></article>
        <article class="public-card"><span>Total direalisasikan</span><h2>Rp{{ number_format($realised,0,',','.') }}</h2></article>
    </div>
    <div class="public-card"><h2>Saldo per tujuan</h2><div class="v610-public-balance">@forelse($balances as $balance)<div><span>{{ config('sullam.infaq.purposes.'.$balance->category, str($balance->category)->headline()) }}</span><strong>Rp{{ number_format((float)$balance->balance,0,',','.') }}</strong></div>@empty<p>Belum ada saldo terverifikasi.</p>@endforelse</div></div>
    <div class="section-heading"><span>PROGRAM TERVERIFIKASI</span><h2>Dana yang sudah direalisasikan</h2></div>
    <div class="public-card-grid three">@forelse($programs as $program)<article class="public-card"><span>{{ $program->realised_on?->translatedFormat('F Y') }}</span><h3>{{ $program->program_name }}</h3><p>{{ $program->impact_summary ?: $program->purpose }}</p><p><strong>{{ $program->beneficiary_count }}</strong> penerima manfaat</p>@foreach($program->evidences as $evidence)<a href="{{ route('public.infaq.evidence',$evidence) }}" target="_blank" rel="noopener">Lihat bukti tersamarkan →</a>@endforeach</article>@empty<p>Belum ada realisasi terverifikasi untuk ditampilkan.</p>@endforelse</div>
    @if($donors->isNotEmpty())<div class="public-card"><h2>Terima kasih kepada para pemberi</h2><p>Nama ditampilkan atas persetujuan pemberi dan tidak dikaitkan dengan nominal.</p><p class="v610-donor-list">{{ $donors->join(' · ') }}</p></div>@endif
</div></section>
@endsection
