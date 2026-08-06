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
    <link rel="stylesheet" href="/css/app.css">
    <script defer src="/js/app.js"></script>
</head>
<body>
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
            @endif
            @if(auth()->user()->hasAnyRole(['superadmin','institution_admin','head']))
                <a href="{{ route('admin.content.index') }}" class="{{ request()->routeIs('admin.content.*') ? 'active' : '' }}" @if(request()->routeIs('admin.content.*')) aria-current="page" @endif>
                    <x-icon name="guidance"/><span>Konten & Pembinaan</span>
                </a>
                <a href="{{ route('admin.student-pledge.edit') }}" class="{{ request()->routeIs('admin.student-pledge.*') ? 'active' : '' }}">
                    <x-icon name="values"/><span>Kelola Ikrar</span>
                </a>
                <a href="{{ route('admin.website.index') }}" class="{{ request()->routeIs('admin.website.*') ? 'active' : '' }}">
                    <x-icon name="community"/><span>Website</span>
                </a>
                <a href="{{ route('admin.report-cards.index') }}" class="{{ request()->routeIs('admin.report-cards.*') ? 'active' : '' }}">
                    <x-icon name="report"/><span>Rapor</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" @if(request()->routeIs('admin.reports.*')) aria-current="page" @endif>
                    <x-icon name="report"/><span>Laporan</span>
                </a>
            @endif
            @if(auth()->user()->hasRole('teacher'))
                <a href="{{ route('teacher.classrooms.index') }}" class="{{ request()->routeIs('teacher.classrooms.*') || request()->routeIs('teacher.meetings.*') ? 'active' : '' }}">
                    <x-icon name="classroom"/><span>Kelas Saya</span>
                </a>
                <a href="{{ route('teacher.assignments.index') }}" class="{{ request()->routeIs('teacher.assignments.*') ? 'active' : '' }}">
                    <x-icon name="assignment"/><span>Tugas</span>
                </a>
            @endif
            @if(auth()->user()->hasRole('guardian'))
                <a href="{{ route('guardian.tasks.index') }}" class="{{ request()->routeIs('guardian.tasks.*') ? 'active' : '' }}">
                    <x-icon name="assignment"/><span>Tugas Anak</span>
                </a>
            @endif
            <a href="{{ route('liaison.index') }}" class="{{ request()->routeIs('liaison.*') ? 'active' : '' }}" @if(request()->routeIs('liaison.*')) aria-current="page" @endif>
                <x-icon name="discussion"/><span>Buku Penghubung</span>
            </a>
            <a href="{{ route('feed.announcements') }}" class="{{ request()->routeIs('feed.announcements') ? 'active' : '' }}" @if(request()->routeIs('feed.announcements')) aria-current="page" @endif>
                <x-icon name="community"/><span>Pengumuman</span>
            </a>
            <a href="{{ route('feed.friday') }}" class="{{ request()->routeIs('feed.friday') ? 'active' : '' }}" @if(request()->routeIs('feed.friday')) aria-current="page" @endif>
                <x-icon name="values"/><span>Pembinaan Jumat</span>
            </a>
            <a href="{{ route('feed.pledge') }}" class="{{ request()->routeIs('feed.pledge') ? 'active' : '' }}" @if(request()->routeIs('feed.pledge')) aria-current="page" @endif>
                <x-icon name="academic"/><span>Ikrar Santri</span>
            </a>
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
            <div class="topbar-title"><strong>{{ $pageTitle ?? 'Sullamul Ḥifẓ' }}</strong><small>{{ auth()->user()->institution?->name }}</small></div>
            <a class="avatar" href="{{ route('profile.edit') }}" aria-label="Buka profil {{ auth()->user()->name }}">{{ strtoupper(mb_substr(auth()->user()->name,0,1)) }}</a>
        </header>
        <div class="content-wrap">
            @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert danger">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="alert danger"><strong>Periksa kembali isian:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
<div class="sidebar-backdrop" data-sidebar-toggle></div>
@else
    @yield('content')
@endif
</body>
</html>
