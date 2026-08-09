{{-- @phase 4.2 Brand & Universal Home; @phase 4.5 Every Person, Every Aspiration --}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#004b3f">
    <meta name="color-scheme" content="light">
    @php
        $siteName = 'Sullamul Ḥifẓ';
        $pageTitle = trim($__env->yieldContent('title')) ?: 'Sullamul Ḥifẓ — Bukan Sekadar Hafal, Tapi KUAT';
        $pageDescription = trim($__env->yieldContent('description')) ?: 'Ekosistem pendidikan Al-Qur’an yang menjaga manusia, kemampuan, makna, dan kesinambungan perjalanan.';
        $canonical = trim($__env->yieldContent('canonical')) ?: request()->url();
        $portalUrl = config('sullam.portal_url') ?: route('login');
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ url('/brand/logo-horizontal.svg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/css/public.css?v={{ @filemtime(public_path('css/public.css')) ?: '201' }}">
    <link rel="stylesheet" href="/css/public-v450.css?v={{ @filemtime(public_path('css/public-v450.css')) ?: '450' }}">
    <script defer src="/js/public.js?v={{ @filemtime(public_path('js/public.js')) ?: '201' }}"></script>
</head>
<body class="public-body">
<a class="skip-link" href="#konten">Lewati ke konten utama</a>
<header class="public-header" data-public-header>
    <div class="public-container header-inner">
        <a class="public-brand" href="{{ route('public.home') }}" aria-label="Sullamul Hifz — Beranda">
            <img src="/brand/logo-horizontal.svg" alt="Sullamul Hifz — Bukan Sekadar Hafal, Tapi KUAT">
        </a>
        <button class="public-menu-button" type="button" aria-expanded="false" aria-controls="public-navigation" data-public-menu>
            <span></span><span></span><span></span><span class="sr-only">Buka menu</span>
        </button>
        <nav class="public-nav" id="public-navigation" aria-label="Navigasi website" data-public-nav>
            <a href="{{ route('public.home') }}" class="{{ request()->routeIs('public.home') ? 'active' : '' }}">Beranda</a>
            <a href="{{ route('public.home') }}#solusi" class="{{ request()->routeIs('public.solution') ? 'active' : '' }}">Solusi</a>
            <a href="{{ route('public.features') }}" class="{{ request()->routeIs('public.features') ? 'active' : '' }}">Fitur</a>
            <a href="{{ route('public.academy') }}" class="{{ request()->routeIs('public.academy') ? 'active' : '' }}">Academy</a>
            <a href="{{ route('public.articles') }}" class="{{ request()->routeIs('public.articles') ? 'active' : '' }}">Artikel</a>
            <a href="{{ route('public.pricing') }}" class="{{ request()->routeIs('public.pricing') ? 'active' : '' }}">Harga</a>
            <a href="{{ route('public.about') }}" class="{{ request()->routeIs('public.about') ? 'active' : '' }}">Tentang</a>
            <a class="public-login-button" href="{{ $portalUrl }}">Masuk aplikasi</a>
        </nav>
    </div>
</header>

<main id="konten">
    @yield('content')
</main>

<footer class="public-footer">
    <div class="public-container footer-grid">
        <div class="footer-brand">
            <img src="/brand/logo-horizontal-light.svg" alt="Sullamul Hifz">
            <p>Pembinaan berlangsung di dunia nyata. Teknologi membantu menjaga jejak, komunikasi, dan kesinambungannya.</p>
        </div>
        <div>
            <h2>Solusi</h2>
            <a href="{{ route('public.solution','personal') }}">Personal</a>
            <a href="{{ route('public.solution','ustadz') }}">Bimbingan Ustadz</a>
            <a href="{{ route('public.solution','keluarga') }}">Keluarga</a>
            <a href="{{ route('public.solution','lembaga') }}">Lembaga</a>
            <a href="{{ route('public.institution.showcase') }}">Contoh TPA Al-Insyirah</a>
        </div>
        <div>
            <h2>Informasi</h2>
            <a href="{{ route('public.about') }}">Tentang kami</a>
            <a href="{{ route('public.articles') }}">Artikel</a>
            <a href="{{ route('public.contact') }}">Kontak</a>
            <a href="{{ route('public.features') }}">Fitur</a>
            <a href="{{ route('public.pricing') }}">Harga</a>
            <a href="{{ route('public.privacy') }}">Privasi</a>
            <a href="{{ route('public.terms') }}">Syarat & ketentuan</a>
        </div>
        <div>
            <h2>Aplikasi</h2>
            <p>Portal untuk Personal, ustadz, peserta, orang tua, dan pengelola lembaga.</p>
            <a class="footer-login" href="{{ $portalUrl }}">Masuk ke portal →</a>
        </div>
    </div>
    <div class="public-container footer-bottom">
        <span>© {{ now()->year }} Sullamul Ḥifẓ. Seluruh hak dilindungi.</span>
        <span>Bukan Sekadar Hafal, Tapi KUAT.</span>
    </div>
</footer>
</body>
</html>
