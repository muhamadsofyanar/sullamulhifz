{{-- @phase 4.2 Brand & Universal Home --}}
@extends('layouts.public')
@section('title', 'Paket Sullamul Ḥifẓ')
@section('content')
<section class="page-hero"><div class="public-container page-hero-inner"><span class="public-eyebrow">PAKET LAYANAN</span><h1>Paket sedang disiapkan berdasarkan cara Anda belajar.</h1><p>Personal, Bimbingan Ustadz, Keluarga, dan Lembaga akan memiliki kapasitas serta layanan berbeda. Harga resmi belum dipublikasikan agar tidak menjanjikan paket yang belum final.</p><div class="hero-actions"><a class="public-button primary" href="{{ route('public.contact') }}">Hubungi kami</a><a class="public-button secondary" href="{{ route('personal.register') }}">Coba ruang Personal</a></div></div></section>
<section class="public-section"><div class="public-container pathway-grid"><article class="pathway-card"><span>01</span><h3>Personal</h3><p>Untuk perjalanan mandiri.</p></article><article class="pathway-card"><span>02</span><h3>Privat</h3><p>Untuk personal bersama ustadz.</p></article><article class="pathway-card"><span>03</span><h3>Family</h3><p>Untuk orang tua dan anak.</p></article><article class="pathway-card"><span>04</span><h3>Institution</h3><p>Untuk organisasi pendidikan.</p></article></div></section>
@endsection
