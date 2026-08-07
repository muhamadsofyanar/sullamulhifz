@extends('layouts.app')
@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand"><img src="/brand/logo-horizontal.svg" alt="Sullamul Hifz"></div>
        <span class="eyebrow">KATA SANDI BARU</span>
        <h1>Atur ulang kata sandi</h1>
        @if($errors->any())<div class="alert danger">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('password.update') }}" class="stack">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label>Email<input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"></label>
            <label>Kata sandi baru<input type="password" name="password" minlength="12" required autocomplete="new-password"></label>
            <label>Ulangi kata sandi<input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password"></label>
            <p class="hint">Minimal 12 karakter, menggunakan huruf besar, huruf kecil, dan angka.</p>
            <button class="button primary wide" type="submit">Simpan kata sandi</button>
        </form>
    </div>
</div>
@endsection
