@extends('layouts.app',['pageTitle'=>'Pustaka Qur’an'])
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">V2.4 · FULL QUR’AN ENGINE</span>
        <h1>Pustaka Mushaf & Audio</h1>
        <p>Kelola korpus 30 juz, 114 surah, 6.236 ayat, 604 halaman Mushaf, dua qari tahfizh, preset pengulangan, dan video terkurasi.</p>
    </div>
    <a class="button secondary" href="{{ route('academy.portal.audio') }}">Buka Academy Qur’an</a>
</div>

<div class="stats-grid four">
    <div class="stat-card"><span>Korpus ayat</span><strong>{{ $corpusStatus['ayahs'] }}/6236</strong></div>
    <div class="stat-card"><span>Juz</span><strong>{{ $corpusStatus['juz'] }}/30</strong></div>
    <div class="stat-card"><span>Qari siap</span><strong>{{ $qariReadyCount }}/{{ $expectedQariCount }}</strong></div>
    <div class="stat-card"><span>Timing audio</span><strong>{{ $timingCount }}/{{ $expectedTimingCount }}</strong></div>
</div>

<section class="card quran-sync-card">
    <div>
        <h2>1. Korpus Full Qur’an</h2>
        <p>{{ $corpusStatus['complete'] ? 'Korpus Uthmani lengkap dan siap dipakai oleh Mushaf Academy.' : 'Sinkronkan korpus terlebih dahulu. Audio penuh membutuhkan master 114 surah dan 6.236 ayat.' }}</p>
        <div class="quran-completion"><span style="width:{{ min(100,round(($corpusStatus['ayahs']/6236)*100)) }}%"></span></div>
        <small>{{ $corpusStatus['surahs'] }}/114 surah · {{ $corpusStatus['ayahs'] }}/6236 ayat · {{ $corpusStatus['juz'] }}/30 juz · {{ $corpusStatus['pages'] }}/604 halaman · {{ $corpusStatus['rubus'] }}/240 rubu‘ · {{ $corpusStatus['source'] }}</small>
    </div>
    <form method="post" action="{{ route('admin.quran-library.sync-corpus') }}">@csrf<button class="button primary">Sinkronkan korpus 30 juz</button></form>
</section>

<section class="card quran-sync-card">
    <div>
        <h2>2. Audio Al-Husary & Al-Minshawi — 30 Juz</h2>
        <p>{{ $qariReadyCount >= $expectedQariCount ? 'Kedua qari sudah memiliki timing lengkap 6.236 ayat.' : 'Sinkronisasi berjalan idempoten: surah yang sudah lengkap tidak diunduh ulang.' }}</p>
        <div class="quran-completion"><span style="width:{{ min(100,round(($timingCount/max(1,$expectedTimingCount))*100)) }}%"></span></div>
        <small>{{ min(100,round(($timingCount/max(1,$expectedTimingCount))*100)) }}% lengkap · sumber utama {{ $surahCount }}/114 surah · {{ $pageCount }} halaman terpetakan</small>
    </div>
    <form method="post" action="{{ route('admin.quran-library.sync') }}">@csrf<button class="button primary">Sinkronkan dua qari</button></form>
</section>

<div class="grid two">
<section class="card">
    <h2>Sumber audio tahfizh</h2>
    @forelse($sources as $source)
        <div class="list-row">
            <div>
                <strong>{{ $source->is_default ? '⭐ ' : '' }}{{ $source->reciter_name }}</strong>
                <small>{{ data_get($source->metadata,'learning_role',$source->is_default ? 'Qari utama' : 'Qari tambahan') }} · {{ $source->timings_count }}/6236 timing</small>
                <p>{{ data_get($source->metadata,'description') }}</p>
                <small>{{ $source->rewaya }} · {{ data_get($source->metadata,'sync_scope','30 juz') }} · Sumber: {{ data_get($source->metadata,'source_attribution','MP3Quran.net') }}</small>
            </div>
            <span class="badge">{{ $source->is_default ? 'utama' : 'murāja‘ah' }}</span>
        </div>
    @empty
        <p class="empty">Belum ada sumber audio.</p>
    @endforelse
</section>

<section class="card">
    <h2>Tambah video terkurasi</h2>
    <p class="hint">Gunakan video yang memiliki izin tayang/sematan. Simpan sebagai draf sampai diperiksa.</p>
    <form class="stack compact" method="post" action="{{ route('admin.quran-library.videos.store') }}">
        @csrf
        <label>Judul<input name="title" required placeholder="Contoh: Talaqqi Al-Fatihah bersama guru"></label>
        <div class="form-grid"><label>Jenis sumber<select name="source_type"><option value="youtube">YouTube</option><option value="direct">MP4 langsung</option></select></label><label>Status<select name="status"><option value="draft">Draf</option><option value="published">Terbit</option></select></label></div>
        <label>URL video<input type="url" name="video_url" required placeholder="https://..."></label>
        <label>Surah<select name="surah_id"><option value="">Umum</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
        <div class="form-grid"><label>Ayat awal<input type="number" name="start_verse" min="1"></label><label>Ayat akhir<input type="number" name="end_verse" min="1"></label></div>
        <div class="form-grid"><label>Mulai detik<input type="number" name="start_seconds" min="0"></label><label>Selesai detik<input type="number" name="end_seconds" min="1"></label></div>
        <label>Saran jumlah ulangan<input type="number" name="default_repeat" min="1" max="100" value="1"></label>
        <label>Catatan<textarea name="notes" rows="3" placeholder="Sumber, qari, tujuan penggunaan, dan hasil pemeriksaan"></textarea></label>
        <button class="button primary">Simpan video</button>
    </form>
</section>
</div>

<section class="card"><div class="section-head"><h2>Preset latihan</h2><span>{{ $presets->total() }} data</span></div>@forelse($presets as $preset)<div class="list-row"><div><strong>{{ $preset->title }}</strong><small>{{ $preset->mode }} · {{ $preset->repeat_count === 0 ? 'tanpa batas' : $preset->repeat_count.'×' }} · {{ $preset->repeat_scope }}</small><p>{{ $preset->description }}</p></div><span class="badge">{{ $preset->is_featured ? 'unggulan' : $preset->status }}</span></div>@empty<p class="empty">Preset akan dibuat setelah sinkronisasi.</p>@endforelse{{ $presets->links() }}</section>
<section class="card"><div class="section-head"><h2>Video tersimpan</h2><span>{{ $videos->count() }}</span></div>@forelse($videos as $video)<div class="list-row"><div><strong>{{ $video->title }}</strong><small>{{ $video->source_type }} · {{ $video->surah?->name_latin ?? 'Umum' }}</small><p>{{ $video->notes }}</p></div><form class="inline-form" method="post" action="{{ route('admin.quran-library.videos.update',$video) }}">@csrf @method('PUT')<input type="hidden" name="title" value="{{ $video->title }}"><input type="hidden" name="default_repeat" value="{{ $video->default_repeat }}"><input type="hidden" name="notes" value="{{ $video->notes }}"><select name="status"><option value="draft" @selected($video->status==='draft')>Draf</option><option value="published" @selected($video->status==='published')>Terbit</option><option value="archived" @selected($video->status==='archived')>Arsip</option></select><button class="button small secondary">Simpan</button></form></div>@empty<p class="empty">Belum ada video. Video tidak diisi otomatis agar tetap terkurasi.</p>@endforelse</section>
@endsection
