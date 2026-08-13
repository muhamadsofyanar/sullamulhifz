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
$normalizedBody = str_replace(['\\r\\n','\\n','\\r'], "\n", (string) $lesson->body);
$paragraphs = preg_split('/\R\R+/', trim($normalizedBody)) ?: [];
$isDirectAudio = $lesson->lesson_type === 'audio' && $mediaUrl !== '' && !$youtubeId;
?>
<article class="academy-premium-lesson">
    <div class="academy-course-toolbar">
        <a class="academy-course-back" href="{{ route($academyRoutePrefix.'program',$lesson->module->program) }}">← Kembali ke program</a>
        <span class="academy-course-chip">{{ $typeLabels[$lesson->lesson_type] ?? strtoupper($lesson->lesson_type) }}</span>
    </div>
    <header class="academy-premium-lesson-head"><span class="eyebrow">{{ $lesson->module->program->title }}</span><h1>{{ $lesson->title }}</h1><p>{{ $lesson->summary }}</p><span>± {{ $lesson->duration_minutes ?? 5 }} menit</span></header>
    @if($academyStandalone ?? false)
    <div class="academy-lesson-utility-row">
        <form method="post" action="{{ route('academy.portal.lesson.bookmark',$lesson) }}">@csrf
            <button type="submit" class="button secondary"><x-icon name="preservation" size="18"/> {{ $isBookmarked ? 'Hapus dari tersimpan' : 'Simpan materi' }}</button>
        </form>
        @if($learningPathsEnabled ?? true)<a class="button secondary" href="{{ route('academy.portal.paths') }}"><x-icon name="continuity" size="18"/> Jalur belajar</a>@endif
    </div>
    @endif

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

    @if(($academyStandalone ?? false) && ($reflectionEnabled ?? true))
    <section class="academy-reflection-card">
        <div><span class="eyebrow">REFLEKSI PRIBADI</span><h2>Apa yang ingin Anda bawa dari materi ini?</h2><p>Catatan ini bersifat pribadi. Simpan hal yang benar-benar berguna untuk tindakan berikutnya.</p></div>
        <form method="post" action="{{ route('academy.portal.lesson.reflection',$lesson) }}" class="academy-reflection-form">
            @csrf
            @if($reflectionStudents->isNotEmpty())
            <label>Terkait anak, opsional<select name="student_id"><option value="">Tidak memilih anak</option>@foreach($reflectionStudents as $student)<option value="{{ $student->id }}">{{ $student->full_name }}</option>@endforeach</select></label>
            @endif
            <label>Refleksi<textarea name="reflection" rows="4" maxlength="3000" required placeholder="Satu hal yang saya pahami atau ingin saya perbaiki..."></textarea></label>
            <label>Tindak lanjut kecil, opsional<input name="follow_up" maxlength="255" placeholder="Contoh: latihan 10 menit setelah Magrib selama 3 hari"></label>
            <button class="button secondary" type="submit">Simpan refleksi</button>
        </form>
        @if($reflections->isNotEmpty())
        <div class="academy-reflection-history">
            @foreach($reflections as $reflection)
                <article><small>{{ $reflection->created_at?->format('d M Y · H:i') }}</small><p>{{ $reflection->reflection }}</p>@if($reflection->follow_up)<strong>Langkah kecil: {{ $reflection->follow_up }}</strong>@endif</article>
            @endforeach
        </div>
        @endif
    </section>
    @endif

    @if($quiz)
    @php
        $passedAttempt=$quizAttempts->first(fn($attempt)=>$attempt->passed);
    @endphp
    <section class="card" style="margin-top:18px">
        <div class="section-head"><div><span class="eyebrow">KUIS</span><h2>{{ $quiz->title }}</h2><p class="muted">{{ $quiz->instructions ?: 'Jawab pertanyaan untuk mengonfirmasi pemahaman materi.' }}</p></div><span class="badge">Lulus ≥ {{ $quiz->passing_percent }}%</span></div>
        @if($passedAttempt)
            <p><strong>✓ Lulus {{ $passedAttempt->percent }}%</strong> pada percobaan ke-{{ $passedAttempt->attempt_number }}.</p>
        @elseif($quizAttempts->count() >= $quiz->max_attempts)
            <p class="muted">Batas {{ $quiz->max_attempts }} percobaan sudah digunakan. Hubungi pengelola Academy bila perlu dibuka kembali.</p>
        @else
            <form method="post" action="{{ route($academyRoutePrefix.'quiz.submit',$quiz) }}" class="stack">@csrf
                @foreach($quiz->questions as $question)
                <fieldset class="card" style="margin:0"><legend><strong>{{ $loop->iteration }}. {{ $question->prompt }}</strong></legend>
                    @foreach($question->options as $option)<label class="check"><input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" required> {{ $option->label }}</label>@endforeach
                </fieldset>
                @endforeach
                <button class="button secondary" type="submit" @disabled($quiz->questions->isEmpty())>Periksa jawaban</button>
            </form>
        @endif
    </section>
    @endif

    @if($worksheet)
    <section class="card" style="margin-top:18px">
        <div class="section-head"><div><span class="eyebrow">WORKSHEET</span><h2>{{ $worksheet->title }}</h2><p class="muted">{{ $worksheet->instructions }}</p></div>@if($worksheet->is_required)<span class="badge">Wajib</span>@endif</div>
        @if($worksheetSubmission?->status === 'completed')
            <p><strong>✓ Worksheet selesai dan tersimpan.</strong></p>
            @if($worksheetSubmission->response)<p>{{ $worksheetSubmission->response }}</p>@endif
        @else
            <form method="post" action="{{ route($academyRoutePrefix.'worksheet.submit',$worksheet) }}" class="stack">@csrf
                @if($worksheet->completion_mode === 'reflection')<label>Jawaban/refleksi<textarea name="response" rows="5" maxlength="10000" required placeholder="Tuliskan jawaban atau tindak lanjut Anda..."></textarea></label>@else<input type="hidden" name="response" value="Sudah dipraktikkan">@endif
                <button class="button secondary" type="submit">{{ $worksheet->completion_mode === 'reflection' ? 'Simpan worksheet' : 'Tandai sudah dipraktikkan' }}</button>
            </form>
        @endif
    </section>
    @endif

    <form method="post" action="{{ route($academyRoutePrefix.'lesson.complete',$lesson) }}" class="academy-complete-form">@csrf<button class="button primary wide academy-complete-button" type="submit" @disabled(!$requirementsComplete && $progress->status!=='completed')>{{ $progress->status==='completed'?'✓ Materi sudah selesai':($requirementsComplete?'Tandai selesai':'Selesaikan kuis/worksheet dahulu') }}</button></form>
    <nav class="academy-lesson-nav">
        @if($previous)<a href="{{ route($academyRoutePrefix.'lesson',$previous) }}">← Sebelumnya</a>@else<span></span>@endif
        @if($next)<a href="{{ route($academyRoutePrefix.'lesson',$next) }}">Berikutnya →</a>@else<a href="{{ route($academyRoutePrefix.'program',$lesson->module->program) }}">Kembali ke program →</a>@endif
    </nav>
</article>
@endsection
