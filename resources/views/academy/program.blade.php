@extends($academyLayout ?? 'layouts.app',['pageTitle'=>$program->title])
@section('content')
@php
$allLessons=$program->modules->flatMap->lessons;
$done=$allLessons->filter(fn($lesson)=>optional($progress->get($lesson->id))->status==='completed')->count();
$percent=$allLessons->count()? (int)round(($done/$allLessons->count())*100):0;
$audienceLabel=$program->audience==='guardian'?'ORANG TUA':($program->audience==='teacher'?'GURU':($program->audience==='admin'?'PENGELOLA':'BERSAMA'));
@endphp
<div class="academy-course-toolbar">
    <a class="academy-course-back" href="{{ route($academyRoutePrefix.'index') }}">← Kembali ke Academy</a>
    <span class="academy-course-chip">{{ $audienceLabel }}</span>
</div>
<header class="academy-course-head">
    <span class="eyebrow">PROGRAM ACADEMY</span><h1>{{ $program->title }}</h1><p>{{ $program->description ?: $program->summary }}</p>
</header>
<div class="academy-course-progress card">
    <div class="academy-course-progress-top"><strong>{{ $percent }}%</strong><div><span>Progres Anda</span><small>{{ $done }} dari {{ $allLessons->count() }} materi selesai</small></div></div>
    <div class="academy-progress"><span style="width:{{ $percent }}%"></span></div>
    <p class="hint">Tidak perlu terburu-buru. Ambil satu materi yang paling relevan untuk keluarga atau kelas hari ini.</p>
</div>
<div class="academy-course-modules">
@foreach($program->modules as $module)
<section class="academy-course-module card">
    <div class="academy-course-module-head"><div><small>MODUL</small><h2>{{ $module->title }}</h2><p>{{ $module->summary }}</p></div><span>{{ $module->lessons->count() }} materi</span></div>
    <div class="academy-course-lessons">
    @foreach($module->lessons as $index=>$lesson)
        @php($item=$progress->get($lesson->id))
        <a class="academy-course-lesson {{ $item?->status === 'completed' ? 'completed' : '' }}" href="{{ route($academyRoutePrefix.'lesson',$lesson) }}">
            <span class="academy-course-number">{{ $item?->status === 'completed' ? '✓' : $index + 1 }}</span>
            <span><strong>{{ $lesson->title }}</strong><small>{{ ucfirst($lesson->lesson_type) }} · ± {{ $lesson->duration_minutes ?? 5 }} menit</small></span>
            <span class="academy-course-arrow">→</span>
        </a>
    @endforeach
    </div>
</section>
@endforeach
</div>
@endsection
