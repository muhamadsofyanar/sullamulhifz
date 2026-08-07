@extends('layouts.app',['pageTitle'=>'Materi Academy'])
@section('content')
<?php
$typeLabels = ['article'=>'BACAAN','activity'=>'AKTIVITAS KELUARGA','checklist'=>'CHECKLIST','video'=>'VIDEO','audio'=>'AUDIO','pdf'=>'PDF','link'=>'TAUTAN'];
$mediaUrl = trim((string) $lesson->media_url);
$youtubeId = null;
$isShort = false;
if ($mediaUrl !== '') {
    if (preg_match('~youtube\.com/shorts/([A-Za-z0-9_-]{6,})~i', $mediaUrl, $match)) {
        $youtubeId = $match[1];
        $isShort = true;
    } elseif (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~i', $mediaUrl, $match)) {
        $youtubeId = $match[1];
    }
}
$embedUrl = $youtubeId
    ? 'https://www.youtube-nocookie.com/embed/'.$youtubeId.'?rel=0&modestbranding=1&playsinline=1'
    : null;
$paragraphs = preg_split('/\R\R+/', trim((string) $lesson->body)) ?: [];
?>
<article class="academy-premium-lesson">
    <div class="academy-course-toolbar">
        <a class="academy-course-back" href="{{ route('academy.program',$lesson->module->program) }}">← Kembali ke program</a>
        <span class="academy-course-chip"><?= e($typeLabels[$lesson->lesson_type] ?? strtoupper($lesson->lesson_type)) ?></span>
    </div>

    <header class="academy-premium-lesson-head">
        <h1>{{ $lesson->title }}</h1>
        <p>{{ $lesson->summary }}</p>
        <span>± {{ $lesson->duration_minutes ?? 5 }} menit</span>
    </header>

    <?php if ($embedUrl): ?>
        <section class="academy-video-stage <?= $isShort ? 'is-short-stage' : '' ?>" data-academy-video-stage aria-label="Video Academy">
            <div class="academy-video-toolbar">
                <div>
                    <strong>Video Academy</strong>
                    <small><?= $isShort ? 'Format vertikal · ditampilkan utuh' : 'Format lebar · ditampilkan utuh' ?></small>
                </div>
                <button type="button" class="academy-video-fullscreen" data-academy-video-fullscreen aria-pressed="false">
                    <span data-fullscreen-label>Layar penuh</span>
                </button>
            </div>
            <div class="academy-video-shell <?= $isShort ? 'is-short' : '' ?>">
                <iframe
                    src="<?= e($embedUrl) ?>"
                    title="<?= e($lesson->title) ?>"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen></iframe>
            </div>
            <p class="academy-video-note">Video mengikuti rasio aslinya agar bagian atas, bawah, dan sisi video tidak terpotong.</p>
        </section>
    <?php elseif ($mediaUrl !== ''): ?>
        <section class="academy-media-premium">
            <div><strong>Materi pendamping</strong><p>Buka media pada sumber aslinya.</p></div>
            <a class="button primary" href="<?= e($mediaUrl) ?>" target="_blank" rel="noopener noreferrer">Buka media</a>
        </section>
    <?php endif; ?>

    <?php if (count($paragraphs)): ?>
    <section class="academy-premium-reading card">
        <?php foreach ($paragraphs as $paragraph): ?>
            <p><?= nl2br(e($paragraph)) ?></p>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if ($lesson->requires_action): ?>
    <section class="academy-premium-action">
        <x-icon name="values"/>
        <div><strong>Satu langkah kecil sudah cukup.</strong><p>Pilih tindakan yang realistis untuk keluarga. Academy membantu komunikasi dan pembinaan, bukan menambah tekanan.</p></div>
    </section>
    <?php endif; ?>

    <form method="post" action="{{ route('academy.lesson.complete',$lesson) }}" class="academy-complete-form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button class="button primary wide academy-complete-button" type="submit">{{ $progress->status==='completed'?'✓ Materi sudah selesai':'Tandai selesai' }}</button>
    </form>

    <nav class="academy-lesson-nav">
        <?php if ($previous): ?><a href="<?= e(route('academy.lesson',$previous)) ?>">← Sebelumnya</a><?php else: ?><span></span><?php endif; ?>
        <?php if ($next): ?><a href="<?= e(route('academy.lesson',$next)) ?>">Berikutnya →</a><?php else: ?><a href="<?= e(route('academy.program',$lesson->module->program)) ?>">Kembali ke program →</a><?php endif; ?>
    </nav>
</article>
@endsection
