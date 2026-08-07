@extends('layouts.app', ['pageTitle' => 'Pembinaan Jumat'])

@section('content')
<div class="page-head">
    <div>
        <h1>Pembinaan Jumat</h1>
        <p>Jejak tema pembinaan untuk dilanjutkan melalui percakapan sederhana di rumah.</p>
    </div>
</div>

<div class="cards-list">
    @forelse ($items as $item)
        <article class="card content-card">
            <span class="eyebrow">
                {{ str_replace('_', ' ', $item->category) }} ·
                {{ $item->schoolClass?->name ?? 'Seluruh Kelas' }}
            </span>

            <h2>{{ $item->title }}</h2>
            <small>{{ $item->session_date->format('d M Y') }}</small>

            @if ($item->objectives)
                <h3>Tujuan</h3>
                <p>{{ $item->objectives }}</p>
            @endif

            <h3>Ringkasan</h3>
            <div class="prose">{!! nl2br(e($item->summary)) !!}</div>

            @if ($item->surah)
                <p>
                    <strong>Ayat terkait:</strong>
                    {{ $item->surah->name_latin }}

                    @if ($item->quran_start_verse)
                        {{ $item->quran_start_verse }}–{{ $item->quran_end_verse }}
                    @endif
                </p>
            @endif

            @if ($item->home_follow_up)
                <div class="follow-up">
                    <strong>Percakapan/tindak lanjut di rumah</strong>
                    <p>{{ $item->home_follow_up }}</p>
                </div>
            @endif
            @if ($item->media_url || $item->worksheet_media_id || $item->worksheet_path)
                <div class="form-actions">
                    @if($item->media_url)<a class="button secondary small" href="{{ $item->media_url }}" target="_blank" rel="noopener">Buka audio/video</a>@endif
                    @if($item->worksheet_media_id || $item->worksheet_path)<a class="button secondary small" href="{{ route('media.friday',$item) }}" target="_blank" rel="noopener">Lembar aktivitas</a>@endif
                </div>
            @endif
        </article>
    @empty
        <div class="card empty">Belum ada Pembinaan Jumat.</div>
    @endforelse
</div>

{{ $items->links() }}
@endsection
