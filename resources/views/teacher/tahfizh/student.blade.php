@extends('layouts.app',['pageTitle'=>'Perjalanan Tahfizh'])
@section('content')
@php
$errorLabels=['makhraj'=>'Makhraj','tajwid'=>'Tajwid','mad'=>'Panjang-pendek','ghunnah'=>'Ghunnah','waqf_ibtida'=>'Waqaf & ibtida','fluency'=>'Kelancaran','hesitation'=>'Terhenti/ragu','omission'=>'Ayat/kata terlewat','substitution'=>'Pergantian lafaz','sequence'=>'Urutan','prompt_dependency'=>'Ketergantungan bantuan','other'=>'Lainnya'];
@endphp
<div class="page-head">
    <div><span class="eyebrow">PERJALANAN INDIVIDUAL</span><h1>{{ $student->full_name }}</h1><p>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Kelompok belajar' }} · jejak belajar tanpa perbandingan dengan santri lain.</p></div>
    <div class="actions"><a class="button secondary" href="{{ route('teacher.tahfizh.index') }}">Kembali</a>@if(\App\Support\Feature::enabled('quran_audio', auth()->user()->institution_id, true))<a class="button ghost" href="{{ route('quran-practice.index') }}">Audio Qur’an</a>@endif</div>
</div>
<div class="stats-grid four">
    <div class="stat-card"><span>Target aktif</span><strong>{{ $summary['activeTargets'] }}</strong></div>
    <div class="stat-card"><span>Murāja‘ah jatuh tempo</span><strong>{{ $summary['dueReviews'] }}</strong></div>
    <div class="stat-card"><span>Fokus koreksi terbuka</span><strong>{{ $summary['openErrors'] }}</strong></div>
    <div class="stat-card"><span>Siklus aktif</span><strong>{{ $summary['activeCycles'] }}</strong></div>
