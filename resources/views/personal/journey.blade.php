{{-- @phase 4.5 Personal 2.0 — aspiration context and private portfolio --}}
@extends('layouts.app',['pageTitle'=>'Perjalanan Saya'])
@section('content')
@php
    $energyLabels = ['low'=>'Perlu ringan','steady'=>'Cukup stabil','strong'=>'Siap bertumbuh'];
    $focusLabels = ['memorization'=>'Hafalan','murajaah'=>'Murāja‘ah','tilawah'=>'Tilawah','reflection'=>'Refleksi'];
@endphp
<div class="personal-page personal-v4-page">
    <section class="personal-v4-hero compact">
        <div><span class="personal-kicker">PERJALANAN SAYA</span><h1>Satu jejak dari seluruh program Qur’an</h1><p>@if($profile->aspiration)Cita-cita <strong>{{ $profile->aspiration }}</strong> menjadi konteks pertumbuhan Anda. @endif Jurnal, latihan, program, dan portofolio diringkas tanpa membandingkan Anda dengan orang lain.</p></div>
        <a class="button secondary" href="{{ route('personal.dashboard') }}">Kembali ke Beranda</a>
    </section>

    @if($profile->quranic_purpose || $profile->learning_mode)
    <section class="personal-purpose-banner">
        <div><span>TUJUAN QUR’ANI</span><strong>{{ $profile->quranic_purpose ?: 'Menjaga hubungan dengan Al-Qur’an secara bertahap.' }}</strong></div>
        <small>{{ $learningModes[$profile->learning_mode]['label'] ?? 'Mandiri' }} · Profil ini privat</small>
    </section>
    @endif

    <section class="personal-v4-grid">
        <div class="card personal-v4-checkin">
            <span class="eyebrow">CHECK-IN HARI INI</span><h2>Sesuaikan ritme dengan keadaan nyata</h2>
            <form method="post" action="{{ route('personal.check-in.store') }}" class="stack">@csrf
                <div class="personal-form-grid compact">
                    <label>Energi<select name="energy" required><option value="steady">Cukup stabil</option><option value="low">Perlu ritme ringan</option><option value="strong">Siap bertumbuh</option></select></label>
                    <label>Fokus<select name="focus" required><option value="murajaah">Murāja‘ah</option><option value="memorization">Hafalan</option><option value="tilawah">Tilawah</option><option value="reflection">Refleksi</option></select></label>
                    <label class="span-all">Niat kecil hari ini<input name="intention" maxlength="190" placeholder="{{ $profile->aspiration ? 'Satu nilai Qur’ani untuk langkah menuju '.$profile->aspiration : 'Contoh: menjaga satu halaman dengan tenang' }}"></label>
                    <label class="span-all">Refleksi <span class="muted">opsional</span><textarea name="reflection" rows="3" maxlength="2000"></textarea></label>
                </div>
                <button class="button primary" type="submit">Simpan check-in</button>
            </form>
            @if($checkIns->isNotEmpty())
            <div class="v4-checkin-history">@foreach($checkIns->take(7) as $checkIn)<span><b>{{ $checkIn->check_in_on->translatedFormat('d M') }}</b>{{ $energyLabels[$checkIn->energy] ?? $checkIn->energy }} · {{ $focusLabels[$checkIn->focus] ?? $checkIn->focus }}</span>@endforeach</div>
            @endif
        </div>
        <div class="card">
            <span class="eyebrow">PROGRAM AKTIF</span><h2>{{ $activeModules->count() }} jalur di Ruang Personal</h2>
            <div class="v4-module-mini-list">@forelse($activeModules as $module)<a href="{{ route($module['route']) }}"><x-icon :name="$module['icon']" size="20"/><span><b>{{ $module['title'] }}</b><small>{{ $module['count'] }} jejak/program</small></span><strong>→</strong></a>@empty<p class="muted">Belum ada program aktif. Jurnal dan target tetap dapat digunakan.</p>@endforelse</div>
            <a class="button secondary" href="{{ route('personal.programs.index') }}">Kelola Program Saya</a>
        </div>
    </section>

    <section class="card" id="portofolio">
        <div class="section-head"><div><span class="eyebrow">PORTOFOLIO PRIVAT</span><h2>Jejak pertumbuhan yang bermakna</h2><p class="muted">Simpan karya, pelayanan, keterampilan, dan refleksi. Tidak ada nilai, ranking, atau perbandingan dengan pengguna lain.</p></div></div>
        <div class="personal-portfolio-layout">
            <div class="personal-portfolio-list">
                @forelse($portfolioEntries as $entry)
                <article><time>{{ ($entry->occurred_on ?: $entry->created_at)->translatedFormat('d M Y') }}</time><span>{{ $portfolioCategories[$entry->category] ?? 'Jejak pertumbuhan' }}</span><h3>{{ $entry->title }}</h3>@if($entry->description)<p>{{ $entry->description }}</p>@endif@if(data_get($entry->metadata, 'quranic_value'))<small>Nilai Qur’ani · {{ data_get($entry->metadata, 'quranic_value') }}</small>@endif</article>
                @empty
                <div class="empty-state"><p>Belum ada portofolio. Jejak pertama boleh sederhana: membantu orang lain, menyelesaikan karya, merawat tanaman, berlatih komunikasi, atau menjaga satu kebiasaan baik.</p></div>
                @endforelse
            </div>
            <form method="post" action="{{ route('personal.portfolio.store') }}" class="stack personal-portfolio-form">@csrf
                <h3>Tambah jejak</h3>
                <label>Kategori<select name="category" required>@foreach($portfolioCategories as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                <label>Judul<input name="title" maxlength="190" required placeholder="Contoh: Menanam dan merawat bibit cabai"></label>
                <label>Tanggal<input type="date" name="occurred_on" value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" required></label>
                <label>Deskripsi<textarea name="description" rows="3" maxlength="2000" placeholder="Apa yang dilakukan dan dipelajari?"></textarea></label>
                <label>Nilai Qur’ani <span class="muted">opsional</span><input name="quranic_value" maxlength="190" placeholder="Contoh: amanah, sabar, menjaga ciptaan Allah"></label>
                <label>Hubungan dengan cita-cita <span class="muted">opsional</span><textarea name="aspiration_connection" rows="2" maxlength="300" placeholder="Bagaimana jejak ini mendekatkan Anda pada peran yang ingin dituju?"></textarea></label>
                <button class="button primary" type="submit">Simpan portofolio privat</button>
            </form>
        </div>
    </section>

    <section class="card">
        <div class="section-head"><div><span class="eyebrow">TIMELINE TERPADU</span><h2>Aktivitas terbaru</h2><p class="muted">Jejak diambil dari seluruh modul yang Anda gunakan.</p></div></div>
        <div class="v4-timeline">
            @forelse($timeline as $item)
            <article><time>{{ $item['date']->translatedFormat('d M Y') }}</time><span class="v4-timeline-dot"></span><div><small>{{ $item['type'] }}</small><strong>{{ $item['title'] }}</strong><p>{{ $item['detail'] }}</p></div><b>{{ str($item['status'] ?: 'tercatat')->replace('_',' ')->headline() }}</b></article>
            @empty<div class="empty-state"><p>Belum ada jejak. Mulai dari check-in atau catat satu aktivitas yang benar-benar dilakukan.</p></div>@endforelse
        </div>
    </section>
</div>
@endsection
