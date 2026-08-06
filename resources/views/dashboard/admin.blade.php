@extends('layouts.app',['pageTitle'=>auth()->user()->hasRole('head') ? 'Beranda Kepala TPA' : 'Beranda Admin'])
@section('content')
<div class="hero brand-hero">
    <div>
        <span class="eyebrow">TPA AL-INSYIRAH</span>
        <h1>Selamat datang, {{ auth()->user()->name }}</h1>
        <p>{{ auth()->user()->hasRole('head') ? 'Pantau kegiatan penting dan kesinambungan pembinaan.' : 'Kelola data penting tanpa menambah beban pembinaan.' }}</p>
    </div>
    @if(auth()->user()->hasAnyRole(['superadmin','institution_admin']))
        <a class="button primary" href="{{ route('admin.students.create') }}"><x-icon name="plus" size="18"/> Tambah Santri</a>
    @else
        <a class="button primary" href="{{ route('admin.reports.index') }}"><x-icon name="report" size="18"/> Buka Laporan</a>
    @endif
</div>

<div class="stats-grid">
    <div class="stat-card branded-stat"><span class="stat-icon"><x-icon name="student" size="24"/></span><div><span>Santri aktif</span><strong>{{ $studentCount }}</strong></div></div>
    <div class="stat-card branded-stat"><span class="stat-icon"><x-icon name="teacher" size="24"/></span><div><span>Guru aktif</span><strong>{{ $teacherCount }}</strong></div></div>
    <div class="stat-card branded-stat"><span class="stat-icon"><x-icon name="academic" size="24"/></span><div><span>Kelas aktif</span><strong>{{ $classCount }}</strong></div></div>
    <div class="stat-card branded-stat"><span class="stat-icon"><x-icon name="calendar" size="24"/></span><div><span>Pertemuan hari ini</span><strong>{{ $todayMeetings }}</strong></div></div>
</div>

<div class="grid two">
    <section class="card">
        <div class="section-head"><h2>Akses cepat</h2></div>
        <div class="quick-grid branded-quick-grid">
            @if(auth()->user()->hasAnyRole(['superadmin','institution_admin']))
                <a href="{{ route('admin.students.index') }}"><x-icon name="student" size="22"/><span>Data Santri</span></a>
                <a href="{{ route('admin.teachers.index') }}"><x-icon name="teacher" size="22"/><span>Data Guru</span></a>
                <a href="{{ route('admin.academic.index') }}"><x-icon name="academic" size="22"/><span>Kelas & Jadwal</span></a>
            @endif
            <a href="{{ route('admin.content.index') }}"><x-icon name="guidance" size="22"/><span>Pengumuman & Jumat</span></a>
            <a href="{{ route('admin.reports.index') }}"><x-icon name="report" size="22"/><span>Laporan</span></a>
        </div>
    </section>
    <section class="card">
        <div class="section-head"><h2>Pengumuman terbaru</h2><a href="{{ route('admin.content.index') }}">Kelola</a></div>
        @if($recentAnnouncements->isEmpty())
            <p class="empty">Belum ada pengumuman.</p>
        @else
            <ul class="clean-list">@foreach($recentAnnouncements as $item)<li><strong>{{ $item->title }}</strong><small>{{ optional($item->publish_at)->format('d M Y H:i') }}</small></li>@endforeach</ul>
        @endif
    </section>
</div>
@endsection
