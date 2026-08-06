@extends('layouts.app',['pageTitle'=>'Mulai Pertemuan'])
@section('content')
<div class="page-head"><div><span class="eyebrow">{{ $assignment->program->name }}</span><h1>Mulai pertemuan {{ $target->name }}</h1><p>Data kelas, pengampu, dan program terisi otomatis.</p></div></div>
<form class="card stack narrow" method="post" action="{{ route('teacher.meetings.store') }}">@csrf<input type="hidden" name="target_type" value="{{ $targetType }}"><input type="hidden" name="target_id" value="{{ $target->id }}"><label>Tanggal<input type="date" name="meeting_date" value="{{ now()->format('Y-m-d') }}" required></label><label>Waktu mulai<input type="time" name="started_at" value="{{ now()->format('H:i') }}"></label><label>Materi umum, opsional<input name="topic" placeholder="Contoh: QS. Al-Qāri‘ah ayat 1–3"></label><button class="button primary">Mulai dan Isi Absensi</button></form>
@endsection
