@extends('layouts.app')
@section('content')
@php($academyLogin = strtolower(request()->getHost()) === strtolower((string) config('sullam.academy_host')))
<div class="auth-page">
    <div class="auth-ornament auth-ornament-left" aria-hidden="true"></div>
    <div class="auth-ornament auth-ornament-right" aria-hidden="true"></div>
    <div class="auth-card">
        <div class="auth-brand"><img src="/brand/logo-horizontal.svg" alt="Sullamul Hifz — Bukan Sekadar Hafal, Tapi KUAT"></div>
        <span class="eyebrow">{{ $academyLogin ? 'SULLAMUL ḤIFẒ ACADEMY' : "EKOSISTEM PENDIDIKAN AL-QUR'AN" }}</span>
        <h1>{{ $academyLogin ? 'Masuk ke Academy' : 'Masuk ke aplikasi' }}</h1>
        <p class="muted">Gunakan email atau nomor telepon yang terdaftar. Akun Personal, Academy, dan aplikasi menggunakan pintu masuk yang sama.</p>
        @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert danger">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ $academyLogin ? url('/login') : route('login.store') }}" class="stack">@csrf
            <label>Email atau nomor telepon<input name="login" value="{{ old('login') }}" required autofocus autocomplete="username"></label>
            <label>Kata sandi<input type="password" name="password" required autocomplete="current-password"></label>
            <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
            <button class="button primary wide" type="submit">{{ $academyLogin ? 'Masuk Academy' : 'Masuk' }}</button>
            <a class="text-link center" href="{{ $academyLogin ? url('/lupa-kata-sandi') : route('password.request') }}">Lupa kata sandi?</a>
        </form>
        @unless($academyLogin)<p class="auth-note"><strong>Belum punya akun?</strong> <a class="text-link" href="{{ route('personal.register') }}">Daftar Personal mandiri →</a></p>@endunless
        <p class="auth-note">Pembinaan berlangsung di dunia nyata. Aplikasi dan Academy menjaga jejak, komunikasi, dan kesinambungannya.</p>
        <p class="auth-back"><a href="{{ $academyLogin ? config('sullam.public_url').'/academy' : (config('sullam.public_url') ?: route('public.home')) }}">← {{ $academyLogin ? 'Tentang Academy' : 'Kembali ke website utama' }}</a></p>
    </div>
</div>
@endsection
