@extends('layouts.app')
@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="brand auth-brand"><span class="brand-mark">SH</span><span><strong>Sullamul Ḥifẓ</strong><small>Bukan Sekadar Hafal, Tapi KUAT</small></span></div>
        <h1>Masuk ke aplikasi</h1>
        <p class="muted">Gunakan email atau nomor telepon yang terdaftar.</p>
        @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert danger">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('login.store') }}" class="stack">@csrf
            <label>Email atau nomor telepon<input name="login" value="{{ old('login') }}" required autofocus autocomplete="username"></label>
            <label>Kata sandi<input type="password" name="password" required autocomplete="current-password"></label>
            <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
            <button class="button primary wide" type="submit">Masuk</button>
        </form>
        <p class="auth-note">Pembinaan berlangsung di dunia nyata. Aplikasi menjaga jejak, komunikasi, dan kesinambungannya.</p>
    </div>
</div>
@endsection
