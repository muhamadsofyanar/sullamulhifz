{{-- @phase 4.2 Brand & Universal Home; @phase 4.5 Every Person, Every Aspiration --}}
@extends('layouts.public')
@section('title', 'Sullamul Ḥifẓ — Satu Ruang untuk Menjaga Perjalanan Al-Qur’an')
@section('description', 'Belajar mandiri, bersama ustadz, didampingi keluarga, atau dikelola melalui lembaga dalam satu ekosistem Al-Qur’an.')
@section('content')
<section class="public-hero universal-hero">
    <div class="public-container hero-grid">
        <div class="hero-copy">
            <span class="public-eyebrow">SATU EKOSISTEM · BANYAK JALUR</span>
            <h1>Jaga perjalanan Al-Qur’an dalam ruang yang <em>sesuai hidup Anda.</em></h1>
            <p class="hero-lead">Belajar mandiri, bersama ustadz, didampingi keluarga, atau dikelola melalui lembaga. Sullamul Ḥifẓ menyatukan perjalanan itu tanpa mencampur ruang privat Anda.</p>
            <div class="hero-actions">
                <a class="public-button primary" href="#solusi">Temukan jalur Anda</a>
                <a class="public-button secondary" href="{{ route('personal.register') }}">Mulai sebagai Personal</a>
            </div>
            <div class="hero-trust"><span>Privasi per konteks</span><span>Satu akun, beberapa peran</span><span>Bertahap dan terukur</span></div>
        </div>
        <div class="relationship-map" aria-label="Pola hubungan Sullamul Hifz">
            <div class="map-center"><img src="/brand/logo-mark.svg" alt=""><strong>Sullamul Ḥifẓ</strong><small>Satu akun · ruang terpisah</small></div>
            <span class="map-node node-personal">Personal</span>
            <span class="map-node node-teacher">Ustadz</span>
            <span class="map-node node-family">Orang Tua</span>
            <span class="map-node node-institution">Lembaga</span>
            <span class="map-node node-student">Peserta</span>
        </div>
    </div>
</section>

<section class="public-section aspiration-section">
    <div class="public-container aspiration-layout">
        <div class="aspiration-copy"><span class="public-eyebrow">SETIAP ORANG · SETIAP CITA</span><h2>Anak maupun dewasa dapat tumbuh bersama Al-Qur’an.</h2><p>Dokter, guru, pilot, ahli tanaman, komunikator, teknolog, pengusaha, pelayan masyarakat, dan cita-cita lain tidak dijadikan kelas profesi. Sullamul Ḥifẓ memakai cita-cita sebagai konteks untuk menjaga amanah, ilmu, adab, kepedulian, dan keteguhan.</p><small>Tanpa ranking cita-cita · tanpa profil anak terbuka · tanpa menilai kemampuan dari label kepribadian</small></div>
        <div class="aspiration-example-grid">
            <article><strong>Dokter</strong><span>rahmah · amanah · menjaga kehidupan</span></article>
            <article><strong>Guru</strong><span>ilmu · sabar · keteladanan</span></article>
            <article><strong>Pilot</strong><span>disiplin · tanggung jawab · ketelitian</span></article>
            <article><strong>Ahli tanaman</strong><span>merawat bumi · syukur · kebermanfaatan</span></article>
            <article><strong>Komunikator</strong><span>jujur · santun · menyampaikan kebaikan</span></article>
            <article><strong>Cita-cita lainnya</strong><span>nilai Qur’ani mengikuti perjalanan nyata</span></article>
        </div>
    </div>
</section>

<section class="public-section" id="solusi">
    <div class="public-container section-heading centered">
        <span class="public-eyebrow">SIAPA YANG DAPAT MENGGUNAKANNYA?</span>
        <h2>Mulai dari kebutuhan Anda hari ini.</h2>
        <p>Anda tidak dikunci pada satu jalur. Ruang baru dapat ditambahkan ketika perjalanan Anda berkembang.</p>
    </div>
    <div class="public-container pathway-grid">
        <a class="pathway-card" href="{{ route('public.solution', 'personal') }}"><span>01</span><h3>Personal</h3><p>Target, jurnal, latihan, dan progres yang tetap privat.</p><strong>Lihat solusi →</strong></a>
        <a class="pathway-card" href="{{ route('public.solution', 'ustadz') }}"><span>02</span><h3>Bimbingan Ustadz</h3><p>Setoran, koreksi, jadwal, dan evaluasi bimbingan.</p><strong>Lihat solusi →</strong></a>
        <a class="pathway-card" href="{{ route('public.solution', 'keluarga') }}"><span>03</span><h3>Keluarga</h3><p>Pendampingan orang tua–anak dengan batas yang sehat.</p><strong>Lihat solusi →</strong></a>
        <a class="pathway-card" href="{{ route('public.solution', 'lembaga') }}"><span>04</span><h3>Lembaga</h3><p>Untuk TPA, SD, SMP, SMA, pesantren, kampus, dan komunitas.</p><strong>Lihat solusi →</strong></a>
    </div>
