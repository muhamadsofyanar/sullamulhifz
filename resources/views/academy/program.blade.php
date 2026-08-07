@extends('layouts.app',['pageTitle'=>'Academy'])
@section('content')
<div class="page-head academy-program-head"><div><a class="back-link" href="{{ route('academy.index') }}">← Academy</a><span class="eyebrow">{{ strtoupper($program->audience==='guardian'?'PARENT ACADEMY':'ACADEMY GURU') }}</span><h1>{{ $program->title }}</h1><p>{{ $program->description ?: $program->summary }}</p></div></div>
@php($allLessons=$program->modules->flatMap->lessons)
@php($done=$progress->where('status','completed')->count())
@php($percent=$allLessons->count() ? (int)round(($done/$allLessons->count())*100) : 0)
<div class="card academy-overall-progress"><div><strong>{{ $percent }}%</strong><span>Progress Anda</span></div><div class="academy-progress"><span style="width:{{ $percent }}%"></span></div><small>{{ $done }} dari {{ $allLessons->count() }} materi selesai. Tidak perlu terburu-buru.</small></div>

<div class="academy-module-stack">
@foreach($program->modules as $module)
<section class="card academy-module-card">
    <div class="section-head"><div><h2>{{ $module->title }}</h2><p class="hint">{{ $module->summary }}</p></div><span class="badge">{{ $module->lessons->count() }} materi</span></div>
    <div class="academy-lesson-list">
    @foreach($module->lessons as $lesson)
        @php($item=$progress->get($lesson->id))
        <a class="academy-lesson-row {{ $item?->status==='completed'?'completed':'' }}" href="{{ route('academy.lesson',$lesson) }}">
            <span class="academy-lesson-state">{{ $item?->status==='completed'?'✓':($loop->iteration) }}</span>
            <div><strong>{{ $lesson->title }}</strong><small>{{ ['article'=>'Bacaan','activity'=>'Aktivitas','checklist'=>'Checklist','video'=>'Video','audio'=>'Audio','pdf'=>'PDF','link'=>'Tautan'][$lesson->lesson_type] ?? ucfirst($lesson->lesson_type) }} · ± {{ $lesson->duration_minutes ?? 5 }} menit</small></div>
            <span>→</span>
        </a>
    @endforeach
    </div>
</section>
@endforeach
</div>
@endsection
