@extends('layouts.public')
@section('title', 'Referensi Implementasi Lembaga — Sullamul Ḥifẓ')
@section('description', 'Panduan menggunakan struktur TPA Al-Insyirah sebagai referensi implementasi Sullamul Ḥifẓ di lembaga lain.')

@section('content')
<section class="page-hero reference-hero">
    <div class="public-container page-hero-inner">
        <span class="public-eyebrow">PANDUAN ADOPSI LEMBAGA</span>
        <h1>TPA Al-Insyirah adalah contoh, bukan cetakan yang harus disalin mentah.</h1>
        <p>Lembaga lain dapat menggunakan struktur operasional dan prinsip Sullamul Ḥifẓ, lalu menyesuaikannya dengan identitas, budaya, sumber daya, dan kebutuhan nyata masing-masing.</p>
        <div class="hero-actions"><a class="public-button primary" href="#boleh-ditiru">Lihat bagian yang dapat ditiru</a><a class="public-button secondary" href="{{ route('public.institution.showcase') }}">Lihat TPA Al-Insyirah</a></div>
    </div>
</section>

<section class="public-section" id="boleh-ditiru">
    <div class="public-container reference-compare-grid">
        <article class="reference-copy-card">
            <span class="public-eyebrow">DAPAT DIJADIKAN REFERENSI</span>
            <h2>Struktur yang dapat digunakan ulang.</h2>
            <ol>
                @foreach($profile['reference']['copyable'] as $item)<li>{{ $item }}</li>@endforeach
            </ol>
        </article>
        <article class="reference-adapt-card">
            <span class="public-eyebrow">WAJIB DISESUAIKAN</span>
            <h2>Identitas dan kebijakan tidak boleh disalin.</h2>
            <ol>
                @foreach($profile['reference']['must_adapt'] as $item)<li>{{ $item }}</li>@endforeach
            </ol>
        </article>
    </div>
</section>

<section class="public-section soft-section">
    <div class="public-container section-heading">
        <span class="public-eyebrow">LIMA LANGKAH IMPLEMENTASI</span>
        <h2>Mulai dari kenyataan lembaga, bukan dari daftar fitur.</h2>
    </div>
    <div class="public-container reference-step-grid">
        @foreach($profile['reference']['steps'] as $step)
            <article><span>{{ $step['number'] }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['description'] }}</p></article>
        @endforeach
    </div>
</section>

<section class="public-section">
    <div class="public-container reference-template-card">
        <div>
            <span class="public-eyebrow">TEMPLATE PROFIL LEMBAGA</span>
            <h2>Data minimum yang perlu disiapkan.</h2>
        </div>
        <div class="reference-template-grid">
            <article><h3>Identitas</h3><p>Nama resmi, nama publik, legalitas, alamat, kontak, penanggung jawab, logo, dan hubungan dengan Sullamul Ḥifẓ.</p></article>
            <article><h3>Akademik</h3><p>Tahun ajaran, jenjang, kelas, kelompok belajar, jadwal, guru, kapasitas, kurikulum, dan metode evaluasi.</p></article>
            <article><h3>Pembinaan</h3><p>Tahsīn, tahfizh, murāja‘ah, adab, tugas keluarga, Pembinaan Jumat, komunikasi, dan laporan.</p></article>
            <article><h3>Perlindungan</h3><p>Privasi anak, izin dokumentasi, hak akses, moderasi, masa simpan file, penanganan keluhan, dan audit aktivitas.</p></article>
            <article><h3>Operasional</h3><p>Alur admin, guru, wali, pendaftaran, kehadiran, penugasan, pelaporan, backup, dan pergantian tahun ajaran.</p></article>
            <article><h3>Evaluasi pilot</h3><p>Waktu pengisian guru, kemudahan wali, kualitas informasi, masalah lapangan, dan keputusan fitur berikutnya.</p></article>
        </div>
    </div>
</section>

<section class="public-section soft-section">
    <div class="public-container reference-warning-card">
        <div><span>!</span></div>
        <div><h2>Jangan menjadikan sistem sebagai alat membandingkan anak.</h2><p>Tidak ada ranking berdasarkan jumlah hafalan, kecepatan, nilai gabungan, atau profil STIFIn. Sistem membantu membaca perkembangan dan kebutuhan, bukan menentukan martabat santri.</p></div>
    </div>
</section>

<section class="public-cta">
    <div class="public-container cta-inner">
        <div><span class="public-eyebrow light">CONTOH IMPLEMENTASI</span><h2>Lihat bagaimana struktur ini diterapkan pada TPA Al-Insyirah.</h2></div>
        <a class="public-button gold" href="{{ route('public.institution.showcase') }}">Buka profil lembaga</a>
    </div>
</section>
@endsection
