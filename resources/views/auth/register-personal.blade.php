{{-- @phase 4.5 Personal 2.0 — every person, every aspiration --}}
@extends('layouts.public')
@section('title', 'Daftar Personal — Sullamul Ḥifẓ')
@section('description', 'Buat Ruang Personal Sullamul Hifz: jurnal pribadi dengan program Qur’an yang dapat diaktifkan sesuai kebutuhan.')
@section('content')
<section class="page-hero personal-register-hero">
    <div class="public-container page-hero-inner">
        <span class="public-eyebrow">SETIAP ORANG · SETIAP CITA</span>
        <h1>Perjalanan Anda. Cita-cita Anda. Al-Qur’an tetap menjadi arah.</h1>
        <p>Buat Ruang Personal yang privat untuk anak, remaja, dewasa, maupun lanjut usia. Cita-cita dipakai sebagai konteks menumbuhkan nilai Qur’ani—bukan kelas profesi dan bukan dasar peringkat.</p>
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
                <fieldset class="span-all personal-identity-choice">
                    <legend>Kenali arah awal <span class="optional-label">boleh dilengkapi nanti</span></legend>
                    <div class="public-form-grid">
                        <label>Kelompok usia<select name="age_group"><option value="">Pilih nanti</option>@foreach($ageGroups as $value => $label)<option value="{{ $value }}" @selected(old('age_group') === $value)>{{ $label }}</option>@endforeach</select><small>Kami tidak meminta tanggal lahir.</small></label>
                        <label>Cita-cita atau peran yang ingin dituju<input name="aspiration" value="{{ old('aspiration') }}" maxlength="150" placeholder="Contoh: dokter, guru, pilot, ahli tanaman"></label>
                        <label class="span-all">Jalur pendampingan awal<select name="learning_mode"><option value="">Pilih nanti</option>@foreach($learningModes as $value => $mode)<option value="{{ $value }}" @selected(old('learning_mode') === $value)>{{ $mode['label'] }} — {{ $mode['description'] }}</option>@endforeach</select></label>
                    </div>
                    <p class="personal-choice-help">Minat awal, pilih maksimal lima.</p>
                    <div class="personal-interest-choice">@foreach($interestOptions as $value => $label)<label><input type="checkbox" name="interests[]" value="{{ $value }}" @checked(in_array($value, (array) old('interests', []), true))><span>{{ $label }}</span></label>@endforeach</div>
                    <label class="personal-guardian-ack"><input type="checkbox" name="guardian_acknowledgement" value="1" @checked(old('guardian_acknowledgement'))> <span>Untuk pengguna di bawah 18 tahun, orang tua/wali mengetahui pembuatan ruang ini dan akan mendampingi penggunaan sesuai kebutuhan.</span></label>
                </fieldset>
                <fieldset class="span-all personal-program-choice">
                    <legend>Program yang ingin ditampilkan <span class="optional-label">boleh dipilih nanti</span></legend>
                    <p>Pilih hanya yang ingin langsung digunakan. Pilihan ini dapat diubah dari Program Saya.</p>
                    <div class="personal-program-choice-grid">
                        @foreach($programs as $program)
                        <label>
                            <input type="checkbox" name="programs[]" value="{{ $program['key'] }}" @checked(in_array($program['key'], (array) old('programs', []), true))>
                            <span><x-icon :name="$program['icon']" size="22"/><strong>{{ $program['title'] }}</strong><small>{{ $program['description'] }}</small></span>
                        </label>
                        @endforeach
                    </div>
                </fieldset>
                <label class="span-all personal-terms"><input type="checkbox" name="terms" value="1" required> <span>Saya menyetujui <a href="{{ route('public.terms') }}" target="_blank">Syarat & Ketentuan</a> dan memahami <a href="{{ route('public.privacy') }}" target="_blank">kebijakan privasi</a>.</span></label>
            </div>
            <button class="public-button primary" type="submit">Buat ruang Personal saya</button>
            <p class="form-privacy">Ruang Personal bersifat privat. Profil usia, minat, cita-cita, jurnal, dan portofolio tidak ditampilkan sebagai ranking atau profil publik.</p>
            <p class="personal-login-note">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini →</a></p>
        </form>
        <aside class="personal-benefit-card">
            <img src="/brand/logo-mark.svg" alt="">
            <span>AL-QUR’AN MENJADI ARAH</span>
            <h2>Satu ruang yang tumbuh bersama penggunanya</h2>
            <ul>
                <li><strong>Setiap usia</strong><small>Bahasa dan pendampingan dapat disesuaikan tanpa meminta tanggal lahir.</small></li>
                <li><strong>Setiap cita-cita</strong><small>Dokter, guru, pilot, ahli tanaman, komunikator, dan cita-cita lain tetap diarahkan pada nilai Qur’ani.</small></li>
                <li><strong>Jurnal Qur’an</strong><small>Hafalan, murāja‘ah, tilawah, dan refleksi dalam satu jejak.</small></li>
                <li><strong>Portofolio privat</strong><small>Simpan karya, pelayanan, keterampilan, dan pertumbuhan tanpa skor atau perbandingan.</small></li>
                <li><strong>Empat jalur</strong><small>Mandiri, bersama orang tua, ustadz privat, atau melalui lembaga.</small></li>
            </ul>
        </aside>
    </div>
</section>
@endsection
