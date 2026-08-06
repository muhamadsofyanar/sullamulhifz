@extends('layouts.app',['pageTitle'=>'Operasional Hari Ini'])
@section('content')
<div class="hero launch-hero">
    <div><span class="eyebrow">OPERASIONAL PEMBELAJARAN</span><h1>Hari ini bersama {{ $teacher->nickname ?: $teacher->full_name }}</h1><p>Buka pertemuan, lengkapi absensi, catat pembelajaran, lalu bagikan ringkasan kepada wali.</p></div>
    <a class="button primary" href="{{ route('teacher.assignments.create') }}">+ Tugas Rumah</a>
</div>

<div class="stats-grid four">
    <div class="stat-card"><span>Jadwal hari ini</span><strong>{{ $todaySchedules->count() }}</strong></div>
    <div class="stat-card"><span>Pertemuan hari ini</span><strong>{{ $todayMeetings->count() }}</strong></div>
    <div class="stat-card"><span>Pertemuan terbuka</span><strong>{{ $openMeetings->count() }}</strong></div>
    <div class="stat-card"><span>Bukti perlu ditinjau</span><strong>{{ $pendingSubmissions->count() }}</strong></div>
</div>

<div class="grid two">
<section class="card">
    <div class="section-head"><div><h2>Mulai pertemuan</h2><p class="hint">Pilih kelas atau kelompok yang sedang diampu.</p></div></div>
    <div class="cards-list">
    @forelse($assignments as $item)
        @php($target=$item->schoolClass ?: $item->learningGroup)
        <div class="item-card daily-target-card">
            <div><strong>{{ $target?->name }}</strong><small>{{ $item->program?->name }} · {{ $item->class_id ? 'Kelas' : 'Kelompok' }}</small></div>
            <a class="button small primary" href="{{ route('teacher.meetings.create',['target_type'=>$item->class_id?'class':'group','target_id'=>$target?->id]) }}">Buka</a>
        </div>
    @empty
        <p class="empty">Belum ada penugasan aktif.</p>
    @endforelse
    </div>
</section>

<section class="card">
    <div class="section-head"><h2>Pertemuan belum ditutup</h2><span class="badge">Wajib diselesaikan</span></div>
    @forelse($openMeetings as $meeting)
        @php($target=$meeting->schoolClass ?: $meeting->learningGroup)
        <a class="list-row" href="{{ route('teacher.meetings.show',$meeting) }}">
            <div><strong>{{ $target?->name }}</strong><small>{{ $meeting->meeting_date->format('d M Y') }} · {{ $meeting->topic ?: 'Materi belum ditulis' }}</small></div><span>→</span>
        </a>
    @empty
        <p class="empty">Tidak ada pertemuan yang tertinggal.</p>
    @endforelse
</section>
</div>

<div class="grid two">
<section class="card">
    <div class="section-head"><h2>Jadwal hari ini</h2></div>
    @forelse($todaySchedules as $schedule)
        @php($target=$schedule->schoolClass ?: $schedule->learningGroup)
        <div class="list-row"><div><strong>{{ $target?->name }}</strong><small>{{ substr((string)$schedule->start_time,0,5) }}–{{ substr((string)$schedule->end_time,0,5) }} · {{ $schedule->program?->name }}{{ $schedule->location ? ' · '.$schedule->location : '' }}</small></div></div>
    @empty
        <p class="empty">Tidak ada jadwal resmi pada hari ini. Pertemuan tambahan tetap dapat dibuat.</p>
    @endforelse
</section>
<section class="card">
    <div class="section-head"><h2>Bukti tugas menunggu</h2><a href="{{ route('teacher.assignments.index') }}">Lihat semua</a></div>
    @forelse($pendingSubmissions as $recipient)
        <a class="list-row" href="{{ route('teacher.assignments.show',$recipient->assignment_id) }}"><div><strong>{{ $recipient->student?->full_name }}</strong><small>{{ $recipient->assignment?->title }} · {{ str_replace('_',' ',$recipient->status) }}</small></div><span>→</span></a>
    @empty
        <p class="empty">Tidak ada bukti yang perlu ditinjau.</p>
    @endforelse
</section>
</div>
@endsection
