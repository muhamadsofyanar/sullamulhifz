@extends('layouts.public')
@section('title', 'Daftar Personal — Sullamul Ḥifẓ')
@section('description', 'Buat Ruang Personal Sullamul Hifz: jurnal pribadi dengan program Qur’an yang dapat diaktifkan sesuai kebutuhan.')
@section('content')
<section class="page-hero personal-register-hero">
    <div class="public-container page-hero-inner">
        <span class="public-eyebrow">SULLAMUL ḤIFẒ PERSONAL</span>
        <h1>Perjalanan Anda. Ritme Anda. Jejaknya tetap terjaga.</h1>
        <p>Daftar mandiri tanpa harus bergabung dengan lembaga. Jurnal dan target pribadi selalu tersedia; Latihan Qur’an, Qur’an Journey, Program Asatidz, dan Academy hadir sesuai program yang benar-benar Anda ikuti.</p>
    </div>
</section>
<section class="public-section soft-section">
    <div class="public-container personal-register-grid">
        <form class="public-form-card personal-register-card" method="post" action="{{ route('personal.register.store') }}">
            @csrf
            @if($errors->any())<div class="public-alert danger"><strong>Periksa kembali:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="public-form-grid">
                <label class="span-all">Nama lengkap<input name="name" value="{{ old('name') }}" required autocomplete="name" autofocus></label>
                <label>Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
                <label>Nomor WhatsApp <span class="optional-label">opsional</span><input name="phone" value="{{ old('phone') }}" autocomplete="tel"></label>
                <label>Kata sandi<input type="password" name="password" required minlength="12" autocomplete="new-password"><small>Minimal 12 karakter, huruf besar-kecil, dan angka.</small></label>
                <label>Ulangi kata sandi<input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></label>
                <label class="span-all personal-terms"><input type="checkbox" name="terms" value="1" required> <span>Saya menyetujui <a href="{{ route('public.terms') }}" target="_blank">Syarat & Ketentuan</a> dan memahami <a href="{{ route('public.privacy') }}" target="_blank">kebijakan privasi</a>.</span></label>
            </div>
            <button class="public-button primary" type="submit">Buat ruang Personal saya</button>
            <p class="form-privacy">Ruang Personal bersifat privat. Pengguna Personal lain tidak dapat melihat catatan perjalanan Anda.</p>
            <p class="personal-login-note">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini →</a></p>
        </form>
        <aside class="personal-benefit-card">
            <img src="/brand/logo-mark.svg" alt="">
            <span>MULAI DARI YANG NYATA</span>
            <h2>Apa yang langsung bisa digunakan?</h2>
            <ul>
                <li><strong>Arah pribadi</strong><small>Tentukan fokus, target, dan ritme harian.</small></li>
                <li><strong>Jurnal Qur’an</strong><small>Hafalan, murāja‘ah, tilawah, dan refleksi dalam satu jejak.</small></li>
                <li><strong>Konsistensi</strong><small>Lihat menit belajar, hari aktif, dan streak tanpa kompetisi dengan orang lain.</small></li>
                <li><strong>Arahan harian</strong><small>Saran sederhana berdasarkan aktivitas nyata Anda, bukan label kepribadian.</small></li>
                <li><strong>Program yang fleksibel</strong><small>Aktifkan Latihan Qur’an, Qur’an Journey, atau pendampingan asatidz tanpa memenuhi ruang Anda dengan program yang tidak diikuti.</small></li>
            </ul>
        </aside>
    </div>
</section>
@endsection
