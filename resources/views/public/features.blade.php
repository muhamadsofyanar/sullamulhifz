{{-- @phase 4.2 Brand & Universal Home --}}
@extends('layouts.public')
@section('title', 'Fitur Sullamul Ḥifẓ')
@section('content')
<section class="page-hero"><div class="public-container page-hero-inner"><span class="public-eyebrow">FITUR</span><h1>Satu mesin perjalanan, disesuaikan untuk setiap ruang.</h1><p>Fitur muncul sesuai peran, hubungan, lembaga, dan izin pengguna—bukan sekadar satu menu besar untuk semua.</p></div></section>
<section class="public-section"><div class="public-container feature-grid">@foreach([
['Personal','Target, jurnal, check-in, latihan, murāja‘ah, dan riwayat pribadi.'],
['Bimbingan','Program ustadz, setoran, koreksi, target bersama, dan evaluasi.'],
['Akademik','Tahun ajaran, kelas, kelompok, jadwal, presensi, dan penilaian.'],
['Keluarga','Tugas rumah, laporan, Buku Penghubung, dan persetujuan akses.'],
['Pembelajaran','Tahsin, tahfiz, murāja‘ah, mushaf, audio, Academy, dan sertifikat.'],
['Komunikasi','WhatsApp, email, template, status pengiriman, retry, dan audit.'],
['Laporan','Progres personal, rapor, rekap kelas, serta ringkasan lembaga.'],
['Keamanan','Ruang terpisah, izin berbasis peran, audit log, dan data terenkripsi.'],
['Multi-lembaga','Satu akun dapat berpindah antara ruang personal, privat, keluarga, dan lembaga.']
] as [$title,$copy])<article><span>{{ str_pad((string) $loop->iteration,2,'0',STR_PAD_LEFT) }}</span><h3>{{ $title }}</h3><p>{{ $copy }}</p></article>@endforeach</div></section>
@endsection
