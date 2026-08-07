@extends('layouts.academy',['pageTitle'=>'Tersimpan'])
@section('content')
<div class="academy-page-head"><div><span class="eyebrow">TERSIMPAN</span><h1>Materi yang ingin Anda buka kembali.</h1><p>Simpan hanya yang relevan agar daftar belajar tetap ringan.</p></div></div>
<div class="academy-bookmark-list">
@forelse($rows as $row)
    @php($lesson = $row->bookmark_type === 'lesson' ? ($lessons[$row->bookmark_id] ?? null) : null)
    @php($preset = $row->bookmark_type === 'quran_preset' ? ($presets[$row->bookmark_id] ?? null) : null)
    @php($ayah = $row->bookmark_type === 'quran_ayah' ? ($ayahs[$row->bookmark_id] ?? null) : null)
    @if($lesson)
        <article><x-icon name="lesson" size="24"/><div><span>{{ $lesson->module?->program?->title }}</span><strong>{{ $lesson->title }}</strong><small>{{ $lesson->lesson_type }} · ± {{ $lesson->duration_minutes ?? 5 }} menit</small></div><a href="{{ route('academy.portal.lesson',$lesson) }}">Buka →</a></article>
    @elseif($preset)
        <article><x-icon name="listen" size="24"/><div><span>Audio Qur’an</span><strong>{{ $preset->title }}</strong><small>{{ $preset->repeat_count }}× · {{ $preset->repeat_scope === 'each_item' ? 'per ayat' : 'seluruh bagian' }}</small></div><a href="{{ route('academy.portal.audio',['preset'=>$preset->id]) }}">Latihan →</a></article>
    @elseif($ayah)
        <article><x-icon name="preservation" size="24"/><div><span>Ayat tersimpan · Juz {{ $ayah->juz_number }}</span><strong>{{ $ayah->surah?->name_latin }} ayat {{ $ayah->verse_number }}</strong><small>Halaman {{ $ayah->page_number }} · Rubu‘ {{ $ayah->hizb_quarter }}</small></div><a href="{{ route('academy.portal.audio',['surah'=>$ayah->surah_id,'ayah'=>$ayah->verse_number]) }}">Buka mushaf →</a></article>
    @endif
@empty
    <div class="academy-empty-card"><h2>Belum ada materi tersimpan.</h2><p>Buka sebuah materi lalu gunakan tombol “Simpan”.</p></div>
@endforelse
</div>
@endsection
