@extends($academyLayout ?? 'layouts.app',['pageTitle'=>'Materi Academy'])
@section('content')
<?php
$typeLabels = ['article'=>'BACAAN','activity'=>'AKTIVITAS KELUARGA','checklist'=>'CHECKLIST','video'=>'VIDEO','audio'=>'AUDIO','pdf'=>'PDF','link'=>'TAUTAN'];
$mediaUrl = trim((string) $lesson->media_url);
$youtubeId = null;
$isShort = false;
if ($mediaUrl !== '') {
    if (preg_match('~youtube\.com/shorts/([A-Za-z0-9_-]{6,})~i', $mediaUrl, $match)) {
        $youtubeId = $match[1]; $isShort = true;
    } elseif (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~i', $mediaUrl, $match)) {
        $youtubeId = $match[1];
    }
}
$embedUrl = $youtubeId ? 'https://www.youtube-nocookie.com/embed/'.$youtubeId.'?rel=0&modestbranding=1&playsinline=1' : null;
$paragraphs = preg_split('/\R\R+/', trim((string) $lesson->body)) ?: [];
$isDirectAudio = $lesson->lesson_type === 'audio' && $mediaUrl !== '' && !$youtubeId;
?>
<article class="academy-premium-lesson">
    <div class="academy-course-toolbar">
        <a class="academy-course-back" href="{{ route($academyRoutePrefix.'program',$lesson->module->program) }}">← Kembali ke program</a>
        <span class="academy-course-chip">{{ $typeLabels[$lesson->lesson_type] ?? strtoupper($lesson->lesson_type) }}</span>
    </div>
    <header class="academy-premium-lesson-head"><span class="eyebrow">{{ $lesson->module->program->title }}</span><h1>{{ $lesson->title }}</h1><p>{{ $lesson->summary }}</p><span>± {{ $lesson->duration_minutes ?? 5 }} menit</span></header>

    @if($embedUrl)
        <section class="academy-video-stage {{ $isShort ? 'is-short-stage' : '' }}" data-academy-video-stage aria-label="Video Academy">
            <div class="academy-video-toolbar"><div><strong>Video Academy</strong><small>{{ $isShort ? 'Format vertikal · ditampilkan utuh' : 'Format lebar · ditampilkan utuh' }}</small></div><button type="button" class="academy-video-fullscreen" data-academy-video-fullscreen aria-pressed="false"><span data-fullscreen-label>Layar penuh</span></button></div>
            <div class="academy-video-shell {{ $isShort ? 'is-short' : '' }}"><iframe src="{{ $embedUrl }}" title="{{ $lesson->title }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>
            <p class="academy-video-note">Video digunakan sebagai materi pendamping. Arahan utama tetap mengikuti isi program dan kebutuhan pembinaan. · <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer" style="color:#f2cd77">Buka sumber video ↗</a></p>
        </section>
    @elseif($isDirectAudio)
        <section class="academy-media-premium"><div><strong>Audio Academy</strong><p>Dengarkan secukupnya, kemudian lanjutkan aktivitas pada materi.</p><audio controls preload="metadata" style="width:min(720px,100%);margin-top:12px"><source src="{{ $mediaUrl }}"></audio></div></section>
    @elseif($mediaUrl !== '')
        <section class="academy-media-premium"><div><strong>Materi pendamping</strong><p>Buka media pada sumber aslinya.</p></div><a class="button primary" href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer">Buka media</a></section>
    @endif

    @if(count($paragraphs))<section class="academy-premium-reading card">@foreach($paragraphs as $paragraph)<p>{!! nl2br(e($paragraph)) !!}</p>@endforeach</section>@endif
    @if($lesson->requires_action)<section class="academy-premium-action"><x-icon name="values"/><div><strong>Satu langkah kecil sudah cukup.</strong><p>Pilih tindakan yang realistis. Academy membantu komunikasi dan pembinaan, bukan menambah tekanan.</p></div></section>@endif
    <form method="post" action="{{ route($academyRoutePrefix.'lesson.complete',$lesson) }}" class="academy-complete-form">@csrf<button class="button primary wide academy-complete-button" type="submit">{{ $progress->status==='completed'?'✓ Materi sudah selesai':'Tandai selesai' }}</button></form>
    <nav class="academy-lesson-nav">
        @if($previous)<a href="{{ route($academyRoutePrefix.'lesson',$previous) }}">← Sebelumnya</a>@else<span></span>@endif
        @if($next)<a href="{{ route($academyRoutePrefix.'lesson',$next) }}">Berikutnya →</a>@else<a href="{{ route($academyRoutePrefix.'program',$lesson->module->program) }}">Kembali ke program →</a>@endif
    </nav>
</article>
@endsection
