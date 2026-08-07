@extends('layouts.app')
@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand"><img src="/brand/logo-horizontal.svg" alt="Sullamul Hifz"></div>
        <span class="eyebrow">PEMULIHAN AKUN</span>
        <h1>Lupa kata sandi</h1>
        <p class="muted">Masukkan email yang terhubung dengan akun. Untuk akun yang hanya memakai nomor telepon, hubungi admin lembaga.</p>
        @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert danger">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('password.email') }}" class="stack">@csrf
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"></label>
            <button class="button primary wide" type="submit">Kirim tautan</button>
        </form>
        <p class="auth-back"><a href="{{ route('login') }}">← Kembali ke halaman masuk</a></p>
    </div>
</div>
@endsection