</div>
@if($summary['nextReview'])
<section class="card phase-highlight"><span class="eyebrow">BERIKUTNYA</span><h2>Murāja‘ah {{ $summary['nextReview']->surah?->name_latin }} {{ $summary['nextReview']->start_verse }}–{{ $summary['nextReview']->end_verse }}</h2><p>{{ $summary['nextReview']->review_date?->format('d M Y') }} · {{ $summary['nextReview']->notes ?: 'Ikuti kondisi hafalan saat pertemuan.' }}</p></section>
@endif
<div class="grid two">
<section class="card">
<h2>Mulai / lanjutkan siklus belajar</h2>
<form class="stack compact" method="post" action="{{ route('teacher.tahfizh.cycles.store') }}">@csrf
<input type="hidden" name="student_id" value="{{ $student->id }}">
<label>Target terkait<select name="memorization_target_id"><option value="">Tanpa target khusus</option>@foreach($student->memorizationTargets->whereIn('status',['active','in_progress','strengthening','paused']) as $target)<option value="{{ $target->id }}">{{ $target->surah?->name_latin }} {{ $target->start_verse }}–{{ $target->end_verse }}</option>@endforeach</select></label>
<div class="form-grid"><label>Jenis siklus<select name="cycle_type"><option value="new_memorization">Hafalan baru</option><option value="initial_repetition">Pengulangan awal</option><option value="murajaah">Murāja‘ah</option><option value="talaqqi">Talaqqi</option><option value="tasmi">Tasmi‘</option><option value="exam">Ujian</option></select></label><label>Persiapan<select name="preparation_method"><option value="talaqqi">Talaqqi</option><option value="audio_repetition">Audio berulang</option><option value="reading_repetition">Membaca berulang</option><option value="writing">Menulis</option><option value="word_arrangement">Susun kata</option><option value="movement">Gerak</option><option value="teach_back">Ajarkan kembali</option><option value="mixed">Campuran</option><option value="custom">Khusus</option></select></label></div>
<label>Arahan guru<textarea name="teacher_guidance" rows="3"></textarea></label><label>Arahan keluarga<textarea name="guardian_guidance" rows="3"></textarea></label>
<button class="button primary">Siapkan siklus</button></form>
</section>
<section class="card">
<h2>Jadwalkan Murāja‘ah</h2><p class="hint">Guru menentukan tanggal berdasarkan kebutuhan nyata; tidak ada rumus interval wajib.</p>
<form class="stack compact" method="post" action="{{ route('teacher.tahfizh.reviews.store') }}">@csrf
<input type="hidden" name="student_id" value="{{ $student->id }}">
<label>Target terkait<select name="memorization_target_id"><option value="">Opsional</option>@foreach($student->memorizationTargets as $target)<option value="{{ $target->id }}">{{ $target->surah?->name_latin }} {{ $target->start_verse }}–{{ $target->end_verse }}</option>@endforeach</select></label>
<label>Surah<select name="surah_id" required>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
<div class="form-grid"><label>Ayat awal<input type="number" min="1" name="start_verse" required></label><label>Ayat akhir<input type="number" min="1" name="end_verse" required></label></div>
<div class="form-grid"><label>Tanggal<input type="date" name="review_date" value="{{ now()->addDay()->format('Y-m-d') }}" required></label><label>Jenis<select name="review_type"><option value="scheduled">Terjadwal</option><option value="random_recall">Pemanggilan acak</option><option value="continuation">Sambung ayat</option><option value="tasmi">Tasmi‘</option><option value="home">Di rumah</option></select></label></div>
<label>Prioritas<select name="priority"><option value="normal">Penjagaan</option><option value="strengthen">Penguatan</option><option value="recall">Panggil kembali</option></select></label><label>Catatan<input name="notes"></label>
<button class="button primary">Simpan jadwal</button></form>
</section>
</div>
<section class="card"><div class="section-head"><div><h2>Siklus belajar</h2><p class="hint">Status menjelaskan posisi proses, bukan nilai anak.</p></div><span>{{ $cycles->count() }}</span></div>
@forelse($cycles as $cycle)<div class="list-row"><div><strong>{{ ucfirst(str_replace('_',' ',$cycle->cycle_type)) }} · {{ $cycle->target?->surah?->name_latin ?? 'Tanpa target terkait' }}</strong><small>{{ ucfirst(str_replace('_',' ',$cycle->preparation_method)) }} · {{ ucfirst(str_replace('_',' ',$cycle->status)) }}</small><p>{{ $cycle->teacher_guidance }} {{ $cycle->guardian_guidance }}</p></div><form class="inline-form" method="post" action="{{ route('teacher.tahfizh.cycles.update',$cycle) }}">@csrf @method('PUT')<select name="status"><option value="preparing" @selected($cycle->status==='preparing')>Persiapan</option><option value="ready" @selected($cycle->status==='ready')>Siap</option><option value="submitted" @selected($cycle->status==='submitted')>Sudah setoran</option><option value="strengthening" @selected($cycle->status==='strengthening')>Penguatan</option><option value="completed" @selected($cycle->status==='completed')>Selesai</option><option value="paused" @selected($cycle->status==='paused')>Jeda</option><option value="cancelled" @selected($cycle->status==='cancelled')>Dibatalkan</option></select><input type="hidden" name="teacher_guidance" value="{{ $cycle->teacher_guidance }}"><input type="hidden" name="guardian_guidance" value="{{ $cycle->guardian_guidance }}"><button class="button small secondary">Simpan</button></form></div>@empty<p class="empty">Belum ada siklus belajar.</p>@endforelse
</section>
<div class="grid two">
<section class="card"><div class="section-head"><h2>Jadwal penjagaan</h2><span>{{ $reviewPlans->count() }}</span></div>
@forelse($reviewPlans as $plan)<div class="list-row"><div><strong>{{ $plan->surah?->name_latin }} {{ $plan->start_verse }}–{{ $plan->end_verse }}</strong><small>{{ $plan->review_date?->format('d M Y') }} · {{ ucfirst(str_replace('_',' ',$plan->status)) }} · {{ ['normal'=>'Penjagaan','strengthen'=>'Penguatan','recall'=>'Panggil kembali'][$plan->priority] ?? $plan->priority }}</small><p>{{ $plan->notes }}</p></div>@if($plan->status==='scheduled')<form method="post" action="{{ route('teacher.tahfizh.reviews.update',$plan) }}">@csrf @method('PUT')<input type="hidden" name="status" value="skipped"><button class="button small ghost">Lewati</button></form>@endif</div>@empty<p class="empty">Belum ada jadwal Murāja‘ah.</p>@endforelse
</section>
<section class="card"><div class="section-head"><h2>Fokus koreksi</h2><span>{{ $correctionItems->whereNull('resolved_at')->count() }} terbuka</span></div>
@forelse($correctionItems as $error)<div class="list-row"><div><strong>{{ $errorLabels[$error->category] ?? ucfirst(str_replace('_',' ',$error->category)) }}</strong><small>{{ ucfirst($error->record_type) }} · {{ $error->created_at?->format('d M Y') }} @if($error->ayah_number) · ayat {{ $error->ayah_number }} @endif</small><p>{{ $error->note }}</p></div>@if(!$error->resolved_at)<form method="post" action="{{ route('teacher.tahfizh.errors.resolve',$error) }}">@csrf @method('PUT')<button class="button small secondary">Sudah ditindaklanjuti</button></form>@else<span class="status-pill success">Selesai</span>@endif</div>@empty<p class="empty">Belum ada fokus koreksi terstruktur.</p>@endforelse
</section>
</div>
<section class="card"><div class="section-head"><h2>Riwayat setoran & Murāja‘ah</h2><span>Terbaru</span></div><div class="grid two">
<div><h3>Setoran</h3>@forelse($student->memorizationRecords as $record)<div class="list-row"><div><strong>{{ $record->surah?->name_latin }} {{ $record->start_verse }}–{{ $record->end_verse }}</strong><small>{{ $record->recorded_at?->format('d M Y') }} · {{ ['fluent'=>'Lancar','fair'=>'Lulus dengan penguatan','repeat_needed'=>'Perlu diulang','postponed'=>'Belum dinilai'][$record->result] ?? $record->result }} · {{ ucfirst(str_replace('_',' ',$record->delivery_mode ?? 'individual_submission')) }}</small><p>{{ $record->review_recommendation ?: $record->teacher_notes }}</p></div></div>@empty<p class="empty">Belum ada setoran.</p>@endforelse</div>
<div><h3>Murāja‘ah</h3>@forelse($student->murajaahRecords as $record)<div class="list-row"><div><strong>{{ $record->surah?->name_latin }} {{ $record->start_verse }}–{{ $record->end_verse }}</strong><small>{{ $record->recorded_at?->format('d M Y') }} · {{ ['maintained'=>'Masih terjaga','strengthening_needed'=>'Perlu dikuatkan','reactivation_needed'=>'Perlu dipanggil kembali'][$record->result] ?? $record->result }}</small><p>{{ $record->review_recommendation ?: $record->teacher_notes }}</p></div></div>@empty<p class="empty">Belum ada Murāja‘ah.</p>@endforelse</div>
</div></section>
@endsection
