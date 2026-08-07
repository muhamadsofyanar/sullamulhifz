@extends('layouts.academy',['pageTitle'=>'Mushaf & Audio Al-Qur’an'])
@section('content')
<div class="academy-quran-page" data-academy-quran
     data-playlist-url="{{ route('academy.portal.audio.playlist') }}"
     data-session-url="{{ route('academy.portal.audio.sessions.start') }}"
     data-session-complete-template="{{ route('academy.portal.audio.sessions.complete',['session'=>'__SESSION__']) }}"
     data-ayah-bookmark-template="{{ route('academy.portal.audio.ayah.bookmark',['globalNumber'=>'__AYAH__']) }}"
     data-bookmarked-ayahs='@json($bookmarkedAyahGlobals->values())'>

    <section class="academy-quran-hero">
        <div>
            <span class="eyebrow">FULL QUR’AN · 30 JUZ · MUSHAF + AUDIO</span>
            <h1>Baca mushaf, dengar, ulangi, dan jaga.</h1>
            <p>Quran Learning v2.4 menghubungkan teks Uthmani, 30 juz, 114 surah, halaman Mushaf, Rubu‘ al-Hizb, Al-Husary, dan Al-Minshawi dalam satu ruang Academy. Audio membantu latihan dan tidak menggantikan talaqqi serta koreksi guru.</p>
            <div class="academy-quran-stats">
                <span><b>{{ $corpusStatus['ayahs'] }}/6236</b> ayat mushaf</span>
                <span><b>{{ $corpusStatus['juz'] }}/30</b> juz</span>
                <span><b>{{ $corpusStatus['pages'] }}/604</b> halaman</span>
                <span><b>{{ $timingCount }}/{{ $expectedTimingCount }}</b> audio utama</span>
            </div>
            @if($readingProgress)
                <a href="#academy-quran-builder" class="academy-resume-chip">Lanjut terakhir: {{ $readingProgress->surah?->name_latin ?? 'Surah' }} ayat {{ $readingProgress->verse_number }} · Juz {{ $readingProgress->juz_number ?? '—' }}</a>
            @endif
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

    @if(!$corpusStatus['complete'])
        <div class="academy-corpus-warning"><strong>Korpus 30 juz sedang disiapkan.</strong><span>Admin perlu menjalankan sinkronisasi Full Qur’an. Halaman Academy tetap tersedia, tetapi pilihan lengkap baru muncul setelah 6.236 ayat tersimpan.</span></div>
    @endif

    @if($featuredPresets->isNotEmpty())
    <section class="academy-section-block">
        <div class="academy-section-heading">
            <div><span class="eyebrow">PILIHAN CEPAT</span><h2>Mulai tanpa mengatur banyak hal</h2><p>Preset dapat berasal dari surah, juz, atau latihan tahfizh yang direkomendasikan.</p></div>
        </div>
        <div class="academy-preset-grid">
            @foreach($featuredPresets->take(8) as $preset)
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
            <span class="academy-player-orb">۞</span>
            <h2>Mushaf siap dibaca</h2>
            <p>Pilih bagian Al-Qur’an di bawah. Teks mushaf akan tetap tampil bersama pemutar audio.</p>
        </div>

        <div class="academy-player-ready" data-player-ready hidden>
            <div class="academy-player-title-row">
                <div><span class="eyebrow" data-player-source>SUMBER AUDIO</span><h2 data-player-title>Latihan Al-Qur’an</h2><p data-player-summary></p></div>
                <span class="academy-repeat-badge" data-player-repeat></span>
            </div>

            <div class="academy-mushaf-shell">
                <div class="academy-mushaf-toolbar">
                    <span data-mushaf-location>Juz — · Halaman —</span>
                    <span data-mushaf-surah>Surah</span>
                </div>
                <div class="academy-mushaf-verses" data-mushaf-verses aria-live="polite"></div>
            </div>

            <div class="academy-now-playing">
                <small>Sedang dibaca</small>
                <strong data-player-current>—</strong>
                <span data-player-progress>0/0</span>
            </div>
            <div class="academy-audio-sync-note" data-audio-sync-note hidden>Audio untuk sebagian ayat masih disinkronkan. Mushaf dapat dibaca, tetapi bagian ini belum dapat diputar lengkap.</div>
            <audio data-quran-audio preload="metadata"></audio>
            <div class="academy-player-controls">
                <button type="button" data-player-prev aria-label="Ayat sebelumnya">⏮</button>
                <button type="button" class="academy-player-play" data-player-toggle aria-label="Putar atau jeda">▶</button>
                <button type="button" data-player-next aria-label="Ayat berikutnya">⏭</button>
            </div>
            <div class="academy-player-status"><span data-counter-ayah>0/0</span><strong data-counter-item>0/0</strong><span data-counter-time>00:00</span></div>
            <div class="academy-player-track"><span data-player-bar></span></div>
            <div class="academy-player-actions"><button type="button" class="button secondary" data-player-ayah-bookmark>☆ Simpan ayat</button><button type="button" class="button secondary" data-player-stop>Hentikan</button></div>
            <p class="academy-quran-note">Murāja‘ah adalah nafas hafalan. Yang telah dibawa perlu terus dijaga.</p>
        </div>
    </section>

    @if($defaultMushaf->isNotEmpty())
    <section class="academy-default-mushaf" data-default-mushaf>
        <div class="academy-section-heading"><div><span class="eyebrow">MUSHAF</span><h2>{{ $defaultMushaf->first()?->surah?->name_latin }}</h2><p>Contoh mushaf tampil bahkan sebelum pemutar dimulai.</p></div></div>
        <div class="academy-default-mushaf-text" dir="rtl">
            @foreach($defaultMushaf as $ayah)
                <span>{{ $ayah->arabic_text }} <b>﴿{{ $ayah->verse_number }}﴾</b></span>
            @endforeach
        </div>
    </section>
    @endif

    <section class="academy-section-block academy-quran-builder-wrap" id="academy-quran-builder">
        <div class="academy-section-heading"><div><span class="eyebrow">PILIH BAGIAN</span><h2>Seluruh 30 juz dalam satu pemutar</h2><p>Pilih berdasarkan juz, surah, ayat, halaman Mushaf, atau Rubu‘ al-Hizb standar.</p></div></div>
        <form class="academy-quran-builder">
            <label>Qari
                <select name="source_id" required>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}" @selected($defaultSource?->id === $source->id)>{{ $source->is_default ? '★ ' : '' }}{{ $source->reciter_name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Jenis latihan
                <select name="mode" data-quran-mode>
                    <option value="range" selected>Beberapa ayat</option>
                    <option value="ayah">Satu ayat</option>
                    <option value="surah">Satu surah</option>
                    <option value="juz">Satu juz</option>
                    <option value="page">Satu halaman Mushaf</option>
                    <option value="hizb_quarter">Satu Rubu‘ al-Hizb</option>
                    <option value="rubu">Milestone Juz 30 Sullam</option>
                </select>
            </label>
            <label data-quran-surah>Surah
                <select name="surah_id">@foreach($surahs as $surah)<option value="{{ $surah->id }}" @selected($surah->id===($readingProgress?->surah_id ?? 1))>{{ $surah->id }}. {{ $surah->name_latin }} — {{ $surah->name_arabic }}</option>@endforeach</select>
            </label>
            <div class="academy-quran-verse-pair" data-quran-verses>
                <label>Ayat mulai<input type="number" name="start_verse" min="1" value="{{ $readingProgress?->verse_number ?? 1 }}"></label>
                <span>—</span>
                <label>Ayat akhir<input type="number" name="end_verse" min="1" value="{{ $readingProgress?->verse_number ?? 7 }}"></label>
            </div>
            <label data-quran-juz hidden>Juz
                <select name="juz_number"><option value="">Pilih juz</option>@foreach($juzs as $juz)<option value="{{ $juz }}" @selected($juz===($readingProgress?->juz_number ?? 30))>Juz {{ $juz }}</option>@endforeach</select>
            </label>
            <label data-quran-page hidden>Halaman Mushaf
                <select name="page_number"><option value="">Pilih halaman</option>@foreach($pages as $page)<option value="{{ $page }}" @selected($page===($readingProgress?->page_number ?? null))>Halaman {{ $page }}</option>@endforeach</select>
            </label>
            <label data-quran-hizb-quarter hidden>Rubu‘ al-Hizb
                <select name="hizb_quarter"><option value="">Pilih rubu‘</option>@foreach($hizbQuarters as $quarter)<option value="{{ $quarter }}" @selected($quarter===($readingProgress?->hizb_quarter ?? null))>Rubu‘ {{ $quarter }} / 240</option>@endforeach</select>
            </label>
            <label data-quran-rubu hidden>Milestone Juz 30 Sullam
                <select name="rubu_id"><option value="">Pilih milestone</option>@foreach($rubus as $rubu)<option value="{{ $rubu->id }}">{{ $rubu->name }}</option>@endforeach</select>
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
            <button type="submit" class="button primary academy-quran-start">Tampilkan mushaf & mulai</button>
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

    <footer class="academy-quran-source-note">Teks Uthmani: AlQuran.cloud. Timing dan audio: MP3Quran.net. Sullamul Ḥifẓ menyajikannya untuk pembelajaran; talaqqi dan verifikasi guru tetap menjadi rujukan utama.</footer>
</div>
@endsection
