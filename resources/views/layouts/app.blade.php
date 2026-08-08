<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#004b3f">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/css/app.css?v={{ @filemtime(public_path('css/app.css')) ?: '201' }}">
    <link rel="stylesheet" href="/css/app-v203.css?v={{ @filemtime(public_path('css/app-v203.css')) ?: '203' }}">
    <link rel="stylesheet" href="/css/app-v204.css?v={{ @filemtime(public_path('css/app-v204.css')) ?: '204' }}">
    <link rel="stylesheet" href="/css/app-v210.css?v={{ @filemtime(public_path('css/app-v210.css')) ?: '210' }}">
    <link rel="stylesheet" href="/css/app-v220.css?v={{ @filemtime(public_path('css/app-v220.css')) ?: '220' }}">
    <link rel="stylesheet" href="/css/app-v230.css?v={{ @filemtime(public_path('css/app-v230.css')) ?: '230' }}">
    <link rel="stylesheet" href="/css/app-v240.css?v={{ @filemtime(public_path('css/app-v240.css')) ?: '240' }}">
    <link rel="stylesheet" href="/css/app-v250.css?v={{ @filemtime(public_path('css/app-v250.css')) ?: '250' }}">
    <link rel="stylesheet" href="/css/app-v300.css?v={{ @filemtime(public_path('css/app-v300.css')) ?: '300' }}">
    <script defer src="/js/app.js?v={{ @filemtime(public_path('js/app.js')) ?: '201' }}"></script>
    <script defer src="/js/academy-player.js?v={{ @filemtime(public_path('js/academy-player.js')) ?: '204' }}"></script>
