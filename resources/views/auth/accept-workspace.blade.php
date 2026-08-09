{{-- @phase 4.7 Institution Suite --}}
@extends('layouts.app', ['pageTitle' => 'Konfirmasi Undangan Ruang'])

@section('content')
<div class="page-head expansion-head">
    <div>
        <span class="eyebrow">UNDANGAN LEMBAGA</span>
        <h1>Bergabung ke {{ $invitation->institution->name }}</h1>
        <p>Periksa informasi berikut. Ruang baru akan ditambahkan ke akun Anda tanpa mengganti atau menghapus Ruang Personal dan lembaga lainnya.</p>
    </div>
    <span class="phase-chip">Konfirmasi aman</span>
</div>

<section class="card expansion-card confirmation-card">
    <div class="snapshot-grid">
        <article class="snapshot-tile"><span>Lembaga</span><strong>{{ $invitation->institution->name }}</strong></article>
        <article class="snapshot-tile"><span>Peran</span><strong>{{ $invitation->role?->display_name }}</strong></article>
        <article class="snapshot-tile"><span>Berlaku sampai</span><strong>{{ $invitation->expires_at->format('d M Y H:i') }}</strong></article>
    </div>
    <div class="alert">Tindakan ini hanya memberi akses pada ruang yang disebutkan. Admin lembaga tidak memperoleh akses ke jurnal dan portofolio Ruang Personal Anda.</div>
    <form class="inline-form" method="post" action="{{ route('institution-suite.invitations.accept', ['token' => $token]) }}">
        @csrf
        <button class="button primary" type="submit">Setuju dan Bergabung</button>
        <a class="button ghost" href="{{ route('dashboard') }}">Kembali</a>
    </form>
</section>
@endsection
