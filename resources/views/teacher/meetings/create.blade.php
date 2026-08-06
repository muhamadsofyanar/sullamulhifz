@extends('layouts.app',['pageTitle'=>'Mulai Pertemuan'])
@section('content')
<div class="page-head"><div><span class="eyebrow">{{ $assignment->program->name }}</span><h1>Mulai pertemuan {{ $target->name }}</h1><p>Data kelas, pengampu, dan program terisi otomatis.</p></div></div>
<form class="card stack narrow" method="post" action="{{ route('teacher.meetings.store') }}">@csrf
<input type="hidden" name="target_type" value="{{ $targetType }}"><input type="hidden" name="target_id" value="{{ $target->id }}">
<label>Jenis pertemuan<select name="meeting_type" required><option value="tahsin">Tahsīn</option><option value="tahfizh" @selected(str_contains(strtolower($assignment->program->name),'tahf'))>Tahfizh</option><option value="murajaah">Murāja‘ah</option><option value="friday_development">Pembinaan Jumat</option><option value="additional">Kegiatan tambahan</option><option value="general">Pembelajaran umum</option></select></label>
<div class="form-grid"><label>Tanggal<input type="date" name="meeting_date" value="{{ now()->format('Y-m-d') }}" required></label><label>Waktu mulai<input type="time" name="started_at" value="{{ now()->format('H:i') }}"></label></div>
<label>Materi umum<input name="topic" placeholder="Contoh: QS. Al-Qāri‘ah ayat 1–5 atau halaman Iqra 18"></label>
<button class="button primary">Mulai dan Isi Absensi</button>
</form>
@endsection
