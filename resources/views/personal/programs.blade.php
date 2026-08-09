@extends('layouts.app')
@section('content')
<div class="personal-page personal-program-page">
    <section class="personal-hero compact">
        <div>
            <span class="personal-kicker">PROGRAM SAYA</span>
            <h1>Satu Ruang Personal, banyak cara bertumbuh</h1>
            <p>Aktifkan hanya program yang memang ingin Anda jalani. Program yang belum diikuti tidak memenuhi Beranda dan navigasi Personal.</p>
        </div>
        <a class="button secondary" href="{{ route('personal.dashboard') }}">Kembali ke Beranda</a>
    </section>

    <section class="personal-program-grid">
        @foreach($programs as $program)
        <article class="card personal-program-card {{ $program['active'] ? 'is-active' : '' }}">
            <div class="personal-program-icon"><x-icon :name="$program['icon']" size="28"/></div>
            <div>
                <span class="eyebrow">{{ $program['eyebrow'] }}</span>
                <h2>{{ $program['title'] }}</h2>
                <p class="muted">{{ $program['description'] }}</p>
                @if($program['active'])
                    <div class="personal-program-status"><span>Aktif di Ruang Personal</span>@if($program['count'] > 0)<small>{{ $program['count'] }} jejak/program aktif</small>@endif</div>
                    <a class="button primary" href="{{ route($program['route']) }}">Buka program</a>
                @elseif($program['self_enrollable'])
                    <form method="post" action="{{ route('personal.programs.enroll', $program['key']) }}">@csrf
                        <button class="button secondary" type="submit">Tambahkan ke Program Saya</button>
                    </form>
                @else
                    <p class="personal-program-locked">Muncul otomatis bila program yang Anda ikuti memberi akses ke materi ini.</p>
                @endif
            </div>
        </article>
        @endforeach
    </section>

    <p class="personal-privacy-note">Ruang Personal tetap privat. Mengaktifkan sarana belajar tidak membagikan jurnal pribadi Anda; setoran hanya dibagikan saat Anda mengirimkannya ke program pendampingan.</p>
</div>
@endsection
