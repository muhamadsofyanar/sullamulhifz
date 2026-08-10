{{-- @phase 4.9 Learning & Academy Integration — unified Personal learning space --}}
@extends('layouts.app')
@section('content')
@php($activeModuleKeys = $activeModules->pluck('key')->all())
<div class="learning-hub-v490">
    <section class="learning-hub-hero">
        <div>
            <span class="learning-hub-kicker">FASE 8 · v4.9 · RUANG BELAJAR TERPADU</span>
            <h1>Satu tempat untuk melanjutkan belajar</h1>
            <p>Latihan Qur’an, Qur’an Journey, program bersama Asatidz, Academy, target Personal, arahan Ustadz, dan tugas lembaga diringkas di sini tanpa membuka jurnal privat ke pihak lain.</p>
        </div>
        <a class="button secondary" href="{{ route('personal.programs.index') }}">Kelola program</a>
    </section>

    <section class="learning-hub-stats" aria-label="Ringkasan belajar">
        <article><span>Program aktif</span><strong>{{ $activeModules->count() }}</strong><small>modul yang memang Anda ikuti</small></article>
        <article><span>Latihan 30 hari</span><strong>{{ $practice['sessions_30d'] }}</strong><small>{{ $practice['minutes_30d'] }} menit tercatat</small></article>
        <article><span>Academy</span><strong>{{ $academy['completed'] }}/{{ max($academy['started'], $academy['completed']) }}</strong><small>materi selesai / dimulai</small></article>
        <article><span>Target aktif</span><strong>{{ $personalGoals->count() }}</strong><small>target milik Personal</small></article>
    </section>

    <section class="card learning-hub-next">
        <div class="section-head">
            <div><span class="eyebrow">LANJUTKAN DARI SINI</span><h2>Langkah berikutnya</h2><p class="muted">Prioritas diringkas dari data yang memang boleh dilihat oleh akun Anda.</p></div>
        </div>
        @if($nextActions->isEmpty())
            <div class="empty-state"><p>Belum ada langkah aktif. Pilih program pertama atau buat target Personal.</p><a class="button primary" href="{{ route('personal.programs.index') }}">Pilih program</a></div>
        @else
            <div class="learning-hub-action-list">
                @foreach($nextActions as $action)
                @php
                    $url = route($action['route'], $action['route_params'] ?? []);
                    if (!empty($action['fragment'])) $url .= '#'.$action['fragment'];
                @endphp
                <a href="{{ $url }}">
                    <span class="learning-hub-source">{{ $action['source'] }}</span>
                    <strong>{{ $action['title'] }}</strong>
                    <small>{{ $action['meta'] }}</small>
                    <b>→</b>
                </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="card">
        <div class="section-head"><div><span class="eyebrow">MESIN BELAJAR</span><h2>Semua program yang aktif</h2><p class="muted">Ruang Belajar tidak menggandakan data. Setiap kartu tetap membuka mesin aslinya.</p></div></div>
        @if($activeModules->isEmpty())
            <p class="muted">Belum ada modul aktif.</p>
        @else
        <div class="learning-hub-modules">
            @foreach($activeModules as $module)
            <a href="{{ route($module['route']) }}">
                <span class="learning-hub-module-icon"><x-icon :name="$module['icon']" size="24"/></span>
                <div><span>{{ $module['eyebrow'] }}</span><strong>{{ $module['title'] }}</strong><p>{{ $module['description'] }}</p></div>
                <b>→</b>
            </a>
            @endforeach
        </div>
        @endif
    </section>

    <section class="learning-hub-grid">
        <div class="card">
            <div class="section-head"><div><span class="eyebrow">TARGET PERSONAL</span><h2>Yang Anda tetapkan sendiri</h2></div><a href="{{ route('personal.dashboard') }}#target">Kelola →</a></div>
            @forelse($personalGoals as $goal)
                @php($percent = min(100, (int) round(($goal->progress_value / max(1, $goal->target_value)) * 100)))
                <article class="learning-hub-row"><div><strong>{{ $goal->title }}</strong><small>{{ $goal->progress_value }} / {{ $goal->target_value }}@if($goal->due_on) · {{ $goal->due_on->translatedFormat('d M Y') }}@endif</small></div><span>{{ $percent }}%</span></article>
            @empty
                <p class="muted">Belum ada target Personal aktif.</p>
            @endforelse
        </div>

        <div class="card">
            <div class="section-head"><div><span class="eyebrow">USTADZ PRIVAT</span><h2>Arahan bimbingan</h2></div><a href="{{ route('mentorship.index') }}">Buka →</a></div>
            @forelse($mentorSessions as $session)
                <article class="learning-hub-row"><div><strong>{{ $session->focus }}</strong><small>{{ $session->mentor?->name ?: 'Ustadz' }} · {{ $session->scheduled_at ? $session->scheduled_at->translatedFormat('d M Y H:i') : 'belum dijadwalkan' }}</small></div><span>{{ ucfirst($session->status) }}</span></article>
            @empty
                <p class="muted">Belum ada sesi bimbingan yang menunggu atau terjadwal.</p>
            @endforelse
        </div>

        <div class="card">
            <div class="section-head"><div><span class="eyebrow">PROGRAM ASATIDZ</span><h2>Setoran & review</h2></div>@if(in_array('guided_learning', $activeModuleKeys, true))<a href="{{ route('personal.learning.index') }}">Buka →</a>@endif</div>
            @forelse($guidedEnrollments as $enrollment)
                @php($latest = $enrollment->submissions->first())
                <article class="learning-hub-row"><div><strong>{{ $enrollment->program?->title ?? 'Program Al-Qur’an' }}</strong><small>{{ $enrollment->program?->provider?->name ?: 'Penyelenggara' }}@if($latest) · setoran terakhir {{ $latest->review_status }}@endif</small></div><span>{{ ucfirst($enrollment->status) }}</span></article>
            @empty
                <p class="muted">Belum ada program Asatidz yang diikuti.</p>
            @endforelse
        </div>

        <div class="card">
            <div class="section-head"><div><span class="eyebrow">QUR’AN JOURNEY</span><h2>Program bertahap</h2></div>@if(in_array('quran_journey', $activeModuleKeys, true))<a href="{{ route('quran-journey.index') }}">Buka →</a>@endif</div>
            @forelse($journeyEnrollments as $enrollment)
                <article class="learning-hub-row"><div><strong>{{ $enrollment->template?->name ?? 'Perjalanan Qur’an' }}</strong><small>{{ $enrollment->purpose ?: 'Program bertahap' }}</small></div><span>Langkah {{ $enrollment->current_step }}</span></article>
            @empty
                <p class="muted">Belum ada Qur’an Journey aktif.</p>
            @endforelse
        </div>

        <div class="card">
            <div class="section-head"><div><span class="eyebrow">ACADEMY</span><h2>Materi yang terhubung</h2></div>@if(in_array('academy', $activeModuleKeys, true))<a href="{{ route('academy.index') }}">Buka →</a>@endif</div>
            <div class="learning-hub-mini-stats"><span><b>{{ $academy['programs'] }}</b> program</span><span><b>{{ $academy['started'] }}</b> dimulai</span><span><b>{{ $academy['completed'] }}</b> selesai</span></div>
            @forelse($academyRecommendations as $recommendation)
                <a class="learning-hub-row linked" href="{{ route('academy.lesson', $recommendation->academy_lesson_id) }}"><div><strong>{{ $recommendation->lesson?->title ?? 'Materi Academy' }}</strong><small>{{ $recommendation->message ?: 'Direkomendasikan untuk Anda' }}</small></div><span>→</span></a>
            @empty
                <p class="muted">Belum ada rekomendasi materi aktif.</p>
            @endforelse
        </div>

        <div class="card">
            <div class="section-head"><div><span class="eyebrow">LEMBAGA</span><h2>Tugas dari ruang belajar</h2></div></div>
            @forelse($institutionAssignments as $recipient)
                <article class="learning-hub-row"><div><strong>{{ $recipient->assignment?->title ?? 'Tugas belajar' }}</strong><small>{{ $recipient->assignment?->teacher?->full_name ?: 'Pengajar' }}@if($recipient->assignment?->due_at) · tenggat {{ $recipient->assignment->due_at->translatedFormat('d M Y H:i') }}@endif</small></div><span>{{ ucfirst(str_replace('_', ' ', $recipient->status)) }}</span></article>
            @empty
                <p class="muted">Tidak ada tugas lembaga aktif yang terhubung ke profil Personal ini.</p>
            @endforelse
        </div>
    </section>

    <p class="learning-hub-privacy">Ruang Belajar hanya merangkum data yang sudah dimiliki atau diizinkan untuk akun ini. Jurnal Personal dan isi portofolio tetap tidak dibuka otomatis kepada Ustadz, keluarga, atau lembaga.</p>
</div>
@endsection