</head>
<body class="app-body @auth role-{{ auth()->user()->primaryRole() }} @endauth">
@if(auth()->check())
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand brand-sidebar" href="{{ route('dashboard') }}" aria-label="Sullamul Hifz — Beranda">
            <img src="/brand/logo-horizontal-light.svg" alt="Sullamul Hifz">
        </a>

        <nav class="nav-list" aria-label="Navigasi utama">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <x-icon name="home"/><span>Beranda</span>
            </a>
            @if(auth()->user()->hasRole('personal'))
                <a href="{{ route('personal.dashboard') }}#catat" class="{{ request()->routeIs('personal.*') ? 'active' : '' }}"><x-icon name="preservation"/><span>Catat Aktivitas</span></a>
                <a href="{{ route('personal.dashboard') }}#target"><x-icon name="plan"/><span>Target Saya</span></a>
                <a href="{{ route('personal.dashboard') }}#jurnal"><x-icon name="continuity"/><span>Jurnal Perjalanan</span></a>
            @endif
            @if(auth()->user()->hasAnyRole(['superadmin','institution_admin']))
                <a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}" @if(request()->routeIs('admin.students.*')) aria-current="page" @endif>
                    <x-icon name="student"/><span>Santri</span>
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}" @if(request()->routeIs('admin.teachers.*')) aria-current="page" @endif>
                    <x-icon name="teacher"/><span>Guru</span>
                </a>
                <a href="{{ route('admin.guardians.index') }}" class="{{ request()->routeIs('admin.guardians.*') ? 'active' : '' }}">
                    <x-icon name="profile"/><span>Wali</span>
                </a>
                <a href="{{ route('admin.imports.index') }}" class="{{ request()->routeIs('admin.imports.*') ? 'active' : '' }}">
                    <x-icon name="assignment"/><span>Impor Data</span>
                </a>
                <a href="{{ route('admin.academic.index') }}" class="{{ request()->routeIs('admin.academic.*') ? 'active' : '' }}" @if(request()->routeIs('admin.academic.*')) aria-current="page" @endif>
                    <x-icon name="academic"/><span>Akademik</span>
                </a>
                <a href="{{ route('admin.academic-core.index') }}" class="{{ request()->routeIs('admin.academic-core.*') ? 'active' : '' }}">
                    <x-icon name="focus"/><span>Fondasi Pembelajaran</span>
                </a>
                <a href="{{ route('admin.institution.edit') }}" class="{{ request()->routeIs('admin.institution.*') ? 'active' : '' }}">
                    <x-icon name="community"/><span>Profil Lembaga</span>
                </a>
                <a href="{{ route('admin.platform.index') }}" class="{{ request()->routeIs('admin.platform.*') ? 'active' : '' }}">
                    <x-icon name="growth"/><span>Fondasi Platform</span>
                </a>
                @if(\App\Support\Feature::enabled('quran_audio', auth()->user()->institution_id, true))
                <a href="{{ route('admin.quran-library.index') }}" class="{{ request()->routeIs('admin.quran-library.*') ? 'active' : '' }}">
                    <x-icon name="audio"/><span>Pustaka Qur’an</span>
                </a>
                @endif
                @if(\App\Support\Feature::enabled('parent_academy', auth()->user()->institution_id, true))
                <a href="{{ route('admin.academy.index') }}" class="{{ request()->routeIs('admin.academy.*') ? 'active' : '' }}">
                    <x-icon name="lesson"/><span>Kelola Academy</span>
                </a>
                @endif
                @if(\App\Support\Feature::enabled('family_learning', auth()->user()->institution_id, true))
                <a href="{{ route('admin.family-teacher.index') }}" class="{{ request()->routeIs('admin.family-teacher.*') ? 'active' : '' }}">
                    <x-icon name="community"/><span>Keluarga & Guru</span>
                </a>
                @endif
                <a href="{{ route('admin.launch-readiness.index') }}" class="{{ request()->routeIs('admin.launch-readiness.*') ? 'active' : '' }}">
                    <x-icon name="achievement"/><span>Kesiapan Peluncuran</span>
                </a>
            @endif
            @if(auth()->user()->hasAnyPermission(['announcements.manage','friday.manage']))
                <a href="{{ route('admin.content.index') }}" class="{{ request()->routeIs('admin.content.*') ? 'active' : '' }}" @if(request()->routeIs('admin.content.*')) aria-current="page" @endif>
                    <x-icon name="guidance"/><span>Konten & Pembinaan</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('institution.manage'))
                <a href="{{ route('admin.student-pledge.edit') }}" class="{{ request()->routeIs('admin.student-pledge.*') ? 'active' : '' }}">
                    <x-icon name="values"/><span>Kelola Ikrar</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('website.manage') && \App\Support\Feature::enabled('public_website', auth()->user()->institution_id, true))
                <a href="{{ route('admin.website.index') }}" class="{{ request()->routeIs('admin.website.*') ? 'active' : '' }}">
                    <x-icon name="community"/><span>Website</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('report_cards.manage') && \App\Support\Feature::enabled('report_cards', auth()->user()->institution_id, true))
                <a href="{{ route('admin.report-cards.index') }}" class="{{ request()->routeIs('admin.report-cards.*') ? 'active' : '' }}">
                    <x-icon name="report"/><span>Rapor</span>
                </a>
            @endif
            @if(auth()->user()->hasAnyRole(['superadmin','institution_admin']) && auth()->user()->hasPermission('reports.view'))
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" @if(request()->routeIs('admin.reports.*')) aria-current="page" @endif>
                    <x-icon name="report"/><span>Laporan</span>
                </a>
            @endif
            @if(auth()->user()->hasRole('teacher'))
                <a href="{{ route('teacher.daily.index') }}" class="{{ request()->routeIs('teacher.daily.*') ? 'active' : '' }}">
                    <x-icon name="home"/><span>Operasional Hari Ini</span>
                </a>
                <a href="{{ route('teacher.classrooms.index') }}" class="{{ request()->routeIs('teacher.classrooms.*') || request()->routeIs('teacher.meetings.*') ? 'active' : '' }}">
                    <x-icon name="classroom"/><span>Kelas Saya</span>
                </a>
                <a href="{{ route('teacher.tahfizh.index') }}" class="{{ request()->routeIs('teacher.tahfizh.*') ? 'active' : '' }}">
                    <x-icon name="preservation"/><span>Perjalanan Tahfizh</span>
                </a>
                @if(\App\Support\Feature::enabled('quran_journey', auth()->user()->institution_id, true))
                <a href="{{ route('teacher.quran-journey.index') }}" class="{{ request()->routeIs('teacher.quran-journey.*') ? 'active' : '' }}">
                    <x-icon name="growth"/><span>Qur’an Journey Santri</span>
                </a>
                @endif
                <a href="{{ route('teacher.learning-plan.index') }}" class="{{ request()->routeIs('teacher.learning-plan.*') ? 'active' : '' }}">
                    <x-icon name="guidance"/><span>Target & Profil</span>
                </a>
                <a href="{{ route('teacher.personal-learning.index') }}" class="{{ request()->routeIs('teacher.personal-learning.*') ? 'active' : '' }}">
                    <x-icon name="growth"/><span>Personalisasi Belajar</span>
                </a>
                @if(\App\Support\Feature::enabled('parent_academy', auth()->user()->institution_id))
                <a href="{{ route('teacher.academy.index') }}" class="{{ request()->routeIs('teacher.academy.*') ? 'active' : '' }}">
                    <x-icon name="lesson"/><span>Academy & Keluarga</span>
                </a>
                @endif
                @if(\App\Support\Feature::enabled('family_learning', auth()->user()->institution_id, true))
                <a href="{{ route('teacher.family-learning.index') }}" class="{{ request()->routeIs('teacher.family-learning.*') ? 'active' : '' }}">
                    <x-icon name="community"/><span>Aktivitas & Kompetensi</span>
                </a>
                @endif
                <a href="{{ route('teacher.assignments.index') }}" class="{{ request()->routeIs('teacher.assignments.*') ? 'active' : '' }}">
                    <x-icon name="assignment"/><span>Tugas</span>
                </a>
            @endif
            @if(auth()->user()->hasRole('guardian'))
                <a href="{{ route('guardian.children.index') }}" class="{{ request()->routeIs('guardian.children.*') ? 'active' : '' }}">
                    <x-icon name="progress"/><span>Perkembangan Anak</span>
                </a>
                <a href="{{ route('guardian.tasks.index') }}" class="{{ request()->routeIs('guardian.tasks.*') ? 'active' : '' }}">
                    <x-icon name="assignment"/><span>Tugas Anak</span>
                </a>
                @if(\App\Support\Feature::enabled('parent_academy', auth()->user()->institution_id))
                <a href="{{ route('academy.portal.index') }}" class="{{ request()->routeIs('academy.*') ? 'active' : '' }}">
                    <x-icon name="lesson"/><span>Parent Academy</span>
                </a>
                @endif
                @if(\App\Support\Feature::enabled('family_learning', auth()->user()->institution_id, true))
                <a href="{{ route('guardian.family-learning.index') }}" class="{{ request()->routeIs('guardian.family-learning.*') ? 'active' : '' }}">
                    <x-icon name="community"/><span>Aktivitas Keluarga</span>
                </a>
                @endif
            @endif
            @unless(auth()->user()->hasRole('personal'))
            <a href="{{ route('liaison.index') }}" class="{{ request()->routeIs('liaison.*') ? 'active' : '' }}" @if(request()->routeIs('liaison.*')) aria-current="page" @endif>
                <x-icon name="discussion"/><span>Buku Penghubung</span>
            </a>
            <a href="{{ route('feed.announcements') }}" class="{{ request()->routeIs('feed.announcements') ? 'active' : '' }}" @if(request()->routeIs('feed.announcements')) aria-current="page" @endif>
                <x-icon name="community"/><span>Pengumuman</span>
            </a>
            <a href="{{ route('feed.friday') }}" class="{{ request()->routeIs('feed.friday') ? 'active' : '' }}" @if(request()->routeIs('feed.friday')) aria-current="page" @endif>
                <x-icon name="values"/><span>Pembinaan Jumat</span>
            </a>
            @if(\App\Support\Feature::enabled('quran_journey', auth()->user()->institution_id, true))
            <a href="{{ route('quran-journey.index') }}" class="{{ request()->routeIs('quran-journey.*') ? 'active' : '' }}" @if(request()->routeIs('quran-journey.*')) aria-current="page" @endif>
                <x-icon name="continuity"/><span>Program Qur’an Saya</span>
            </a>
            @endif
            @if(\App\Support\Feature::enabled('quran_audio', auth()->user()->institution_id))
            <a href="{{ route('quran-practice.index') }}" class="{{ request()->routeIs('quran-practice.*') ? 'active' : '' }}" @if(request()->routeIs('quran-practice.*')) aria-current="page" @endif>
                <x-icon name="listen"/><span>Latihan Al-Qur’an</span>
            </a>
            @endif
            @if(auth()->user()->hasAnyRole(['superadmin','institution_admin','head']) && \App\Support\Feature::enabled('parent_academy', auth()->user()->institution_id))
            <a href="{{ route('academy.portal.index') }}" class="{{ request()->routeIs('academy.*') ? 'active' : '' }}">
                <x-icon name="lesson"/><span>Lihat Academy</span>
            </a>
            @endif
            <a href="{{ route('feed.pledge') }}" class="{{ request()->routeIs('feed.pledge') ? 'active' : '' }}" @if(request()->routeIs('feed.pledge')) aria-current="page" @endif>
                <x-icon name="values"/><span>Ikrar Santri</span>
            </a>
            @endunless
        </nav>

        <div class="sidebar-footer">
            <a class="sidebar-user" href="{{ route('profile.edit') }}">
                <span class="sidebar-avatar"><x-icon name="profile" size="19"/></span>
                <span><strong>{{ auth()->user()->name }}</strong><small>Lihat profil</small></span>
            </a>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="link-button logout-button" type="submit"><x-icon name="logout" size="18"/><span>Keluar</span></button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <button type="button" class="icon-button" data-sidebar-toggle aria-label="Buka menu"><x-icon name="menu" size="25"/></button>
            <div class="topbar-title"><strong>{{ $pageTitle ?? 'Sullamul Ḥifẓ' }}</strong><small>{{ auth()->user()->hasRole('personal') ? 'Ruang Personal · Privat' : auth()->user()->institution?->name }}</small></div>
            <button type="button" class="pwa-install-chip" data-pwa-install hidden>Instal</button>
            <a class="avatar" href="{{ route('profile.edit') }}" aria-label="Buka profil {{ auth()->user()->name }}">{{ strtoupper(mb_substr(auth()->user()->name,0,1)) }}</a>
        </header>
        <div class="content-wrap">
            @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
            @if(session('activation_url'))
                <div class="alert success">
                    <strong>Tautan aktivasi siap dikirim.</strong>
                    <div class="activation-link-box">
                        <input type="text" value="{{ session('activation_url') }}" readonly data-copy-source aria-label="Tautan aktivasi akun">
                        <button type="button" class="button secondary" data-copy-button>Salin tautan</button>
                    </div>
                </div>
            @endif
            @if(session('error'))<div class="alert danger">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="alert danger"><strong>Periksa kembali isian:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
