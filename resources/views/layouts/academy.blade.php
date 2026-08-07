<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#004b3f">
    <title>{{ $pageTitle ?? 'Sullamul Ḥifẓ Academy' }}</title>
    <link rel="manifest" href="/academy-manifest.webmanifest">
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/css/app.css?v={{ @filemtime(public_path('css/app.css')) ?: '201' }}">
    <link rel="stylesheet" href="/css/app-v203.css?v={{ @filemtime(public_path('css/app-v203.css')) ?: '203' }}">
    <link rel="stylesheet" href="/css/app-v204.css?v={{ @filemtime(public_path('css/app-v204.css')) ?: '204' }}">
    <link rel="stylesheet" href="/css/app-v210.css?v={{ @filemtime(public_path('css/app-v210.css')) ?: '210' }}">
    <link rel="stylesheet" href="/css/app-v220.css?v={{ @filemtime(public_path('css/app-v220.css')) ?: '220' }}">
    <link rel="stylesheet" href="/css/app-v230.css?v={{ @filemtime(public_path('css/app-v230.css')) ?: '230' }}">
    <link rel="stylesheet" href="/css/app-v240.css?v={{ @filemtime(public_path('css/app-v240.css')) ?: '240' }}">
    <script defer src="/js/app.js?v={{ @filemtime(public_path('js/app.js')) ?: '201' }}"></script>
    <script defer src="/js/academy-player.js?v={{ @filemtime(public_path('js/academy-player.js')) ?: '204' }}"></script>
    <script defer src="/js/academy-quran.js?v={{ @filemtime(public_path('js/academy-quran.js')) ?: '240' }}"></script>
</head>
<body class="academy-standalone-body role-{{ auth()->user()->primaryRole() }}">
@php
    $academyInstitutionId = (int) auth()->user()->institution_id;
    $academyLearningPathsEnabled = \App\Support\Feature::enabled('learning_paths', $academyInstitutionId, true);
    $academyQuranEnabled = \App\Support\Feature::enabled('quran_audio', $academyInstitutionId, true);
