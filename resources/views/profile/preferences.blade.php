{{-- @phase 5.3 Mobile, Offline & Global --}}
@extends('layouts.app',['pageTitle'=>'Preferensi'])
@section('content')
<div class="v530-page">
    <section class="v530-hero"><div><span class="personal-kicker">FASE 12 · MOBILE & GLOBAL</span><h1>Perangkat, bahasa, dan zona waktu</h1><p>PWA menyimpan hanya aset statis untuk mode offline-safe. Halaman privat dan media pengguna tidak dimasukkan ke Cache Storage.</p></div><span class="v530-badge">v5.3</span></section>
    <section class="v530-grid v530-grid-2">
        <form class="card stack" method="post" action="{{ route('preferences.update') }}">@csrf @method('PUT')
            <span class="eyebrow">PREFERENSI AKUN</span><h2>Tampilan lintas wilayah</h2>
            <label>Bahasa<select name="locale" required>@foreach($locales as $code=>$label)<option value="{{ $code }}" @selected($preference->locale===$code)>{{ $label }}</option>@endforeach</select></label>
            <label>Zona waktu<select name="timezone" required>@foreach($timezones as $timezone)<option value="{{ $timezone }}" @selected($preference->timezone===$timezone)>{{ $timezone }}</option>@endforeach</select></label>
            <label class="v530-check"><input type="checkbox" name="pwa_enabled" value="1" @checked($preference->pwa_enabled)> Izinkan pengalaman instal PWA</label>
            <label class="v530-check"><input type="checkbox" name="email_notifications" value="1" @checked(data_get($preference->notification_preferences,'email',false))> Notifikasi email</label>
            <label class="v530-check"><input type="checkbox" name="whatsapp_notifications" value="1" @checked(data_get($preference->notification_preferences,'whatsapp',false))> Notifikasi WhatsApp</label>
            <button class="button primary" type="submit">Simpan preferensi</button>
        </form>
        <article class="card"><span class="eyebrow">OFFLINE-SAFE</span><h2>Apa yang tersedia saat koneksi terputus?</h2><ul class="v530-list"><li>Halaman fallback offline</li><li>Logo, CSS, JavaScript, dan manifest aplikasi</li><li>Tidak menyimpan jurnal Personal ke cache</li><li>Tidak menyimpan halaman autentikasi atau media privat ke cache</li></ul><div class="personal-guardrail">Sinkronisasi data belajar tetap membutuhkan koneksi agar data terbaru dan batas akses tidak tertinggal.</div></article>
    </section>
</div>
@endsection
