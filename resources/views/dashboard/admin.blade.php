@extends('layouts.app',['pageTitle'=>auth()->user()->hasRole('head') ? 'Beranda Kepala TPA' : 'Beranda Admin'])
@section('content')
<div class="hero"><div><span class="eyebrow">TPA AL-INSYIRAH</span><h1>Selamat datang, {{ auth()->user()->name }}</h1><p>{{ auth()->user()->hasRole('head') ? 'Pantau kegiatan penting dan kesinambungan pembinaan.' : 'Kelola data penting tanpa menambah beban pembinaan.' }}</p></div>@if(auth()->user()->hasAnyRole(['superadmin','institution_admin']))<a class="button primary" href="{{ route('admin.students.create') }}">+ Tambah Santri</a>@else<a class="button primary" href="{{ route('admin.reports.index') }}">Buka Laporan</a>@endif</div>
<div class="stats-grid">
    <div class="stat-card"><span>Santri aktif</span><strong>{{ $studentCount }}</strong></div>
    <div class="stat-card"><span>Guru aktif</span><strong>{{ $teacherCount }}</strong></div>
    <div class="stat-card"><span>Kelas aktif</span><strong>{{ $classCount }}</strong></div>
    <div class="stat-card"><span>Pertemuan hari ini</span><strong>{{ $todayMeetings }}</strong></div>
</div>
<div class="grid two">
<section class="card"><div class="section-head"><h2>Akses cepat</h2></div><div class="quick-grid">
@if(auth()->user()->hasAnyRole(['superadmin','institution_admin']))
<a href="{{ route('admin.students.index') }}">Data Santri</a><a href="{{ route('admin.teachers.index') }}">Data Guru</a><a href="{{ route('admin.academic.index') }}">Kelas & Jadwal</a>
@endif
<a href="{{ route('admin.content.index') }}">Pengumuman & Jumat</a><a href="{{ route('admin.reports.index') }}">Laporan</a>
</div></section>
<section class="card"><div class="section-head"><h2>Pengumuman terbaru</h2><a href="{{ route('admin.content.index') }}">Kelola</a></div>
@if($recentAnnouncements->isEmpty())<p class="empty">Belum ada pengumuman.</p>@else<ul class="clean-list">@foreach($recentAnnouncements as $item)<li><strong>{{ $item->title }}</strong><small>{{ optional($item->publish_at)->format('d M Y H:i') }}</small></li>@endforeach</ul>@endif
</section></div>
@endsection
