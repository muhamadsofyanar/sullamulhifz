@extends('layouts.public')
@section('title', 'Sullamul Ḥifẓ — Bukan Sekadar Hafal, Tapi KUAT')
@section('description', 'Ekosistem pendidikan Al-Qur’an yang membangun perjalanan hafalan secara manusiawi, bertahap, bermakna, dan berkelanjutan.')
@section('content')
<section class="public-hero">
    <div class="public-container hero-grid">
        <div class="hero-copy">
            <span class="public-eyebrow">EKOSISTEM PENDIDIKAN AL-QUR’AN</span>
            <h1>Menumbuhkan hubungan yang <em>kuat</em> dengan Al-Qur’an.</h1>
            <p class="hero-lead">Bukan sekadar menambah hafalan. Sullamul Ḥifẓ membantu lembaga, guru, orang tua, dan peserta menjaga perjalanan dari kesiapan hingga keberlanjutan.</p>
            <div class="hero-actions">
                <a class="public-button primary" href="{{ route('public.programs') }}">Jelajahi program</a>
                <a class="public-button secondary" href="{{ config('sullam.portal_url') ?: route('login') }}">Masuk aplikasi</a>
            </div>
            <div class="hero-trust" aria-label="Prinsip utama">
                <span>Berpusat pada manusia</span>
                <span>Bertahap dan terukur</span>
                <span>Menjaga kesinambungan</span>
            </div>
        </div>
        <div class="hero-visual" aria-label="Empat gerak KUAT">
            <div class="visual-mark"><img src="/brand/logo-mark.svg" alt=""></div>
            <div class="visual-orbit orbit-one"></div>
            <div class="visual-orbit orbit-two"></div>
            <article class="orbit-card card-k"><strong>K</strong><span>Kenali Diri</span></article>
            <article class="orbit-card card-u"><strong>U</strong><span>Ukur Kemampuan</span></article>
            <article class="orbit-card card-a"><strong>A</strong><span>Al-Qur’an Dipahami</span></article>
            <article class="orbit-card card-t"><strong>T</strong><span>Teguh Menjaga</span></article>
        </div>
    </div>
</section>

<section class="public-section problem-section">
    <div class="public-container split-intro">
        <div>
            <span class="public-eyebrow">MENGAPA SULLAMUL ḤIFẒ?</span>
            <h2>Target tetap diperlukan. Tetapi target harus tunduk kepada kesiapan.</h2>
        </div>
        <div class="intro-copy">
            <p>Hafalan dapat bertambah cepat, tetapi belum tentu hidup dalam jangka panjang. Sullamul Ḥifẓ membantu proses pembinaan agar tidak hanya menghitung jumlah, melainkan juga membaca manusia, mutu bacaan, kemampuan murāja‘ah, pemahaman, dan keberlanjutan.</p>
            <a class="text-link" href="{{ route('public.about') }}">Pelajari filosofi kami →</a>
        </div>
    </div>
    <div class="public-container value-grid">
        <article class="value-card"><span class="value-number">01</span><h3>Mulai dari manusia</h3><p>Kondisi, kemampuan, lingkungan, dan riwayat peserta dikenali sebelum beban ditentukan.</p></article>
        <article class="value-card"><span class="value-number">02</span><h3>Tumbuh bertahap</h3><p>Tantangan diberikan dalam ukuran yang dapat dibawa, bukan diseragamkan tanpa membaca kesiapan.</p></article>
        <article class="value-card"><span class="value-number">03</span><h3>Hafalan yang hidup</h3><p>Hafalan baru, hafalan lama, pemahaman, dan pengamalan ditempatkan dalam satu perjalanan.</p></article>
        <article class="value-card"><span class="value-number">04</span><h3>Ada jalan untuk kembali</h3><p>Jeda dan perubahan keadaan tidak dipandang sebagai kegagalan, melainkan fase yang perlu dibimbing.</p></article>
    </div>
</section>