<nav class="mobile-bottom-nav" aria-label="Navigasi bawah">
    @if(auth()->user()->hasRole('personal'))
        <a href="{{ route('personal.dashboard') }}" class="{{ request()->routeIs('personal.*')?'active':'' }}"><x-icon name="home"/><span>Beranda</span></a>
        <a href="{{ route('personal.dashboard') }}#catat"><x-icon name="preservation"/><span>Catat</span></a>
        <a href="{{ route('personal.dashboard') }}#target"><x-icon name="plan"/><span>Target</span></a>
        <a href="{{ route('personal.dashboard') }}#jurnal"><x-icon name="continuity"/><span>Jurnal</span></a>
        <button type="button" data-sidebar-toggle><x-icon name="menu"/><span class="mobile-more-label">Lainnya</span></button>
    @elseif(auth()->user()->hasRole('guardian'))
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard')?'active':'' }}"><x-icon name="home"/><span>Beranda</span></a>
        <a href="{{ route('guardian.children.index') }}" class="{{ request()->routeIs('guardian.children.*')?'active':'' }}"><x-icon name="student"/><span>Anak</span></a>
        <a href="{{ route('guardian.tasks.index') }}" class="{{ request()->routeIs('guardian.tasks.*')?'active':'' }}"><x-icon name="plan"/><span>Tugas</span></a>
        <a href="{{ route('liaison.index') }}" class="{{ request()->routeIs('liaison.*')?'active':'' }}"><x-icon name="discussion"/><span>Pesan</span></a>
        <button type="button" data-sidebar-toggle><x-icon name="menu"/><span class="mobile-more-label">Lainnya</span></button>
    @elseif(auth()->user()->hasRole('teacher'))
        <a href="{{ route('teacher.daily.index') }}" class="{{ request()->routeIs('teacher.daily.*')?'active':'' }}"><x-icon name="home"/><span>Beranda</span></a>
        <a href="{{ route('teacher.classrooms.index') }}" class="{{ request()->routeIs('teacher.classrooms.*')||request()->routeIs('teacher.meetings.*')?'active':'' }}"><x-icon name="lesson"/><span>Kelas</span></a>
        <a href="{{ route('teacher.assignments.index') }}" class="{{ request()->routeIs('teacher.assignments.*')||request()->routeIs('teacher.submissions.*')?'active':'' }}"><x-icon name="plan"/><span>Tugas</span></a>
        <a href="{{ route('liaison.index') }}" class="{{ request()->routeIs('liaison.*')?'active':'' }}"><x-icon name="discussion"/><span>Pesan</span></a>
        <button type="button" data-sidebar-toggle><x-icon name="menu"/><span class="mobile-more-label">Lainnya</span></button>
    @elseif(auth()->user()->hasAnyRole(['superadmin','institution_admin']))
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard')?'active':'' }}"><x-icon name="home"/><span>Beranda</span></a>
        <a href="{{ route('admin.academic.index') }}" class="{{ request()->routeIs('admin.academic.*')||request()->routeIs('admin.academic-core.*')?'active':'' }}"><x-icon name="schedule"/><span>Akademik</span></a>
        <a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*')||request()->routeIs('admin.teachers.*')||request()->routeIs('admin.guardians.*')?'active':'' }}"><x-icon name="community"/><span>Pengguna</span></a>
        <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*')||request()->routeIs('admin.report-cards.*')?'active':'' }}"><x-icon name="progress"/><span>Laporan</span></a>
        <button type="button" data-sidebar-toggle><x-icon name="menu"/><span class="mobile-more-label">Lainnya</span></button>
    @elseif(auth()->user()->hasRole('head'))
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard')?'active':'' }}"><x-icon name="home"/><span>Beranda</span></a>
        <a href="{{ route('admin.content.index') }}" class="{{ request()->routeIs('admin.content.*')?'active':'' }}"><x-icon name="guidance"/><span>Pembinaan</span></a>
        @if(\App\Support\Feature::enabled('report_cards', auth()->user()->institution_id, true))
        <a href="{{ route('admin.report-cards.index') }}" class="{{ request()->routeIs('admin.report-cards.*')?'active':'' }}"><x-icon name="achievement"/><span>Rapor</span></a>
        @else
        <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*')?'active':'' }}"><x-icon name="progress"/><span>Laporan</span></a>
        @endif
        <a href="{{ route('liaison.index') }}" class="{{ request()->routeIs('liaison.*')?'active':'' }}"><x-icon name="discussion"/><span>Pesan</span></a>
        <button type="button" data-sidebar-toggle><x-icon name="menu"/><span class="mobile-more-label">Lainnya</span></button>
    @else
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard')?'active':'' }}"><x-icon name="home"/><span>Beranda</span></a>
        <a href="{{ route('feed.announcements') }}" class="{{ request()->routeIs('feed.announcements')?'active':'' }}"><x-icon name="community"/><span>Info</span></a>
        <a href="{{ route('feed.friday') }}" class="{{ request()->routeIs('feed.friday')?'active':'' }}"><x-icon name="values"/><span>Pembinaan</span></a>
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*')?'active':'' }}"><x-icon name="profile"/><span>Profil</span></a>
        <button type="button" data-sidebar-toggle><x-icon name="menu"/><span class="mobile-more-label">Lainnya</span></button>
    @endif
</nav>
<div class="sidebar-backdrop" data-sidebar-toggle></div>
@else
    @yield('content')
@endif
</body>
</html>
