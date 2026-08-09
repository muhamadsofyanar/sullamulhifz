@extends('layouts.app',['pageTitle'=>'Perjalanan Saya'])
@section('content')
@php
    $energyLabels = ['low'=>'Perlu ringan','steady'=>'Cukup stabil','strong'=>'Siap bertumbuh'];
    $focusLabels = ['memorization'=>'Hafalan','murajaah'=>'Murāja‘ah','tilawah'=>'Tilawah','reflection'=>'Refleksi'];
@endphp
<div class="personal-page personal-v4-page">
    <section class="personal-v4-hero compact">
        <div><span class="personal-kicker">PERJALANAN SAYA</span><h1>Satu jejak dari seluruh program Qur’an</h1><p>Jurnal pribadi, latihan, Qur’an Journey, dan setoran kepada asatidz diringkas tanpa membandingkan Anda dengan orang lain.</p></div>
        <a class="button secondary" href="{{ route('personal.dashboard') }}">Kembali ke Beranda</a>
    </section>

    <section class="personal-v4-grid">
        <div class="card personal-v4-checkin">
            <span class="eyebrow">CHECK-IN HARI INI</span><h2>Sesuaikan ritme dengan keadaan nyata</h2>
            <form method="post" action="{{ route('personal.check-in.store') }}" class="stack">@csrf
                <div class="personal-form-grid compact">
                    <label>Energi<select name="energy" required><option value="steady">Cukup stabil</option><option value="low">Perlu ritme ringan</option><option value="strong">Siap bertumbuh</option></select></label>
                    <label>Fokus<select name="focus" required><option value="murajaah">Murāja‘ah</option><option value="memorization">Hafalan</option><option value="tilawah">Tilawah</option><option value="reflection">Refleksi</option></select></label>
                    <label class="span-all">Niat kecil hari ini<input name="intention" maxlength="190" placeholder="Contoh: menjaga satu halaman dengan tenang"></label>
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