<section class="public-section ecosystem-section">
    <div class="public-container section-heading centered">
        <span class="public-eyebrow">SATU BRAND, BEBERAPA JALUR</span>
        <h2>Ekosistem yang tumbuh bersama kebutuhan nyata.</h2>
        <p>Setiap jalur memiliki fungsi berbeda, tetapi memakai filosofi, identitas, dan arah pembinaan yang sama.</p>
    </div>
    <div class="public-container ecosystem-grid">
        <article class="ecosystem-card featured">
            <div class="ecosystem-icon">TPA</div>
            <span class="status-badge active">Sudah berjalan</span>
            <h3>Sullamul Ḥifẓ TPA</h3>
            <p>Sistem operasional untuk data santri, kelas, guru, Tahsin, Tahfizh, murāja‘ah, laporan, dan komunikasi wali.</p>
            <a href="{{ route('public.tpa') }}">Lihat sistem TPA →</a>
        </article>
        <article class="ecosystem-card">
            <div class="ecosystem-icon">KU</div>
            <span class="status-badge">Bertumbuh</span>
            <h3>Keluarga Qur’ani</h3>
            <p>Panduan bagi orang tua untuk mendampingi tanpa menjadikan rumah sebagai ruang tekanan kedua.</p>
            <a href="{{ route('public.programs') }}#keluarga">Lihat arah program →</a>
        </article>
        <article class="ecosystem-card">
            <div class="ecosystem-icon">AC</div>
            <span class="status-badge upcoming">Segera hadir</span>
            <h3>Sullamul Ḥifẓ Academy</h3>
            <p>Kelas digital bagi guru, orang tua, pengelola lembaga, dan pembelajar Al-Qur’an.</p>
            <a href="{{ route('public.academy') }}">Kenali Academy →</a>
        </article>
    </div>
</section>

<section class="public-section kuat-section" id="kuat">
    <div class="public-container kuat-layout">
        <div class="kuat-intro">
            <span class="public-eyebrow light">KERANGKA PEMBINAAN</span>
            <h2>KUAT bukan sekadar slogan.</h2>
            <p>KUAT adalah urutan pertanyaan agar pembinaan tidak langsung melompat kepada target.</p>
            <a class="public-button gold" href="{{ route('public.about') }}#kuat">Baca kerangka KUAT</a>
        </div>
        <div class="kuat-grid">
            <article><strong>K</strong><div><h3>Kenali Diri</h3><p>Kenali manusia, keadaan, kekuatan, dan kebutuhan aktualnya.</p></div></article>
            <article><strong>U</strong><div><h3>Ukur Kemampuan, Usahakan Bertahap</h3><p>Tentukan beban setelah kesiapan dibaca, lalu tumbuhkan secara proporsional.</p></div></article>
            <article><strong>A</strong><div><h3>Al-Qur’an Dipahami dan Diamalkan</h3><p>Hafalan diarahkan kembali kepada fungsi Al-Qur’an sebagai petunjuk.</p></div></article>
            <article><strong>T</strong><div><h3>Teguh Menjaga Perjalanan</h3><p>Bangun sistem yang membantu menjaga, menyesuaikan, dan kembali.</p></div></article>
        </div>
    </div>
</section>

<section class="public-section journey-section">
    <div class="public-container journey-card">
        <div>
            <span class="public-eyebrow">DARI DUNIA NYATA KE JEJAK DIGITAL</span>
            <h2>Teknologi tidak menggantikan guru. Teknologi membantu perjalanan tetap terbaca.</h2>
            <p>Aplikasi Sullamul Ḥifẓ menyimpan catatan yang relevan agar keputusan tidak dibuat dari ingatan yang terputus-putus.</p>
        </div>
        <div class="journey-list">
            <span>Data peserta dan hubungan wali</span>
            <span>Kelas utama dan kelompok Tahfizh</span>
            <span>Kehadiran, Tahsin, hafalan, murāja‘ah</span>
            <span>Buku penghubung dan pengumuman</span>
            <span>Laporan perkembangan yang dapat ditelusuri</span>
        </div>
    </div>
</section>

@if(isset($featuredArticles) && $featuredArticles->isNotEmpty())
<section class="public-section soft-section">
    <div class="public-container section-heading centered"><span class="public-eyebrow">ARTIKEL TERBARU</span><h2>Gagasan untuk menjaga perjalanan.</h2></div>
    <div class="public-container article-grid">
        @foreach($featuredArticles as $article)
        <a class="article-card" href="{{ route('public.article', $article) }}"><span>ARTIKEL</span><h2>{{ $article->title }}</h2><p>{{ $article->excerpt }}</p><small>Baca artikel →</small></a>
        @endforeach
    </div>
</section>
@endif

<section class="public-cta">
    <div class="public-container cta-inner">
        <div><span class="public-eyebrow light">MULAI DARI YANG SUDAH ADA</span><h2>Kenali ekosistemnya. Gunakan aplikasinya. Jaga perjalanannya.</h2></div>
        <div class="hero-actions"><a class="public-button gold" href="{{ route('public.programs') }}">Lihat program</a><a class="public-button outline-light" href="{{ config('sullam.portal_url') ?: route('login') }}">Masuk aplikasi</a></div>
    </div>
</section>
@endsection
