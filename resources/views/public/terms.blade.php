@extends('layouts.public')
@section('title', 'Syarat dan Ketentuan — Sullamul Ḥifẓ')
@section('description', 'Syarat penggunaan website dan aplikasi Sullamul Ḥifẓ.')
@section('content')
<section class="page-hero"><div class="public-container page-hero-inner"><span class="public-eyebrow">KEBIJAKAN</span><h1>Syarat dan Ketentuan</h1><p>Pedoman penggunaan layanan digital Sullamul Ḥifẓ.</p></div></section>
<section class="public-section"><div class="public-container privacy-content"><article><h2>Penggunaan layanan</h2><p>Pengguna wajib menjaga kerahasiaan akun, menggunakan data sesuai kepentingan pendidikan, dan tidak membagikan data anak kepada pihak yang tidak berwenang.</p><h2>Data pendidikan</h2><p>Catatan Tahsin, Tahfizh, murāja‘ah, kehadiran, tugas, dan komunikasi digunakan untuk pembinaan. Data tidak boleh dijadikan alat mempermalukan atau membandingkan anak secara terbuka.</p><h2>Perubahan layanan</h2><p>Fitur dapat diperbarui untuk meningkatkan keamanan dan mutu layanan. Perubahan penting akan diumumkan melalui kanal resmi.</p></article><article><h2>Kontak</h2><p>Hubungi pengelola apabila menemukan kesalahan data, masalah akses, atau dugaan penyalahgunaan.</p><a class="public-button primary" href="{{ route('public.contact') }}">Hubungi pengelola</a></article></div></section>
@endsection
