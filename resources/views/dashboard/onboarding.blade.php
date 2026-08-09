{{-- @phase 4.4 Multi-tenant Institution Foundation --}}
@extends('layouts.app',['pageTitle'=>'Ruang Lembaga'])
@section('content')
@php
    $statusCopy = match ($institution->status) {
        'rejected' => 'Pendaftaran ruang belum dapat disetujui. Hubungi tim platform untuk klarifikasi.',
        'suspended' => 'Operasional ruang sedang ditangguhkan. Hubungi tim platform untuk tindak lanjut.',
        default => 'Ruang Anda sudah tercatat dan sedang menunggu pemeriksaan platform.',
    };
@endphp
<div class="page-head"><div><span class="eyebrow">STATUS PENDAFTARAN</span><h1>{{ $institution->name }}</h1><p>{{ $statusCopy }}</p></div></div>
<div class="grid two"><section class="card"><h2>Status saat ini</h2><div class="stats-grid two"><div class="stat-card"><span>Jenis</span><strong>{{ \App\Support\InstitutionType::label($institution->institution_type) }}</strong></div><div class="stat-card"><span>Onboarding</span><strong>{{ str($institution->onboarding_status)->replace('_',' ')->title() }}</strong></div></div><p>Anda tetap dapat memperbarui profil akun. Fitur operasional dibuka setelah status lembaga aktif.</p></section><section class="card"><h2>Berikutnya</h2><ol><li>Pastikan nama dan kontak pengelola benar.</li><li>Tim platform memeriksa pendaftaran.</li><li>Setelah aktif, lengkapi identitas, tahun ajaran, dan struktur akademik.</li></ol><a class="button secondary" href="{{ route('profile.edit') }}">Periksa profil akun</a></section></div>
@endsection
