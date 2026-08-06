@extends('layouts.app',['pageTitle'=>'Laporan'])
@section('content')
<div class="page-head"><div><h1>Laporan dasar</h1><p>Ekspor data administratif tanpa membuat peringkat santri.</p></div></div>
<div class="stats-grid three"><div class="stat-card"><span>Santri aktif</span><strong>{{ $studentCount }}</strong></div><div class="stat-card"><span>Catatan kehadiran</span><strong>{{ $attendanceCount }}</strong></div><div class="stat-card"><span>Catatan setoran</span><strong>{{ $memorizationCount }}</strong></div></div>
<div class="grid two"><section class="card"><h2>Data santri</h2><p>Berisi kode, nama, kelas aktif, status, dan wali terhubung.</p><a class="button primary" href="{{ route('admin.reports.students.csv') }}">Unduh CSV Santri</a></section><section class="card"><h2>Absensi</h2><p>Berisi tanggal, kelas/kelompok, santri, status, dan catatan.</p><a class="button primary" href="{{ route('admin.reports.attendance.csv') }}">Unduh CSV Absensi</a></section></div>
<div class="privacy-note">Laporan menggunakan data yang diperlukan untuk operasional. Tidak tersedia laporan ranking berdasarkan jumlah atau kecepatan hafalan.</div>
@endsection
