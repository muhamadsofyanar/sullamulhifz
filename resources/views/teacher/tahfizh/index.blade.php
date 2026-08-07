@extends('layouts.app',['pageTitle'=>'Tahfizh Learning Engine'])
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">FASE 3 · TAHFIZH LEARNING ENGINE</span>
        <h1>Jaga proses, bukan hanya jumlah setoran.</h1>
        <p>Target, persiapan talaqqi, setoran, penguatan, murāja‘ah dan tindak lanjut berada dalam satu alur.</p>
    </div>
    <div class="actions"><a class="button secondary" href="{{ route('teacher.learning-plan.index') }}">Target & Profil</a></div>
</div>
<div class="stats-grid four">
    <div class="stat-card"><span>Santri dalam penugasan</span><strong>{{ $students->count() }}</strong></div>
    <div class="stat-card"><span>Murāja‘ah jatuh tempo</span><strong>{{ $dueReviews->count() }}</strong></div>
    <div class="stat-card"><span>Siklus aktif</span><strong>{{ $activeCycles->count() }}</strong></div>
    <div class="stat-card"><span>Perlu tindak lanjut</span><strong>{{ $attentionStudents->count() }}</strong></div>
</div>
<div class="grid two">
<section class="card">
    <div class="section-head"><div><h2>Murāja‘ah hari ini</h2><p class="hint">Jadwal dibuat dari keputusan guru, bukan rumus ranking otomatis.</p></div><span>{{ $dueReviews->count() }}</span></div>
    @forelse($dueReviews as $plan)
        <div class="list-row"><div><strong>{{ $plan->student?->full_name }} · {{ $plan->surah?->name_latin }} {{ $plan->start_verse }}–{{ $plan->end_verse }}</strong><small>{{ $plan->review_date?->format('d M Y') }} · {{ ['normal'=>'Penjagaan','strengthen'=>'Penguatan','recall'=>'Panggil kembali'][$plan->priority] ?? $plan->priority }}</small>@if($plan->notes)<p>{{ $plan->notes }}</p>@endif</div><a class="button small secondary" href="{{ route('teacher.tahfizh.student',$plan->student_id) }}">Buka</a></div>
    @empty<p class="empty">Tidak ada Murāja‘ah jatuh tempo.</p>@endforelse
</section>
<section class="card">
    <div class="section-head"><div><h2>Perlu ditindaklanjuti</h2><p class="hint">Bukan peringkat. Hanya pekerjaan pembinaan yang belum selesai.</p></div><span>{{ $attentionStudents->count() }}</span></div>
    @forelse($attentionStudents as $student)
        <div class="list-row"><div><strong>{{ $student->full_name }}</strong><small>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Kelompok belajar' }}</small></div><a class="button small ghost" href="{{ route('teacher.tahfizh.student',$student) }}">Lihat perjalanan</a></div>
    @empty<p class="empty">Belum ada tindak lanjut khusus.</p>@endforelse
</section>
</div>
<section class="card">
    <div class="section-head"><div><h2>Siklus belajar aktif</h2><p class="hint">Persiapan → siap → setoran → penguatan → selesai.</p></div><span>{{ $activeCycles->count() }}</span></div>
    @forelse($activeCycles as $cycle)
        <div class="list-row"><div><strong>{{ $cycle->student?->full_name }} · {{ $cycle->target?->surah?->name_latin ?? 'Target umum' }}</strong><small>{{ ucfirst(str_replace('_',' ',$cycle->cycle_type)) }} · {{ ucfirst(str_replace('_',' ',$cycle->preparation_method)) }} · {{ ucfirst(str_replace('_',' ',$cycle->status)) }}</small></div><a class="button small ghost" href="{{ route('teacher.tahfizh.student',$cycle->student_id) }}">Kelola</a></div>
    @empty<p class="empty">Belum ada siklus aktif.</p>@endforelse
</section>
<section class="card">
    <div class="section-head"><h2>Semua santri</h2><span>{{ $students->count() }}</span></div>
    <div class="grid two">@foreach($students as $student)<a class="card-link" href="{{ route('teacher.tahfizh.student',$student) }}"><strong>{{ $student->full_name }}</strong><small>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Kelompok belajar' }}</small><span>Perjalanan Tahfizh →</span></a>@endforeach</div>
</section>
@endsection
