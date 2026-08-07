@extends('layouts.app',['pageTitle'=>'Sullamul Hifz Academy'])
@section('content')
<div class="academy-family-hero">
    <div><span class="eyebrow">FAMILY LEARNING</span><h1>Academy untuk mendampingi perjalanan Al-Qur’an.</h1><p>Materi singkat, hangat, dan dapat langsung dipraktikkan di rumah atau kelas.</p></div>
    <div class="academy-hero-mark"><x-icon name="academic" size="42"/></div>
</div>

@if($recommendations->isNotEmpty())
<section class="card academy-recommendations">
    <div class="section-head"><div><h2>Direkomendasikan untuk keluarga</h2><p class="hint">Materi dipilih guru sesuai kebutuhan anak. Selesaikan satu per satu.</p></div><span class="badge">{{ $recommendations->count() }} rekomendasi</span></div>
    <div class="academy-recommendation-list">
        @foreach($recommendations as $recommendation)
        <a class="academy-recommendation-card" href="{{ route('academy.lesson',$recommendation->lesson) }}">
            <div class="academy-icon-circle"><x-icon name="academic"/></div>
            <div><small>Untuk {{ $recommendation->student->full_name }}</small><strong>{{ $recommendation->lesson->title }}</strong><p>{{ $recommendation->message ?: $recommendation->lesson->summary }}</p><span>{{ $recommendation->lesson->module->program->title }}</span></div>
            <b>Mulai →</b>
        </a>
        @endforeach
    </div>
</section>
@endif

<section class="academy-program-grid">
@forelse($programs as $program)
    @php($lessons=$program->modules->flatMap->lessons)
    @php($done=$lessons->whereIn('id',$completedIds)->count())
    @php($percent=$lessons->count() ? (int)round(($done/$lessons->count())*100) : 0)
    <a class="academy-program-card" href="{{ route('academy.program',$program) }}">
        <div class="academy-program-top"><span class="academy-audience">{{ $program->audience==='guardian'?'ORANG TUA':($program->audience==='teacher'?'GURU':'BERSAMA') }}</span>@if($program->is_featured)<span class="badge">Utama</span>@endif</div>
        <h2>{{ $program->title }}</h2><p>{{ $program->summary }}</p>
        <div class="academy-progress"><span style="width:{{ $percent }}%"></span></div>
        <div class="academy-program-meta"><span>{{ $done }}/{{ $lessons->count() }} materi selesai</span><strong>{{ $percent }}%</strong></div>
        <div class="academy-open">Buka Academy <span>→</span></div>
    </a>
@empty
    <div class="card"><p class="empty">Program Academy belum diterbitkan.</p></div>
@endforelse
</section>

<section class="card academy-principle"><div><x-icon name="community" size="32"/></div><div><h2>Belajar untuk mendampingi, bukan mengawasi.</h2><p>Academy membantu guru dan orang tua bekerja sama. Tidak ada ranking santri dan tidak ada tuntutan menyelesaikan semua materi sekaligus.</p></div></section>
@endsection
