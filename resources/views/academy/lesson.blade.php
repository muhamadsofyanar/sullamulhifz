@extends('layouts.app',['pageTitle'=>'Materi Academy'])
@section('content')
<article class="academy-lesson-shell">
    <a class="back-link" href="{{ route('academy.program',$lesson->module->program) }}">← {{ $lesson->module->program->title }}</a>
    <header class="academy-lesson-header">
        <span class="academy-lesson-type">{{ strtoupper(['article'=>'BACAAN','activity'=>'AKTIVITAS KELUARGA','checklist'=>'CHECKLIST','video'=>'VIDEO','audio'=>'AUDIO','pdf'=>'PDF','link'=>'TAUTAN'][$lesson->lesson_type] ?? $lesson->lesson_type) }}</span>
        <h1>{{ $lesson->title }}</h1><p>{{ $lesson->summary }}</p><div class="academy-time">± {{ $lesson->duration_minutes ?? 5 }} menit</div>
    </header>

    @if($lesson->media_url)
    <div class="academy-media-card"><p>Materi media pendamping tersedia.</p><a class="button primary" href="{{ $lesson->media_url }}" target="_blank" rel="noopener noreferrer">Buka media</a></div>
    @endif

    <div class="academy-reading card">
        @foreach(preg_split('/\R\R+/', trim((string)$lesson->body)) as $paragraph)
            @if(str_contains($paragraph,"\n"))
                <div class="academy-text-block">{!! nl2br(e($paragraph)) !!}</div>
            @else
                <p>{{ $paragraph }}</p>
            @endif
        @endforeach
    </div>

    @if($lesson->requires_action)
    <div class="academy-action-note"><x-icon name="values"/><div><strong>Praktikkan secukupnya.</strong><p>Tujuannya bukan mengejar checklist, tetapi memilih satu tindakan yang realistis dan dapat dijaga.</p></div></div>
    @endif

    <form method="post" action="{{ route('academy.lesson.complete',$lesson) }}" class="academy-complete-form">@csrf
        <button class="button primary wide academy-complete-button" type="submit">{{ $progress->status==='completed'?'✓ Materi sudah selesai':'Tandai selesai' }}</button>
    </form>

    <nav class="academy-lesson-nav">
        @if($previous)<a href="{{ route('academy.lesson',$previous) }}">← Sebelumnya</a>@else<span></span>@endif
        @if($next)<a href="{{ route('academy.lesson',$next) }}">Berikutnya →</a>@else<a href="{{ route('academy.program',$lesson->module->program) }}">Kembali ke program →</a>@endif
    </nav>
</article>
@endsection
