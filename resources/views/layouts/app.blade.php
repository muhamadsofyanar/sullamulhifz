<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f5132">
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
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark">SH</span>
            <span><strong>Sullamul Ḥifẓ</strong><small>Bukan Sekadar Hafal, Tapi KUAT</small></span>
        </a>
        <nav class="nav-list">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Beranda</a>
            @if(auth()->user()->hasAnyRole(['superadmin','institution_admin']))
                <a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}">Santri</a>
                <a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">Guru</a>
                <a href="{{ route('admin.academic.index') }}" class="{{ request()->routeIs('admin.academic.*') ? 'active' : '' }}">Akademik</a>
            @endif
            @if(auth()->user()->hasAnyRole(['superadmin','institution_admin','head']))
                <a href="{{ route('admin.content.index') }}" class="{{ request()->routeIs('admin.content.*') ? 'active' : '' }}">Konten & Pembinaan</a>
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Laporan</a>
            @endif
            @if(auth()->user()->hasRole('teacher'))
                <a href="{{ route('teacher.classrooms.index') }}" class="{{ request()->routeIs('teacher.classrooms.*') || request()->routeIs('teacher.meetings.*') ? 'active' : '' }}">Kelas Saya</a>
                <a href="{{ route('teacher.assignments.index') }}" class="{{ request()->routeIs('teacher.assignments.*') ? 'active' : '' }}">Tugas</a>
            @endif
            @if(auth()->user()->hasRole('guardian'))
                <a href="{{ route('guardian.tasks.index') }}" class="{{ request()->routeIs('guardian.tasks.*') ? 'active' : '' }}">Tugas Anak</a>
            @endif
            <a href="{{ route('liaison.index') }}" class="{{ request()->routeIs('liaison.*') ? 'active' : '' }}">Buku Penghubung</a>
            <a href="{{ route('feed.announcements') }}" class="{{ request()->routeIs('feed.announcements') ? 'active' : '' }}">Pengumuman</a>
            <a href="{{ route('feed.friday') }}" class="{{ request()->routeIs('feed.friday') ? 'active' : '' }}">Pembinaan Jumat</a>
        </nav>
        <div class="sidebar-footer">
            <a href="{{ route('profile.edit') }}">{{ auth()->user()->name }}</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Keluar</button></form>
        </div>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <button type="button" class="icon-button" data-sidebar-toggle aria-label="Buka menu">☰</button>
            <div><strong>{{ $pageTitle ?? 'Sullamul Ḥifẓ' }}</strong><small>{{ auth()->user()->institution?->name }}</small></div>
            <a class="avatar" href="{{ route('profile.edit') }}">{{ strtoupper(mb_substr(auth()->user()->name,0,1)) }}</a>
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
