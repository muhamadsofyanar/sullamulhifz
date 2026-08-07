@extends('layouts.app')
@section('content')
<div class="auth-page">
    <div class="auth-ornament auth-ornament-left" aria-hidden="true"></div>
    <div class="auth-ornament auth-ornament-right" aria-hidden="true"></div>
    <div class="auth-card">
        <div class="auth-brand">
            <img src="/brand/logo-horizontal.svg" alt="Sullamul Hifz — Bukan Sekadar Hafal, Tapi KUAT">
        </div>
        <span class="eyebrow">EKOSISTEM PENDIDIKAN AL-QUR'AN</span>
        <h1>Masuk ke aplikasi</h1>
        <p class="muted">Gunakan email atau nomor telepon yang terdaftar.</p>
        @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert danger">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('login.store') }}" class="stack">@csrf
            <label>Email atau nomor telepon<input name="login" value="{{ old('login') }}" required autofocus autocomplete="username"></label>
            <label>Kata sandi<input type="password" name="password" required autocomplete="current-password"></label>
            <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
            <button class="button primary wide" type="submit">Masuk</button>
            <a class="text-link center" href="{{ route('password.request') }}">Lupa kata sandi?</a>
        </form>
        <p class="auth-note">Pembinaan berlangsung di dunia nyata. Aplikasi menjaga jejak, komunikasi, dan kesinambungannya.</p>
        <p class="auth-back"><a href="{{ config('sullam.public_url') ?: route('public.home') }}">← Kembali ke website utama</a></p>
    </div>
</div>
@endsection
