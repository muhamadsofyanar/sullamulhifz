{{-- @phase 4.5 Personal 2.0 — contextual Personal home --}}
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
    <section class="personal-v4-hero">
        <div>
            <span class="personal-kicker">SATU RUANG QUR’AN · PRIVAT</span>
            <h1>Assalamu‘alaikum, {{ auth()->user()->name }}</h1>
            <p>@if($profile->aspiration)Cita-cita Anda menjadi <strong>{{ $profile->aspiration }}</strong>. @endif Hari ini tidak harus sempurna. Pilih satu langkah Qur’ani yang paling mungkin dijaga.</p>
            @if($profile->aspiration || $profile->quranic_purpose || $profile->learning_mode)
            <div class="personal-identity-chips">
                @if($profile->aspiration)<span>Cita-cita · {{ $profile->aspiration }}</span>@endif
                @if($profile->learning_mode)<span>Jalur · {{ $learningModes[$profile->learning_mode]['label'] ?? 'Personal' }}</span>@endif
                @if($profile->quranic_purpose)<span>Tujuan Qur’ani · {{ str($profile->quranic_purpose)->limit(80) }}</span>@endif
            </div>
            @endif
            <div class="v4-hero-actions">
                @if($primaryModule)<a class="button primary" href="{{ route($primaryModule['route']) }}">Lanjutkan {{ $primaryModule['title'] }}</a>@else<a class="button primary" href="{{ route('personal.programs.index') }}">Pilih program pertama</a>@endif
                <a class="button secondary" href="{{ route('personal.journey.index') }}">Lihat Perjalanan Saya</a>
            </div>
        </div>
        <div class="v4-hero-status">
            <article><strong>{{ $streak }}</strong><span>hari kesinambungan</span></article>
            <article><strong>{{ $activeModules->count() }}</strong><span>program aktif</span></article>
            <article><strong>{{ $unreadNotifications }}</strong><span>informasi baru</span></article>
        </div>
    </section>

    <section class="v4-today-strip">
        <div><span class="eyebrow">KEADAAN HARI INI</span>@if($todayCheckIn)<strong>{{ ['low'=>'Ritme ringan','steady'=>'Cukup stabil','strong'=>'Siap bertumbuh'][$todayCheckIn->energy] ?? $todayCheckIn->energy }}</strong><small>{{ $todayCheckIn->intention ?: 'Check-in hari ini sudah tersimpan.' }}</small>@else<strong>Belum check-in</strong><small>Kenali keadaan sebelum menentukan beban.</small>@endif</div>
        <a class="button secondary small" href="{{ route('personal.journey.index') }}">{{ $todayCheckIn ? 'Perbarui refleksi' : 'Check-in sekarang' }}</a>
    </section>

    @if(in_array($profile->age_group, ['child','teen'], true))
    <section class="personal-safeguarding-banner"><strong>Ruang aman untuk pengguna di bawah 18 tahun</strong><span>Profil, jurnal, dan portofolio tetap privat. Orang tua/wali mendampingi penggunaan, dan fitur komunitas tidak otomatis aktif.</span><a href="{{ route('relationships.index') }}">Kelola hubungan pendamping →</a></section>
    @endif

    <section class="card personal-onboarding" id="arah-saya">
        <div class="section-head"><div><span class="eyebrow">{{ $profile->onboarding_completed_at ? 'ARAH SAYA' : 'LANGKAH PERTAMA' }}</span><h2>Setiap orang, setiap cita</h2><p class="muted">Profil ini memberi konteks pada perjalanan Qur’an Anda. Ini bukan tes kemampuan, kelas profesi, atau dasar ranking.</p></div></div>
        @if($profile->onboarding_completed_at)
        <div class="personal-identity-summary">
            <article><span>Kelompok usia</span><strong>{{ $ageGroups[$profile->age_group] ?? 'Belum dipilih' }}</strong></article>
            <article><span>Cita-cita</span><strong>{{ $profile->aspiration ?: 'Masih terbuka' }}</strong></article>
            <article><span>Jalur belajar</span><strong>{{ $learningModes[$profile->learning_mode]['label'] ?? 'Mandiri' }}</strong></article>
            <article class="wide"><span>Tujuan Qur’ani</span><strong>{{ $profile->quranic_purpose ?: 'Belum ditulis' }}</strong></article>
        </div>
        @endif
        <details class="personal-disclosure identity-disclosure" @if(!$profile->onboarding_completed_at) open @endif>
        <summary>{{ $profile->onboarding_completed_at ? 'Perbarui arah perjalanan' : 'Lengkapi arah perjalanan' }}</summary>
        <form method="post" action="{{ route('personal.onboarding') }}" class="personal-form-grid personal-identity-form">
            @csrf @method('put')
            <label>Kelompok usia<select name="age_group" required><option value="">Pilih</option>@foreach($ageGroups as $value => $label)<option value="{{ $value }}" @selected(old('age_group', $profile->age_group) === $value)>{{ $label }}</option>@endforeach</select><small>Tidak memerlukan tanggal lahir.</small></label>
            <label>Cita-cita atau peran<input name="aspiration" value="{{ old('aspiration', $profile->aspiration) }}" maxlength="150" placeholder="Contoh: dokter, guru, pilot, ahli tanaman"></label>
            <label class="span-all">Tujuan Qur’ani<textarea name="quranic_purpose" rows="3" maxlength="500" required placeholder="Nilai apa yang ingin dijaga melalui Al-Qur’an dalam kehidupan dan cita-cita Anda?">{{ old('quranic_purpose', $profile->quranic_purpose) }}</textarea></label>
            <label class="span-all">Jalur pendampingan<select name="learning_mode" required>@foreach($learningModes as $value => $mode)<option value="{{ $value }}" @selected(old('learning_mode', $profile->learning_mode ?: 'self') === $value)>{{ $mode['label'] }} — {{ $mode['description'] }}</option>@endforeach</select></label>
            <fieldset class="span-all personal-interest-field"><legend>Minat yang ingin dikembangkan <span class="muted">maksimal lima</span></legend><div class="personal-interest-choice">@foreach($interestOptions as $value => $label)<label><input type="checkbox" name="interests[]" value="{{ $value }}" @checked(in_array($value, (array) old('interests', $profile->interests ?? []), true))><span>{{ $label }}</span></label>@endforeach</div></fieldset>
            <label>Posisi perjalanan saat ini<select name="experience_level" required><option value="starting" @selected(old('experience_level',$profile->experience_level) === 'starting')>Baru memulai</option><option value="restarting" @selected(old('experience_level',$profile->experience_level) === 'restarting')>Mulai kembali setelah jeda</option><option value="active" @selected(old('experience_level',$profile->experience_level) === 'active')>Sedang aktif menghafal</option><option value="maintaining" @selected(old('experience_level',$profile->experience_level) === 'maintaining')>Fokus menjaga hafalan</option></select></label>
            <label>Fokus utama<select name="primary_focus" required><option value="balanced" @selected(old('primary_focus',$profile->primary_focus ?: 'balanced') === 'balanced')>Seimbang: hafalan + murāja‘ah</option><option value="memorization" @selected(old('primary_focus',$profile->primary_focus) === 'memorization')>Menambah hafalan</option><option value="murajaah" @selected(old('primary_focus',$profile->primary_focus) === 'murajaah')>Menguatkan murāja‘ah</option></select></label>
            <label>Ritme harian<input type="number" min="5" max="180" name="daily_minutes" value="{{ old('daily_minutes',$profile->daily_minutes ?: 20) }}" required><small>menit/hari</small></label>
            <label>Target juz <span class="muted">opsional</span><input type="number" min="1" max="30" name="target_juz" value="{{ old('target_juz',$profile->target_juz) }}" placeholder="1–30"></label>
            <label>Target surah <span class="muted">opsional</span><select name="target_surah_id"><option value="">Belum ditentukan</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}" @selected((string) old('target_surah_id',$profile->target_surah_id) === (string) $surah->id)>{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
            <label>Tanggal target <span class="muted">opsional</span><input type="date" name="target_date" value="{{ old('target_date',$profile->target_date?->toDateString()) }}" min="{{ now()->addDay()->toDateString() }}"></label>
            <label class="span-all personal-guardian-ack"><input type="checkbox" name="guardian_acknowledgement" value="1" @checked(old('guardian_acknowledgement', (bool) $profile->safeguarding_acknowledged_at))> <span>Untuk pengguna di bawah 18 tahun, orang tua/wali mengetahui dan mendampingi penggunaan ruang ini. Profil tetap privat dan Community tidak otomatis dibuka.</span></label>
            <div class="span-all"><button class="button primary" type="submit">Simpan arah perjalanan</button></div>
        </form>
        </details>
    </section>

    <section class="personal-stat-grid" aria-label="Ringkasan tujuh hari">
        <article><span>Hari ini</span><strong>{{ $today_minutes }}</strong><small>menit · {{ $today_sessions }} sesi</small></article>
        <article><span>7 hari</span><strong>{{ $week_minutes }}</strong><small>menit latihan</small></article>
        <article><span>Konsistensi</span><strong>{{ $week_active_days }}/7</strong><small>hari aktif</small></article>
        <article><span>Murāja‘ah</span><strong>{{ $week_murajaah_verses }}</strong><small>ayat tercatat</small></article>
    </section>

    <section class="card" id="program-saya">
        <div class="section-head"><div><span class="eyebrow">PROGRAM SAYA</span><h2>Yang aktif untuk Anda</h2><p class="muted">Beranda hanya menampilkan program yang memang sudah Anda ikuti.</p></div><a class="button secondary small" href="{{ route('personal.programs.index') }}">Kelola program</a></div>
        @if($activeModules->isEmpty())
            <div class="personal-program-empty"><h3>Belum ada program aktif</h3><p class="muted">Ruang Personal Anda tetap bisa dipakai untuk jurnal dan target. Tambahkan Latihan Qur’an, Qur’an Journey, atau program pendampingan saat Anda membutuhkannya.</p><a class="button primary" href="{{ route('personal.programs.index') }}">Pilih program</a></div>
        @else
            <div class="personal-home-programs">
                @foreach($activeModules as $module)
                <a class="personal-home-program" href="{{ route($module['route']) }}">
                    <span class="personal-program-icon"><x-icon :name="$module['icon']" size="24"/></span>
                    <span class="program-copy"><strong>{{ $module['title'] }}</strong><span>{{ $module['description'] }}</span></span>
                    <b>→</b>
                </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="personal-grid-two">
        <div class="card personal-guidance">
            <div class="section-head"><div><span class="eyebrow">RENCANA HARI INI</span><h2>Apa yang perlu dijaga?</h2></div></div>
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
