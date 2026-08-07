@extends('layouts.academy',['pageTitle'=>'Audio & Latihan Al-Qur’an'])
@section('content')
<div class="academy-quran-page" data-academy-quran
     data-playlist-url="{{ route('academy.portal.audio.playlist') }}"
     data-session-url="{{ route('academy.portal.audio.sessions.start') }}"
     data-session-complete-template="{{ route('academy.portal.audio.sessions.complete',['session'=>'__SESSION__']) }}">
    <section class="academy-quran-hero">
        <div>
            <span class="eyebrow">QURAN LEARNING · TETAP DI ACADEMY</span>
            <h1>Dengar, ulangi, dan jaga dengan tenang.</h1>
            <p>Al-Husary menjadi rujukan utama latihan. Al-Minshawi tersedia sebagai pilihan murāja‘ah. Audio membantu latihan dan tidak menggantikan talaqqi serta koreksi guru.</p>
            <div class="academy-quran-stats">
                <span><b>{{ $timingCount }}/564</b> ayat siap</span>
                <span><b>{{ $sources->count() }}</b> qari</span>
                <span><b>{{ $presets->count() }}</b> preset</span>
            </div>
        </div>
        <div class="academy-qari-stack">
            @forelse($sources as $source)
                <div class="academy-qari-card {{ $source->is_default ? 'primary' : '' }}">
                    <span>{{ $source->is_default ? 'UTAMA' : 'MURĀJA‘AH' }}</span>
                    <strong>{{ $source->reciter_name }}</strong>
                    <small>{{ data_get($source->metadata,'learning_role') ?: $source->rewaya }}</small>
                </div>
            @empty
                <div class="alert danger">Sumber audio belum tersedia. Admin perlu menjalankan sinkronisasi Quran Learning.</div>
            @endforelse
        </div>
    </section>

    @if($featuredPresets->isNotEmpty())
    <section class="academy-section-block">
        <div class="academy-section-heading">
            <div><span class="eyebrow">PILIHAN CEPAT</span><h2>Mulai tanpa mengatur banyak hal</h2><p>Pilih latihan yang sudah disiapkan, lalu tekan putar.</p></div>
        </div>
        <div class="academy-preset-grid">
            @foreach($featuredPresets->take(6) as $preset)
                <div class="academy-preset-shell">
                    <button type="button" class="academy-preset-card" data-load-preset="{{ $preset->id }}">
                        <x-icon name="listen" size="24"/>
                        <span><strong>{{ $preset->title }}</strong><small>{{ $preset->repeat_count === 0 ? '∞' : $preset->repeat_count.'×' }} · {{ $preset->repeat_scope === 'each_item' ? 'per ayat' : 'seluruh bagian' }}</small></span>
                        <b>→</b>
                    </button>
                    <form method="post" action="{{ route('academy.portal.audio.preset.bookmark',$preset) }}" class="academy-preset-save">
                        @csrf
                        <button type="submit" title="{{ $bookmarkedPresetIds->contains($preset->id) ? 'Hapus dari tersimpan' : 'Simpan preset' }}" aria-label="{{ $bookmarkedPresetIds->contains($preset->id) ? 'Hapus dari tersimpan' : 'Simpan preset' }}">{{ $bookmarkedPresetIds->contains($preset->id) ? '★' : '☆' }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <section class="academy-quran-player" id="academy-quran-player">
        <div class="academy-player-empty" data-player-empty>
            <span class="academy-player-orb">▶</span>
            <h2>Siap menemani latihan</h2>
            <p>Pilih preset di atas atau atur latihan sendiri di bawah.</p>
        </div>
        <div class="academy-player-ready" data-player-ready hidden>
            <div class="academy-player-title-row">
                <div><span class="eyebrow" data-player-source>SUMBER AUDIO</span><h2 data-player-title>Latihan Al-Qur’an</h2><p data-player-summary></p></div>
                <span class="academy-repeat-badge" data-player-repeat></span>
            </div>
            <div class="academy-now-playing">
                <small>Sedang dibaca</small>
                <strong data-player-current>—</strong>
                <span data-player-progress>0/0</span>
            </div>
            <audio data-quran-audio preload="metadata"></audio>
            <div class="academy-player-controls">
                <button type="button" data-player-prev aria-label="Ayat sebelumnya">⏮</button>
                <button type="button" class="academy-player-play" data-player-toggle aria-label="Putar atau jeda">▶</button>
                <button type="button" data-player-next aria-label="Ayat berikutnya">⏭</button>
            </div>
            <div class="academy-player-status"><span data-counter-ayah>0/0</span><strong data-counter-item>0/0</strong><span data-counter-time>00:00</span></div>
            <div class="academy-player-track"><span data-player-bar></span></div>
            <div class="academy-player-actions">
                <button type="button" class="button secondary" data-player-stop>Hentikan</button>
            </div>
            <p class="academy-quran-note">Langkah kecil yang dijaga lebih kuat daripada lompatan besar yang rapuh.</p>
        </div>
    </section>

    <section class="academy-section-block academy-quran-builder-wrap">
        <div class="academy-section-heading"><div><span class="eyebrow">ATUR SENDIRI</span><h2>Buat latihan sesuai kebutuhan</h2><p>Surah Juz 30, satu ayat, rentang ayat, halaman, atau rubu‘.</p></div></div>
        <form id="academy-quran-builder" class="academy-quran-builder">
            <label>Qari
                <select name="source_id" required>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}" @selected($defaultSource?->id === $source->id)>{{ $source->is_default ? '★ ' : '' }}{{ $source->reciter_name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Jenis latihan
                <select name="mode" data-quran-mode>
                    <option value="ayah">Satu ayat</option>
                    <option value="range" selected>Beberapa ayat</option>
                    <option value="surah">Satu surah</option>
                    <option value="page">Satu halaman</option>
                    <option value="rubu">Satu rubu’ Juz 30</option>
                </select>
            </label>
            <label data-quran-surah>Surah
                <select name="surah_id">@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select>
            </label>
            <div class="academy-quran-verse-pair" data-quran-verses>
                <label>Ayat mulai<input type="number" name="start_verse" min="1" value="1"></label>
                <span>—</span>
                <label>Ayat akhir<input type="number" name="end_verse" min="1" value="5"></label>
            </div>
            <label data-quran-page hidden>Halaman Mushaf
                <select name="page_number"><option value="">Pilih halaman</option>@foreach($pages as $page)<option value="{{ $page }}">Halaman {{ $page }}</option>@endforeach</select>
            </label>
            <label data-quran-rubu hidden>Rubu’ Juz 30
                <select name="rubu_id"><option value="">Pilih rubu’</option>@foreach($rubus as $rubu)<option value="{{ $rubu->id }}">{{ $rubu->name }}</option>@endforeach</select>
            </label>
            <label>Pengulangan
                <select name="repeat_count"><option value="1">1×</option><option value="3">3×</option><option value="5">5×</option><option value="10" selected>10×</option><option value="20">20×</option><option value="0">Tanpa batas</option></select>
            </label>
            <label>Pola ulang
                <select name="repeat_scope"><option value="each_item" selected>Setiap ayat</option><option value="whole_selection">Seluruh bagian</option></select>
            </label>
            <label>Jeda
                <select name="gap_seconds"><option value="0">Tanpa jeda</option><option value="1" selected>1 detik</option><option value="2">2 detik</option><option value="3">3 detik</option></select>
            </label>
            <label>Kecepatan
                <select name="playback_rate"><option value="0.85">0,85×</option><option value="0.9">0,9×</option><option value="1" selected>1×</option><option value="1.1">1,1×</option></select>
            </label>
            <button type="submit" class="button primary academy-quran-start">Mulai latihan</button>
        </form>
    </section>

    @if($recentSessions->isNotEmpty())
    <section class="academy-section-block">
        <div class="academy-section-heading"><div><span class="eyebrow">RIWAYAT</span><h2>Latihan terakhir</h2><p>Riwayat pribadi untuk membantu menjaga kesinambungan.</p></div></div>
        <div class="academy-history-grid">
            @foreach($recentSessions as $session)
                <article><span>{{ $session->status === 'completed' ? '✓ Selesai' : '• Berhenti' }}</span><strong>{{ data_get($session->selection,'title','Latihan Al-Qur’an') }}</strong><small>{{ $session->started_at?->format('d M Y · H:i') }} · {{ floor(($session->duration_seconds ?? 0)/60) }} menit</small></article>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