</section>

<section class="public-section relationship-section">
    <div class="public-container section-heading">
        <span class="public-eyebrow">HUBUNGAN YANG DIDUKUNG</span>
        <h2>Bukan lima aplikasi berbeda.</h2>
        <p>Satu platform dengan ruang kerja terpisah. Setiap hubungan hanya melihat data yang memang diizinkan.</p>
    </div>
    <div class="public-container relationship-list">
        <article><strong>Personal</strong><span>Perjalanan mandiri dan privat</span></article>
        <article><strong>Personal ↔ Ustadz</strong><span>Bimbingan privat dan setoran</span></article>
        <article><strong>Lembaga ↔ Ustadz ↔ Peserta</strong><span>Kelas dan operasional pembelajaran</span></article>
        <article><strong>Lembaga ↔ Ustadz ↔ Orang Tua ↔ Peserta</strong><span>Pembelajaran, laporan, dan komunikasi</span></article>
        <article><strong>Orang Tua ↔ Anak</strong><span>Pendampingan keluarga mandiri</span></article>
    </div>
</section>

<section class="public-section kuat-section" id="kuat">
    <div class="public-container kuat-layout">
        <div class="kuat-intro"><span class="public-eyebrow light">KERANGKA KUAT</span><h2>Bukan sekadar hafal, tapi KUAT.</h2><p>Teknologi membantu membaca manusia, kemampuan, makna, dan kesinambungan—bukan menggantikan guru atau hubungan nyata.</p><a class="public-button gold" href="{{ route('public.about') }}#kuat">Pelajari kerangka KUAT</a></div>
        <div class="kuat-grid">
            <article><strong>K</strong><div><h3>Kenali Diri</h3><p>Kondisi dan kebutuhan dibaca sebelum target ditentukan.</p></div></article>
            <article><strong>U</strong><div><h3>Ukur Kemampuan</h3><p>Beban tumbuh bertahap dan dapat disesuaikan.</p></div></article>
            <article><strong>A</strong><div><h3>Al-Qur’an Dipahami</h3><p>Hafalan kembali kepada fungsi Al-Qur’an sebagai petunjuk.</p></div></article>
            <article><strong>T</strong><div><h3>Teguh Menjaga</h3><p>Jeda bukan akhir; sistem membantu pengguna kembali.</p></div></article>
        </div>
    </div>
</section>

<section class="public-section implementation-section">
    <div class="public-container journey-card">
        <div><span class="public-eyebrow">CONTOH IMPLEMENTASI</span><h2>TPA Al-Insyirah adalah salah satu penerapan, bukan batas produk.</h2><p>Model kelas, pembinaan, laporan, dan komunikasi dari implementasi awal menjadi rujukan yang dapat disesuaikan untuk lembaga lain.</p><div class="hero-actions"><a class="public-button primary" href="{{ route('public.institution.showcase') }}">Lihat implementasi</a><a class="public-button secondary" href="{{ route('institution.register') }}">Daftarkan lembaga</a></div></div>
        <div class="journey-list"><span>TPA dan rumah tahfiz</span><span>SD, SMP, dan SMA</span><span>Pesantren dan kampus</span><span>Komunitas Al-Qur’an</span></div>
    </div>
</section>

@if(isset($featuredArticles) && $featuredArticles->isNotEmpty())
<section class="public-section soft-section"><div class="public-container section-heading centered"><span class="public-eyebrow">ARTIKEL TERBARU</span><h2>Gagasan untuk menjaga perjalanan.</h2></div><div class="public-container article-grid">@foreach($featuredArticles as $article)<a class="article-card" href="{{ route('public.article', $article) }}"><span>ARTIKEL</span><h2>{{ $article->title }}</h2><p>{{ $article->excerpt }}</p><small>Baca artikel →</small></a>@endforeach</div></section>
@endif

<section class="public-cta"><div class="public-container cta-inner"><div><span class="public-eyebrow light">MULAI SEKARANG</span><h2>Pilih ruang pertama. Perjalanan Anda dapat tumbuh dari sana.</h2></div><div class="hero-actions"><a class="public-button gold" href="{{ route('personal.register') }}">Daftar Personal</a><a class="public-button outline-light" href="{{ route('institution.register') }}">Daftar Lembaga</a></div></div></section>
@endsection
