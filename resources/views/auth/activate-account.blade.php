@extends('layouts.app')
@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand"><img src="/brand/logo-horizontal.svg" alt="Sullamul Hifz"></div>
        <span class="eyebrow">AKTIVASI AKUN</span>
        <h1>Selamat datang, {{ $invitation->user->name }}</h1>
        <p class="muted">Buat kata sandi pribadi untuk menyelesaikan aktivasi akun.</p>
        @if($errors->any())<div class="alert danger">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('activation.store', $token) }}" class="stack">@csrf
            <label>Kata sandi<input type="password" name="password" minlength="12" required autocomplete="new-password"></label>
            <label>Ulangi kata sandi<input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password"></label>
            <label class="check"><input type="checkbox" name="accept_terms" value="1" required> Saya menyetujui syarat penggunaan dan kebijakan privasi.</label>
            <button class="button primary wide" type="submit">Aktifkan akun</button>
        </form>
        <p class="auth-note">Data anak, bukti tugas, dan komunikasi pribadi hanya digunakan dalam lingkup pembinaan yang berwenang.</p>
    </div>
</div>
@endsection
