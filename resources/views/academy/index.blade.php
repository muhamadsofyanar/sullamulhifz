@extends($academyLayout ?? 'layouts.app',['pageTitle'=>'Sullamul Hifz Academy'])
@section('content')
@php
    $lessonCount = $allLessons->count();
    $completedCount = $completedIds->count();
    $overallPercent = $lessonCount ? (int) round(($completedCount / $lessonCount) * 100) : 0;
@endphp
<div class="academy-family-hero">
    <div><span class="eyebrow">SULLAMUL ḤIFẒ ACADEMY</span><h1>Belajar untuk mendampingi perjalanan, bukan menambah tekanan.</h1><p>Program singkat untuk orang tua, guru, dan keluarga: Al-Qur’an, pendidikan anak, STIFIn secara proporsional, komunikasi, adab, dan praktik pendampingan yang dapat langsung dicoba.</p></div>
    <div class="academy-hero-mark"><x-icon name="lesson" size="46"/></div>
</div>

@if($academyStandalone ?? false)
<section class="academy-overview-grid" aria-label="Ringkasan Academy">
    <article class="academy-overview-card"><small>Program tersedia</small><strong>{{ $programs->count() }}</strong><span>Sesuai peran Anda</span></article>
    <article class="academy-overview-card"><small>Total materi</small><strong>{{ $lessonCount }}</strong><span>Video, artikel, aktivitas & audio</span></article>
    <article class="academy-overview-card"><small>Sudah selesai</small><strong>{{ $completedCount }}</strong><span>Materi yang telah ditandai selesai</span></article>
    <article class="academy-overview-card"><small>Progres keseluruhan</small><strong>{{ $overallPercent }}%</strong><span>Belajar boleh bertahap</span></article>
</section>

<section class="academy-quick-grid">
    <a class="academy-quick-card" href="{{ route('academy.portal.programs') }}"><x-icon name="lesson"/><strong>Program</strong><span>Lihat seluruh e-course yang tersedia.</span></a>
    <a class="academy-quick-card" href="{{ route('academy.portal.videos') }}"><x-icon name="video"/><strong>Video</strong><span>Materi video singkat sebagai pengantar.</span></a>
    <a class="academy-quick-card" href="{{ route('academy.portal.audio') }}"><x-icon name="listen"/><strong>Audio Qur’an</strong><span>Murattal dan latihan pengulangan.</span></a>
    <a class="academy-quick-card" href="{{ route('academy.portal.articles') }}"><x-icon name="assignment"/><strong>Artikel & Aktivitas</strong><span>Bacaan praktis untuk rumah dan kelas.</span></a>
</section>
@endif

@if($recommendations->isNotEmpty())
<section class="card academy-recommendations">
    <div class="section-head"><div><h2>Direkomendasikan untuk keluarga</h2><p class="hint">Materi dipilih guru sesuai kebutuhan anak. Selesaikan satu per satu.</p></div><span class="badge">{{ $recommendations->where('status','active')->count() }} aktif</span></div>
    <div class="academy-recommendation-list">
        @foreach($recommendations->take(4) as $recommendation)
        <a class="academy-recommendation-card" href="{{ route($academyRoutePrefix.'lesson',$recommendation->lesson) }}">
            <div class="academy-icon-circle"><x-icon name="guidance"/></div>
            <div><small>Untuk {{ $recommendation->student->full_name }}</small><strong>{{ $recommendation->lesson->title }}</strong><p>{{ $recommendation->message ?: $recommendation->lesson->summary }}</p><span>{{ $recommendation->lesson->module->program->title }}</span></div>
            <b>{{ $recommendation->status === 'completed' ? 'Sudah dibaca' : 'Mulai →' }}</b>
        </a>
        @endforeach
    </div>
    @if(($academyStandalone ?? false) && $recommendations->count()>4)<p style="margin:14px 0 0"><a class="text-link" href="{{ route('academy.portal.recommendations') }}">Lihat semua rekomendasi →</a></p>@endif
</section>
@endif

<div class="academy-section-heading"><div><span class="eyebrow">E-COURSE</span><h2>Program yang dapat Anda ikuti</h2><p>Mulai dari materi yang paling relevan. Tidak harus selesai sekaligus.</p></div>@if($academyStandalone ?? false)<a class="button secondary" href="{{ route('academy.portal.classes') }}">Kelas Saya</a>@endif</div>
<section class="academy-program-grid">
@forelse($programs as $program)
    @php($lessons=$program->modules->flatMap->lessons)
    @php($done=$lessons->whereIn('id',$completedIds)->count())
    @php($percent=$lessons->count() ? (int)round(($done/$lessons->count())*100) : 0)
    <a class="academy-program-card" href="{{ route($academyRoutePrefix.'program',$program) }}">
        <div class="academy-program-top"><span class="academy-audience">{{ $program->audience==='guardian'?'ORANG TUA':($program->audience==='teacher'?'GURU':($program->audience==='admin'?'PENGELOLA':'BERSAMA')) }}</span>@if($program->is_featured)<span class="badge">Utama</span>@endif</div>
        <h2>{{ $program->title }}</h2><p>{{ $program->summary }}</p>
        <div class="academy-progress"><span style="width:{{ $percent }}%"></span></div>
        <div class="academy-program-meta"><span>{{ $done }}/{{ $lessons->count() }} materi selesai</span><strong>{{ $percent }}%</strong></div>
        <div class="academy-open">Buka program <span>→</span></div>
    </a>
@empty
    <div class="academy-empty">Program Academy belum diterbitkan.</div>
@endforelse
</section>

<section class="card academy-principle"><div><x-icon name="values" size="32"/></div><div><h2>Human Before Data.</h2><p>Academy mengikuti filosofi Sullamul Ḥifẓ: tidak ada ranking santri, STIFIn tidak menjadi label, dan materi dipakai untuk membantu keputusan serta pendampingan yang manusiawi.</p></div></section>
@endsection
