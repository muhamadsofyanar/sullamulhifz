@extends('layouts.public')
@section('title', 'Pendaftaran Awal — Sullamul Ḥifẓ')
@section('description', 'Formulir pendaftaran awal program Sullamul Ḥifẓ dan TPA Al-Insyirah.')
@section('content')
<section class="page-hero">
    <div class="public-container page-hero-inner">
        <span class="public-eyebrow">PENDAFTARAN AWAL</span>
        <h1>Mulai perjalanan dengan informasi yang cukup.</h1>
        <p>Isi data singkat berikut. Tim kami akan menghubungi wali untuk menjelaskan program, jadwal, dan proses berikutnya.</p>
    </div>
</section>
<section class="public-section soft-section">
    <div class="public-container registration-grid">
        <form class="public-form-card" method="post" action="{{ route('public.registration.store') }}">
            @csrf
            @if(session('success'))<div class="public-alert success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="public-alert danger"><strong>Periksa kembali:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="public-form-grid">
                <label>Nama calon santri<input name="student_name" value="{{ old('student_name') }}" required></label>
                <label>Usia calon santri<input name="student_age" value="{{ old('student_age') }}" placeholder="Contoh: 7 tahun"></label>
                <label>Nama orang tua/wali<input name="guardian_name" value="{{ old('guardian_name') }}" required></label>
                <label>Nomor WhatsApp<input name="guardian_phone" value="{{ old('guardian_phone') }}" required></label>
                <label>Email<input type="email" name="guardian_email" value="{{ old('guardian_email') }}"></label>
                <label>Program yang diminati<select name="desired_program"><option value="">Pilih program</option><option>TPA</option><option>Tahsin</option><option>Tahfizh</option><option>Academy</option><option>Parenting Qur’ani</option></select></label>
                <label class="span-all">Catatan<textarea name="notes" rows="5" placeholder="Kebutuhan, pengalaman belajar, atau pertanyaan awal">{{ old('notes') }}</textarea></label>
            </div>
            <button class="public-button primary" type="submit">Kirim pendaftaran awal</button>
            <p class="form-privacy">Data digunakan hanya untuk menindaklanjuti pendaftaran dan tidak dipublikasikan.</p>
        </form>
        <aside class="registration-aside">
            <img src="/brand/logo-mark.svg" alt="">
            <h2>Apa yang terjadi setelah mengisi?</h2>
            <ol>
                <li>Tim memeriksa data awal.</li>
                <li>Wali dihubungi melalui WhatsApp.</li>
                <li>Program dan kelas dijelaskan.</li>
                <li>Penempatan dilakukan sesuai kesiapan.</li>
            </ol>
        </aside>
    </div>
</section>
@endsection
