@extends('layouts.app')
@section('content')
@php
    $metricLabels = [
        'practice_minutes'=>'menit latihan', 'sessions'=>'sesi', 'active_days'=>'hari aktif',
        'memorization_verses'=>'ayat hafalan', 'murajaah_verses'=>'ayat murāja‘ah',
    ];
    $activityLabels = ['memorization'=>'Hafalan baru','murajaah'=>'Murāja‘ah','tilawah'=>'Tilawah','reflection'=>'Refleksi'];
    $ratingLabels = ['needs_review'=>'Perlu diulang','developing'=>'Sedang tumbuh','steady'=>'Cukup mantap','strong'=>'Kuat'];
@endphp
<div class="personal-page">
    <section class="personal-hero">
        <div>
            <span class="personal-kicker">RUANG PERSONAL · PRIVAT</span>
            <h1>Assalamu‘alaikum, {{ auth()->user()->name }}</h1>
            <p>Yang penting bukan seberapa cepat, tetapi seberapa jujur Anda membaca keadaan hafalan dan menjaga kesinambungannya.</p>
        </div>
        <div class="personal-streak"><strong>{{ $streak }}</strong><span>hari streak</span><small>berdasarkan hari aktif berurutan</small></div>
    </section>

    @if(!$profile->onboarding_completed_at)
    <section class="card personal-onboarding">
        <div class="section-head"><div><span class="eyebrow">LANGKAH PERTAMA</span><h2>Atur ritme yang realistis</h2><p class="muted">Ini bukan tes kemampuan. Pilihan ini hanya membantu menyusun fokus dashboard Anda.</p></div></div>
        <form method="post" action="{{ route('personal.onboarding') }}" class="personal-form-grid">
            @csrf @method('put')
            <label>Posisi perjalanan saat ini<select name="experience_level" required><option value="">Pilih</option><option value="starting">Baru memulai</option><option value="restarting">Mulai kembali setelah jeda</option><option value="active">Sedang aktif menghafal</option><option value="maintaining">Fokus menjaga hafalan</option></select></label>
            <label>Fokus utama<select name="primary_focus" required><option value="balanced">Seimbang: hafalan + murāja‘ah</option><option value="memorization">Menambah hafalan</option><option value="murajaah">Menguatkan murāja‘ah</option></select></label>
            <label>Ritme harian<input type="number" min="5" max="180" name="daily_minutes" value="{{ old('daily_minutes',20) }}" required><small>menit/hari</small></label>
            <label>Target juz <span class="muted">opsional</span><input type="number" min="1" max="30" name="target_juz" value="{{ old('target_juz') }}" placeholder="1–30"></label>
            <label>Target surah <span class="muted">opsional</span><select name="target_surah_id"><option value="">Belum ditentukan</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
            <label>Tanggal target <span class="muted">opsional</span><input type="date" name="target_date" value="{{ old('target_date') }}" min="{{ now()->addDay()->toDateString() }}"></label>
            <div class="span-all"><button class="button primary" type="submit">Simpan arah perjalanan</button></div>
        </form>
    </section>
    @endif

    <section class="personal-stat-grid" aria-label="Ringkasan tujuh hari">
        <article><span>Hari ini</span><strong>{{ $today_minutes }}</strong><small>menit · {{ $today_sessions }} sesi</small></article>
        <article><span>7 hari</span><strong>{{ $week_minutes }}</strong><small>menit latihan</small></article>
        <article><span>Konsistensi</span><strong>{{ $week_active_days }}/7</strong><small>hari aktif</small></article>
        <article><span>Murāja‘ah</span><strong>{{ $week_murajaah_verses }}</strong><small>ayat tercatat</small></article>
    </section>

    <section class="card guided-dashboard-cta">
        <div><span class="eyebrow">BELAJAR DENGAN ARAH</span><h2>Dari latihan mandiri ke pendampingan asatidz</h2><p class="muted">Dengarkan murattal, ikuti program seperti Tahfizh Juz 30/Tahsin, lalu kirim setoran yang Anda pilih untuk mendapat koreksi teks atau voice note.</p></div>
        <a class="button primary" href="{{ route('personal.learning.index') }}">Buka Belajar & Audio</a>
    </section>

    <section class="personal-grid-two">
        <div class="card personal-guidance">
            <div class="section-head"><div><span class="eyebrow">HARI INI</span><h2>Apa yang perlu dijaga?</h2></div></div>
            <div class="personal-guidance-list">
                @foreach($guidance as $item)
                <article><span>{{ $loop->iteration }}</span><div><strong>{{ $item['title'] }}</strong><p>{{ $item['body'] }}</p></div></article>
                @endforeach
            </div>
            <p class="personal-guardrail">Arahan dibuat dari ritme dan jurnal aktivitas Anda. STIFIn tidak digunakan untuk menentukan kemampuan, target, atau rekomendasi belajar.</p>
        </div>

        <div class="card" id="catat">
            <div class="section-head"><div><span class="eyebrow">CATAT JEJAK</span><h2>Aktivitas Qur’an</h2><p class="muted">Catat yang benar-benar dilakukan. Tidak perlu terlihat sempurna.</p></div></div>
            <form method="post" action="{{ route('personal.activities.store') }}" class="stack">
                @csrf
                <div class="personal-form-grid compact">
                    <label>Jenis<select name="activity_type" required><option value="memorization">Hafalan baru</option><option value="murajaah">Murāja‘ah</option><option value="tilawah">Tilawah</option><option value="reflection">Refleksi</option></select></label>
                    <label>Tanggal<input type="date" name="practiced_on" value="{{ old('practiced_on',today()->toDateString()) }}" max="{{ today()->toDateString() }}" required></label>
                    <label class="span-all">Surah <span class="muted">tidak wajib untuk refleksi</span><select name="surah_id"><option value="">Pilih surah</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }} · {{ $surah->verse_count }} ayat</option>@endforeach</select></label>
                    <label>Ayat mulai<input type="number" min="1" name="start_verse" value="{{ old('start_verse') }}"></label>
                    <label>Ayat akhir<input type="number" min="1" name="end_verse" value="{{ old('end_verse') }}"></label>
                    <label>Durasi<input type="number" min="1" max="600" name="duration_minutes" value="{{ old('duration_minutes',10) }}" required><small>menit</small></label>
                    <label>Penilaian diri<select name="self_rating"><option value="">Tanpa penilaian</option><option value="needs_review">Perlu diulang</option><option value="developing">Sedang tumbuh</option><option value="steady">Cukup mantap</option><option value="strong">Kuat</option></select></label>
                    <label class="span-all">Catatan<textarea name="notes" rows="3" maxlength="2000" placeholder="Apa yang terasa kuat? Bagian mana yang perlu diulang?">{{ old('notes') }}</textarea></label>
                </div>
                <button class="button primary" type="submit">Simpan aktivitas</button>
            </form>
        </div>
    </section>

    <section class="card" id="target">
        <div class="section-head"><div><span class="eyebrow">TARGET PRIBADI</span><h2>Ukur kemajuan tanpa membandingkan diri</h2></div></div>
        @if($goals->isNotEmpty())
        <div class="personal-goal-list">
            @foreach($goals as $goal)
            @php($percent = min(100, (int) round(($goal->progress_value / max(1,$goal->target_value))*100)))
            <article>
                <div><strong>{{ $goal->title }}</strong><small>{{ $goal->progress_value }} / {{ $goal->target_value }} {{ $metricLabels[$goal->metric] ?? $goal->metric }} @if($goal->due_on) · sampai {{ $goal->due_on->translatedFormat('d M Y') }} @endif</small></div>
                <div class="personal-progress"><span style="width:{{ $percent }}%"></span></div><b>{{ $percent }}%</b>
                <form method="post" action="{{ route('personal.goals.complete',$goal) }}">@csrf @method('put')<button class="button secondary small" type="submit">Tandai selesai</button></form>
            </article>
            @endforeach
        </div>
        @else
        <p class="muted">Belum ada target terukur. Mulai dari target kecil yang bisa dijaga.</p>
        @endif
        <details class="personal-disclosure">
            <summary>+ Tambah target baru</summary>
            <form method="post" action="{{ route('personal.goals.store') }}" class="personal-form-grid compact">@csrf
                <label class="span-all">Nama target<input name="title" maxlength="190" required placeholder="Contoh: Murāja‘ah 300 ayat pekan ini"></label>
                <label>Ukuran<select name="metric" required><option value="murajaah_verses">Ayat murāja‘ah</option><option value="memorization_verses">Ayat hafalan baru</option><option value="practice_minutes">Menit latihan</option><option value="active_days">Hari aktif</option><option value="sessions">Jumlah sesi</option></select></label>
                <label>Nilai target<input type="number" min="1" max="100000" name="target_value" required></label>
                <label>Mulai<input type="date" name="starts_on" value="{{ today()->toDateString() }}" required></label>
                <label>Selesai <span class="muted">opsional</span><input type="date" name="due_on"></label>
                <div class="span-all"><button class="button primary" type="submit">Tambahkan target</button></div>
            </form>
        </details>
    </section>

    <section class="card" id="jurnal">
        <div class="section-head"><div><span class="eyebrow">JURNAL</span><h2>Jejak terbaru</h2></div><span class="personal-week-badge">{{ $week_memorization_verses }} ayat hafalan · 7 hari</span></div>
        @if($entries->isEmpty())<div class="empty-state"><p>Belum ada aktivitas. Catatan pertama adalah awal pola yang bisa Anda baca nanti.</p></div>@else
        <div class="personal-journal-list">
            @foreach($entries as $entry)
            <article><time>{{ $entry->practiced_on->translatedFormat('d M') }}</time><div><strong>{{ $activityLabels[$entry->activity_type] ?? $entry->activity_type }}</strong><span>@if($entry->surah){{ $entry->surah->name_latin }}@if($entry->start_verse) · {{ $entry->start_verse }}–{{ $entry->end_verse }}@endif · @endif{{ $entry->duration_minutes }} menit</span>@if($entry->notes)<p>{{ $entry->notes }}</p>@endif</div><small>{{ $ratingLabels[$entry->self_rating] ?? '' }}</small></article>
            @endforeach
        </div>
        @endif
    </section>

    <p class="personal-privacy-note">🔒 Catatan pada Ruang Personal dipisahkan per akun. Pengguna Personal lain tidak dapat mengakses jurnal, target, atau progres Anda.</p>
</div>
@endsection
