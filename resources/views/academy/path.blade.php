@extends('layouts.academy',['pageTitle'=>$path->title])
@section('content')
<div class="academy-path-hero-detail">
    <a href="{{ route('academy.portal.paths') }}">← Semua jalur</a>
    <span class="eyebrow">{{ strtoupper($path->category ?? 'LEARNING PATH') }}</span>
    <h1>{{ $path->title }}</h1>
    <p>{{ $path->summary }}</p>
    <div class="academy-path-progress-line"><span style="width:{{ $pathProgress['percent'] }}%"></span></div>
    <small>{{ $pathProgress['percent'] }}% · {{ $pathProgress['done'] }} dari {{ $pathProgress['total'] }} langkah wajib selesai</small>
</div>

<section class="academy-path-timeline">
@foreach($path->items as $index => $item)
    @php
        $lesson = $item->item_type === 'lesson' ? ($lessons[$item->item_id] ?? null) : null;
        $preset = $item->item_type === 'quran_preset' ? ($presets[$item->item_id] ?? null) : null;
        $done = $lesson ? $completedLessons->contains($lesson->id) : ($preset ? $completedPresets->contains($preset->id) : false);
        $url = $lesson ? route('academy.portal.lesson',$lesson) : ($preset ? route('academy.portal.audio',['preset'=>$preset->id]) : '#');
        $title = $item->title_override ?: ($lesson?->title ?: $preset?->title ?: 'Aktivitas Academy');
    @endphp
    <article class="academy-path-step {{ $done ? 'done' : '' }}">
        <div class="academy-step-number">{{ $done ? '✓' : $index + 1 }}</div>
        <div class="academy-step-copy">
            <span>{{ $item->item_type === 'quran_preset' ? 'LATIHAN QUR’AN' : strtoupper($lesson?->lesson_type ?? $item->item_type) }} {{ $item->is_required ? '· WAJIB' : '· OPSIONAL' }}</span>
            <h3>{{ $title }}</h3>
            @if($item->instruction)<p>{{ $item->instruction }}</p>@elseif($lesson?->summary)<p>{{ $lesson->summary }}</p>@endif
        </div>
        @if($url !== '#')<a class="button secondary" href="{{ $url }}">{{ $done ? 'Buka lagi' : 'Mulai' }}</a>@endif
    </article>
@endforeach
</section>
@endsection
