@extends($academyLayout ?? 'layouts.app',['pageTitle'=>$title])
@section('content')
<div class="academy-section-heading"><div><span class="eyebrow">{{ strtoupper($eyebrow) }}</span><h1>{{ $title }}</h1><p>Konten yang tersedia untuk peran Anda di Sullamul Ḥifẓ Academy.</p></div><span class="badge">{{ $lessons->count() }} materi</span></div>

@if($section === 'audio' && ($academyStandalone ?? false))
<section class="academy-audio-feature">
    <article class="academy-audio-panel"><span class="eyebrow">QURAN LEARNING</span><h2>Audio Qur’an terhubung dengan latihan.</h2><p>Pilih murattal dan pola pengulangan sesuai target. Audio membantu mendengar dan menirukan, tetapi tidak menggantikan talaqqi dan koreksi guru.</p><div class="academy-audio-source-list">@forelse($quranSources as $source)<span><strong>{{ $source->reciter_name ?: $source->name }}</strong><small>{{ $source->is_default ? 'Utama' : ($source->rewaya ?: 'Aktif') }}</small></span>@empty<span><strong>Pustaka audio</strong><small>Menunggu sinkronisasi</small></span>@endforelse</div></article>
    <article class="academy-audio-action"><x-icon name="listen" size="38"/><h3>Latihan Al-Qur’an</h3><p>{{ $quranPresets->count() }} preset latihan tersedia. Buka Quran Player untuk memilih ayat, surah, rubu‘, pengulangan, dan qari.</p><a class="button primary" href="{{ $quranPracticeUrl }}">Buka Audio Qur’an →</a></article>
</section>
@endif

<div class="academy-catalog-grid">
@forelse($lessons as $lesson)
<a class="academy-content-card" href="{{ route($academyRoutePrefix.'lesson',$lesson) }}">
    <div class="academy-content-card-top"><span class="academy-content-type">{{ strtoupper($lesson->lesson_type) }}</span>@if($completedIds->contains($lesson->id))<span class="badge">✓ Selesai</span>@endif</div>
    <h3>{{ $lesson->title }}</h3><p>{{ $lesson->summary }}</p>
    <footer><span>{{ $lesson->module->program->title }}</span><strong>± {{ $lesson->duration_minutes ?? 5 }} menit →</strong></footer>
</a>
@empty
<div class="academy-empty">Belum ada {{ strtolower($eyebrow) }} yang diterbitkan. Admin dapat menambahkannya melalui menu Kelola Academy.</div>
@endforelse
</div>
@endsection
