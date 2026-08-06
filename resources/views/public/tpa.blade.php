@extends('layouts.public')
@section('title', 'Sullamul Ḥifẓ untuk TPA')
@section('description', 'Sistem operasional TPA yang menghubungkan data santri, guru, kelas, Tahsin, Tahfizh, murāja‘ah, dan komunikasi wali.')
@section('content')
<section class="page-hero tpa-hero"><div class="public-container page-hero-inner"><span class="public-eyebrow">SULLAMUL ḤIFẒ TPA</span><h1>Administrasi yang membantu pembinaan tetap terbaca.</h1><p>Aplikasi tidak menggantikan tatap muka. Aplikasi menjaga jejak keputusan dan komunikasi yang lahir dari pembinaan nyata.</p><div class="hero-actions"><a class="public-button primary" href="{{ config('sullam.portal_url') ?: route('login') }}">Masuk aplikasi TPA</a><a class="public-button secondary" href="#fitur">Lihat fitur</a></div></div></section>
<section class="public-section" id="fitur"><div class="public-container section-heading"><span class="public-eyebrow">FONDASI OPERASIONAL</span><h2>Dari identitas peserta hingga laporan perkembangan.</h2></div><div class="public-container feature-grid">
<article><span>01</span><h3>Santri & wali</h3><p>Data santri, hubungan wali, riwayat kelas, dan status tersimpan tanpa menimpa perjalanan lama.</p></article>
<article><span>02</span><h3>Guru & penugasan</h3><p>Peran guru, kelas, kelompok belajar, jadwal, dan tanggung jawab dapat ditelusuri.</p></article>
<article><span>03</span><h3>Kelas & Tahfizh</h3><p>Kelas utama dan kelompok Tahfizh dapat berjalan berdampingan sesuai kebutuhan pembelajaran.</p></article>
<article><span>04</span><h3>Pertemuan & kehadiran</h3><p>Guru mencatat pertemuan, kehadiran, topik, dan catatan umum secara terstruktur.</p></article>
<article><span>05</span><h3>Tahsin, hafalan, murāja‘ah</h3><p>Catatan dipisahkan agar mutu bacaan, hafalan baru, dan penjagaan hafalan lama dapat dibaca dengan tepat.</p></article>
<article><span>06</span><h3>Komunikasi & laporan</h3><p>Buku penghubung, pengumuman, pembinaan, dan rekap membantu orang tua memahami perjalanan anak.</p></article>
</div></section>
<section class="public-section soft-section"><div class="public-container two-column"><div><span class="public-eyebrow">IMPLEMENTASI PERTAMA</span><h2>TPA Al-Insyirah</h2><p>Implementasi awal menggunakan enam kelas utama dan dua kelompok Tahfizh, dengan peran admin, guru, dan wali yang terpisah.</p></div><div class="metric-grid"><article><strong>6</strong><span>Kelas utama</span></article><article><strong>2</strong><span>Kelompok Tahfizh</span></article><article><strong>4</strong><span>Guru awal</span></article><article><strong>88</strong><span>Santri awal</span></article></div></div></section>
<section class="public-cta"><div class="public-container cta-inner"><div><span class="public-eyebrow light">PORTAL OPERASIONAL</span><h2>Data privat tetap berada di balik autentikasi.</h2></div><a class="public-button gold" href="{{ config('sullam.portal_url') ?: route('login') }}">Masuk aplikasi</a></div></section>
@endsection