@endphp
<div class="academy-shell">
    <aside class="academy-sidebar" id="academy-sidebar">
        <div class="academy-brand-wrap">
            <a class="academy-brand" href="{{ route('academy.portal.index') }}" aria-label="Sullamul Hifz Academy">
                <img src="/brand/logo-horizontal-light.svg" alt="Sullamul Hifz">
            </a>
            <span>ACADEMY</span>
        </div>

        <nav class="academy-nav" aria-label="Navigasi Academy">
            <a href="{{ route('academy.portal.index') }}" class="{{ request()->routeIs('academy.portal.index') ? 'active' : '' }}"><x-icon name="home"/><span>Beranda Academy</span></a>
            <a href="{{ route('academy.portal.programs') }}" class="{{ request()->routeIs('academy.portal.programs','academy.portal.program') ? 'active' : '' }}"><x-icon name="lesson"/><span>Program</span></a>
            <a href="{{ route('academy.portal.classes') }}" class="{{ request()->routeIs('academy.portal.classes') ? 'active' : '' }}"><x-icon name="community"/><span>Kelas Saya</span></a>
            @if($academyLearningPathsEnabled)<a href="{{ route('academy.portal.paths') }}" class="{{ request()->routeIs('academy.portal.paths','academy.portal.path') ? 'active' : '' }}"><x-icon name="continuity"/><span>Jalur Belajar</span></a>@endif
            <a href="{{ route('academy.portal.modules') }}" class="{{ request()->routeIs('academy.portal.modules') ? 'active' : '' }}"><x-icon name="plan"/><span>Modul</span></a>
            <a href="{{ route('academy.portal.materials') }}" class="{{ request()->routeIs('academy.portal.materials','academy.portal.lesson') ? 'active' : '' }}"><x-icon name="academic"/><span>Materi</span></a>
            <a href="{{ route('academy.portal.videos') }}" class="{{ request()->routeIs('academy.portal.videos') ? 'active' : '' }}"><x-icon name="video"/><span>Video</span></a>
            @if($academyQuranEnabled)<a href="{{ route('academy.portal.audio') }}" class="{{ request()->routeIs('academy.portal.audio') ? 'active' : '' }}"><x-icon name="listen"/><span>Audio</span></a>@endif
            <a href="{{ route('academy.portal.articles') }}" class="{{ request()->routeIs('academy.portal.articles') ? 'active' : '' }}"><x-icon name="assignment"/><span>Artikel</span></a>
            <a href="{{ route('academy.portal.progress') }}" class="{{ request()->routeIs('academy.portal.progress') ? 'active' : '' }}"><x-icon name="progress"/><span>Progres Belajar</span></a>
            <a href="{{ route('academy.portal.recommendations') }}" class="{{ request()->routeIs('academy.portal.recommendations') ? 'active' : '' }}"><x-icon name="guidance"/><span>Rekomendasi Guru</span></a>
            <a href="{{ route('academy.portal.bookmarks') }}" class="{{ request()->routeIs('academy.portal.bookmarks') ? 'active' : '' }}"><x-icon name="preservation"/><span>Tersimpan</span></a>
            <a href="{{ route('academy.portal.ecosystem') }}" class="{{ request()->routeIs('academy.portal.ecosystem') ? 'active' : '' }}"><x-icon name="growth"/><span>Ekosistem 10 Fase</span></a>
            <a href="{{ route('academy.portal.profile') }}" class="{{ request()->routeIs('academy.portal.profile') ? 'active' : '' }}"><x-icon name="profile"/><span>Profil</span></a>
        </nav>

        <div class="academy-sidebar-footer">
            <a href="{{ config('sullam.portal_base_url') }}" class="academy-external-link"><x-icon name="growth"/><span>Aplikasi TPA</span></a>
            <a href="{{ config('sullam.public_url') }}" class="academy-external-link"><x-icon name="home"/><span>Website Utama</span></a>
            <form method="post" action="{{ route('logout') }}">@csrf
                <button type="submit" class="academy-logout"><x-icon name="logout"/><span>Keluar</span></button>
            </form>
        </div>
    </aside>

    <main class="academy-main">
        <header class="academy-topbar">
            <button type="button" class="academy-menu-button" data-academy-sidebar-toggle aria-label="Buka menu"><x-icon name="menu" size="24"/></button>
            <div class="academy-topbar-copy"><strong>{{ $pageTitle ?? 'Sullamul Ḥifẓ Academy' }}</strong><small>Belajar bertahap · mendampingi dengan hangat</small></div>
            <div class="academy-user-chip"><span>{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->institution?->name }}</small></div></div>
        </header>
        <div class="academy-content-wrap">
            @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert danger"><strong>Periksa kembali isian:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </div>
    </main>
</div>

<nav class="academy-mobile-nav" aria-label="Navigasi Academy ponsel">
    <a href="{{ route('academy.portal.index') }}" class="{{ request()->routeIs('academy.portal.index') ? 'active' : '' }}"><x-icon name="home"/><span>Beranda</span></a>
    <a href="{{ route('academy.portal.programs') }}" class="{{ request()->routeIs('academy.portal.programs','academy.portal.program') ? 'active' : '' }}"><x-icon name="lesson"/><span>Program</span></a>
    <a href="{{ route('academy.portal.classes') }}" class="{{ request()->routeIs('academy.portal.classes') ? 'active' : '' }}"><x-icon name="community"/><span>Kelas</span></a>
    <a href="{{ route('academy.portal.progress') }}" class="{{ request()->routeIs('academy.portal.progress') ? 'active' : '' }}"><x-icon name="progress"/><span>Progres</span></a>
    <button type="button" data-academy-sidebar-toggle><x-icon name="menu"/><span>Lainnya</span></button>
</nav>
<div class="academy-sidebar-backdrop" data-academy-sidebar-toggle></div>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const body=document.body;
    document.querySelectorAll('[data-academy-sidebar-toggle]').forEach(button=>button.addEventListener('click',()=>body.classList.toggle('academy-sidebar-open')));
});
</script>
</body>
</html>
