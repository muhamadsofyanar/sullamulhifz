@extends('layouts.public')
@section('title', 'Ikrar Santri TPA Al-Insyirah — Sullamul Ḥifẓ')
@section('description', 'Tujuh ikrar Santri TPA Al-Insyirah untuk mencintai Al-Qur’an, berakhlak mulia, rajin belajar, dan membawa kebaikan.')
@section('content')
<section class="page-hero pledge-page-hero">
    <div class="public-container page-hero-inner">
        <span class="public-eyebrow">{{ $pledge['eyebrow'] }}</span>
        <h1>{{ $pledge['title'] }}</h1>
        <p>Ikrar bukan sekadar kalimat yang diucapkan. Ia menjadi pengingat bersama untuk dibiasakan di kelas, di rumah, dan di masjid.</p>
        <div class="hero-actions pledge-no-print">
            <a class="public-button primary" href="#isi-ikrar">Baca ikrar</a>
            <button class="public-button secondary" type="button" onclick="window.print()">Cetak ikrar</button>
        </div>
    </div>
</section>

<section class="public-section pledge-section" id="isi-ikrar">
    <div class="public-container pledge-poster">
        <div class="pledge-poster-main">
            <div class="pledge-heading">
                <img src="/brand/logo-mark.svg" alt="" aria-hidden="true">
                <div>
                    <span>TPA Al-Insyirah · {{ $pledge['institution_descriptor'] }}</span>
                    <h2>{{ $pledge['title'] }}</h2>
                    <p>{{ $pledge['intro'] }}</p>
                </div>
            </div>

            <ol class="pledge-list">
                @foreach($pledge['items'] as $item)
                    <li>
                        <span class="pledge-number">{{ $item['number'] }}</span>
                        <span class="pledge-copy">
                            <small>{{ $item['short_title'] }}</small>
                            <strong>{{ $item['title'] }}</strong>
                            @if(filled($item['description']))<span>{{ $item['description'] }}</span>@endif
                        </span>
                    </li>
                @endforeach
            </ol>

            <div class="pledge-closing">{{ $pledge['closing'] }}</div>
        </div>

        <aside class="pledge-poster-aside">
            <div class="pledge-aside-mark"><img src="/brand/logo-mark.svg" alt="Sullamul Ḥifẓ"></div>
            <span class="public-eyebrow">ARAH BERSAMA</span>
            <blockquote>“{{ $pledge['aspiration'] }}”</blockquote>
            <p>Ikrar dijaga melalui keteladanan, pembiasaan, percakapan, dan pendampingan—bukan melalui perbandingan antarsantri.</p>
            <a class="text-link pledge-no-print" href="{{ route('public.tpa') }}">Lihat implementasi TPA →</a>
            <small class="pledge-institution-motto">{{ $pledge['institution_motto'] }}</small>
        </aside>
    </div>
</section>

<section class="public-section soft-section pledge-values-section">
    <div class="public-container section-heading centered">
        <span class="public-eyebrow">LIMA BUDAYA BERSAMA</span>
        <h2>Nilai yang tumbuh dari ikrar.</h2>
        <p>Setiap nilai diterjemahkan menjadi kebiasaan yang dapat dilihat dan didampingi, bukan menjadi bahan ranking.</p>
    </div>
    <div class="public-container pledge-values-grid">
        @foreach($pledge['values'] as $index => $value)
            <article>
                <span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <h3>{{ $value['title'] }}</h3>
                <p>{{ $value['description'] }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="public-section pledge-practice-section">
    <div class="public-container section-heading">
        <span class="public-eyebrow">DARI UCAPAN MENJADI KEBIASAAN</span>
        <h2>Ikrar dihidupkan di tiga ruang utama.</h2>
    </div>
    <div class="public-container pledge-practice-grid">
        @foreach($pledge['practice'] as $practice)
            <article>
                <h3>{{ $practice['place'] }}</h3>
                <p>{{ $practice['description'] }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="public-cta pledge-no-print">
    <div class="public-container cta-inner">
        <div>
            <span class="public-eyebrow light">TPA AL-INSYIRAH</span>
            <h2>Membaca bersama, memahami bersama, dan bertumbuh bersama.</h2>
        </div>
        <div class="hero-actions">
            <a class="public-button gold" href="{{ route('public.registration') }}">Pendaftaran santri</a>
            <a class="public-button outline-light" href="{{ config('sullam.portal_url') ?: route('login') }}">Masuk aplikasi</a>
        </div>
    </div>
</section>
@endsection
