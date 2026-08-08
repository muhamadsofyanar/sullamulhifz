@extends('layouts.app',['pageTitle'=>'Qur’an Journey'])
@section('content')
@php
$profile = $summary['profile'];
$rule = $summary['rule'];
$statusMem = ['not_started'=>'Belum dimulai','in_progress'=>'Berjalan','completed'=>'Selesai hafalan'];
$statusRetention = ['not_assessed'=>'Belum dinilai','strengthening'=>'Penguatan','maintained'=>'Terjaga','reactivation'=>'Perlu dipanggil kembali'];
$purposeLabel = ['tilawah'=>'Tilawah','murajaah'=>'Murāja‘ah','both'=>'Tilawah + Murāja‘ah'];
@endphp
<div class="page-head">
    <div><span class="eyebrow">QUR’AN JOURNEY SANTRI</span><h1>{{ $student->full_name }}</h1><p>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Kelompok belajar' }} · jejak perjalanan, bukan perbandingan.</p></div>
    <div class="actions"><a class="button secondary" href="{{ route('teacher.tahfizh.student',$student) }}">Perjalanan Tahfizh</a><a class="button ghost" href="{{ route('teacher.quran-journey.index') }}">Semua Santri</a></div>
</div>

@if(!$profile)
<section class="card">
    <span class="eyebrow">INISIALISASI SEKALI</span><h2>Tetapkan posisi nyata santri</h2>
    <p class="hint">Pilih Juz yang sedang menjadi wilayah hafalan baru santri. Marhalah akan ditentukan otomatis dari Juz; guru tidak memilih level secara bebas.</p>
    <form class="stack compact" method="post" action="{{ route('teacher.quran-journey.initialize',$student) }}">@csrf
        <label>Juz perjalanan saat ini<select name="current_juz_number" required>
            <option value="30">Juz 30 — Āyah · ≥ 1 ayat</option><option value="29">Juz 29 — Tsalātsiyyah · 3 baris</option><option value="28">Juz 28 — Khamsiyyah · 5 baris</option><option value="27">Juz 27 — Niṣfiyyah · ½ halaman</option><option value="26">Juz 26 — Ṣafḥah · 1 halaman</option>
            @for($j=1;$j<=25;$j++)<option value="{{ $j }}">Juz {{ $j }} — Ṣafḥatayn · 2 halaman</option>@endfor
        </select></label>
        <label>Ritme program<select name="cadence_mode"><option value="flexible">Fleksibel</option><option value="daily">Harian</option><option value="weekly">Mingguan</option><option value="custom">Khusus</option></select></label>
        <label>Catatan ritme<textarea name="cadence_notes" rows="2" placeholder="Contoh: setoran Senin dan Kamis; porsi tetap mengikuti Marhalah"></textarea></label>
        <label>Alasan posisi awal<textarea name="reason" rows="3" placeholder="Catatan guru saat memulai penggunaan Qur’an Journey"></textarea></label>
        <button class="button primary">Mulai Qur’an Journey</button>
    </form>
</section>
@else
<div class="stats-grid four">
    <div class="stat-card"><span>Juz aktif</span><strong>{{ $profile->current_juz_number }}</strong></div>
    <div class="stat-card"><span>Marhalah</span><strong>{{ $profile->marhalah?->name }}</strong></div>
    <div class="stat-card"><span>Porsi sesi</span><strong>{{ $rule['portion'] }}</strong></div>
    <div class="stat-card"><span>Juz selesai</span><strong>{{ $summary['completedJuz'] }}/30</strong></div>
</div>

<section class="card">
    <div class="section-head"><div><span class="eyebrow">MARHALAH AKTIF</span><h2>Juz {{ $profile->current_juz_number }} · {{ $profile->marhalah?->name }}</h2><p class="hint">{{ $profile->marhalah?->description }}</p></div><span class="badge">{{ $rule['portion'] }} / sesi</span></div>
    <p><strong>Ritme:</strong> {{ ['flexible'=>'Fleksibel','daily'=>'Harian','weekly'=>'Mingguan','custom'=>'Khusus'][$profile->cadence_mode] ?? $profile->cadence_mode }}. Porsi ini tidak berarti santri wajib setor setiap hari.</p>
    @if($profile->cadence_notes)<p>{{ $profile->cadence_notes }}</p>@endif
    @if(in_array($rule['unit'],['line','page']))<div class="alert info">Untuk porsi {{ $rule['portion'] }}, guru memastikan rentang pada Mushaf Madinah. Metadata korpus saat ini mengetahui halaman tetapi belum memetakan posisi baris setiap ayat secara otomatis.</div>@endif
</section>

<section class="card">
    <div class="section-head"><div><span class="eyebrow">PORSI MARHALAH</span><h2>Rencanakan satu porsi hafalan baru</h2><p class="hint">Porsi mengikuti Juz aktif dan tidak berarti wajib setiap hari. Rentang boleh melewati pergantian surah selama tetap berada dalam Juz yang sama.</p></div><span class="badge">{{ $rule['portion'] }}</span></div>
    <form class="stack compact" method="post" action="{{ route('teacher.quran-journey.portions.store',$student) }}">@csrf
        <div class="form-grid">
            <label>Mulai surah<select name="start_surah_id" required><option value="">Pilih surah</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
            <label>Ayat mulai<input type="number" min="1" name="start_verse" required value="1"></label>
            <label>Sampai surah<select name="end_surah_id" required><option value="">Pilih surah</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
            <label>Ayat akhir<input type="number" min="1" name="end_verse" required value="1"></label>
        </div>
        <div class="form-grid"><label>Rencana mulai<input type="date" name="scheduled_for"></label><label>Batas fleksibel<input type="date" name="due_date"></label></div>
        <label>Catatan<textarea name="notes" rows="2" placeholder="Contoh: setoran pekan ini; boleh selesai dalam beberapa pertemuan"></textarea></label>
        <label class="checkbox-row"><input type="checkbox" name="teacher_confirmed" value="1" required><span>Saya sudah memastikan rentang ini sesuai porsi <strong>{{ $profile->marhalah?->name }} — {{ $rule['portion'] }}</strong> pada Mushaf Madinah. Untuk 3/5 baris, sistem tidak menebak posisi baris.</span></label>
        <button class="button primary">Buat porsi & target setoran</button>
    </form>
    @if($portions->isNotEmpty())
    <div class="cards-list" style="margin-top:14px">
        @foreach($portions as $portion)
        <div class="item-card static"><div><strong>{{ $portion->marhalah?->name }} · {{ $portion->portion_label }}</strong><small>Juz {{ $portion->journey_juz_number }} · {{ $portion->startSurah?->name_latin }} {{ $portion->start_verse }} → {{ $portion->endSurah?->name_latin }} {{ $portion->end_verse }} · {{ ucfirst(str_replace('_',' ',$portion->status)) }}</small><p>{{ $portion->targets->count() }} target setoran terhubung @if($portion->start_page_number) · halaman {{ $portion->start_page_number }}@if($portion->end_page_number && $portion->end_page_number!==$portion->start_page_number)–{{ $portion->end_page_number }}@endif @endif</p></div></div>
        @endforeach
    </div>
    @endif
</section>

<section class="card">
    <div class="section-head"><div><h2>Milestone Juz {{ $profile->current_juz_number }}</h2><p class="hint">“Selesai hafalan” dan “terjaga” adalah dua hal berbeda.</p></div></div>
    @php($current = $summary['currentMilestone'])
    <div class="list-row"><div><strong>{{ $current?->label ?? 'Juz '.$profile->current_juz_number }}</strong><small>Hafalan: {{ $statusMem[$current?->memorization_status ?? 'in_progress'] ?? '-' }} · Penjagaan: {{ $statusRetention[$current?->retention_status ?? 'not_assessed'] ?? '-' }}</small><p>{{ $current?->notes }}</p></div></div>
    <form class="inline-form" method="post" action="{{ route('teacher.quran-journey.milestones.current-juz',$student) }}">@csrf
        <select name="memorization_status"><option value="in_progress">Masih berjalan</option><option value="completed">Selesai hafalan Juz ini</option></select>
        <input name="notes" placeholder="Bukti/pertimbangan guru">
        <button class="button secondary">Simpan milestone</button>
    </form>
    @if(($current?->memorization_status ?? null)==='completed')
        @if($summary['nextJuz'])
        <form method="post" action="{{ route('teacher.quran-journey.advance',$student) }}" style="margin-top:12px">@csrf<button class="button primary">Lanjut ke Juz {{ $summary['nextJuz'] }} → Marhalah otomatis</button></form>
        @else
        <p class="alert success">Seluruh jalur Juz telah selesai hafalan. Penjagaan 30 juz tetap memiliki histori tersendiri.</p>
        @endif
    @endif
</section>
@endif

@if($profile)
<div class="grid two">
<section class="card">
    <h2>Catat milestone lain</h2><p class="hint">Surah, Rubu‘ al-Ḥizb, Ḥizb, Juz, atau manzil Fami dapat menjadi penanda nyata—bukan badge kompetisi.</p>
    <form class="stack compact" method="post" action="{{ route('teacher.quran-journey.milestones.store',$student) }}">@csrf
        <label>Jenis<select name="unit_type"><option value="surah">Surah</option><option value="rubu">Rubu‘ al-Ḥizb</option><option value="hizb">Ḥizb</option><option value="juz">Juz</option><option value="fami_manzil">Manzil Fami</option></select></label>
        <label>Nomor/kunci<input name="unit_key" required placeholder="Surah: 101 · Rubu‘: 1–240 · Ḥizb: 1–60 · Juz: 1–30 · Manzil: 1–7"><small>Nama diambil otomatis dari Peta Mushaf agar istilah konsisten.</small></label><input type="hidden" name="label" value="">
        <label>Status hafalan<select name="memorization_status"><option value="in_progress">Berjalan</option><option value="completed">Selesai hafalan</option></select></label>
        <label>Catatan<textarea name="notes" rows="2"></textarea></label><button class="button secondary">Simpan milestone</button>
    </form>
</section>
<section class="card">
    <h2>Fondasi 5 Juz</h2><p class="hint">Juz 30 → 29 → 28 → 27 → 26 adalah tahap fondasi. Di dalamnya tercakup wilayah Qāf–An-Nās, yaitu manzil Qaf pada Fami Bisyauqin.</p>
    <div class="stats-grid two"><div class="stat-card"><span>Fondasi 5 Juz</span><strong>{{ $summary['foundationCompleted'] ? 'Selesai hafalan' : 'Berjalan' }}</strong></div><div class="stat-card"><span>Manzil Qaf</span><strong>{{ $summary['qafMilestone'] ? ($statusRetention[$summary['qafMilestone']->retention_status] ?? 'Belum dinilai') : 'Belum tercapai' }}</strong></div></div>
    <p class="hint">Juz 26–30 tidak ditulis sebagai batas yang identik dengan al-Mufaṣṣal. Sistem hanya menjelaskan bahwa tahap fondasi berfokus pada bagian akhir Al-Qur’an dan mencakup Qāf–An-Nās.</p>
</section>
</div>

<section class="card">
    <div class="section-head"><div><h2>Milestone & pemeriksaan penjagaan</h2><p class="hint">Satu milestone dapat selesai hafalan tetapi masih membutuhkan penguatan.</p></div><span>{{ $milestones->count() }} milestone</span></div>
    @forelse($milestones as $milestone)
    <div class="list-row"><div><strong>{{ $milestone->label }}</strong><small>{{ ucfirst(str_replace('_',' ',$milestone->unit_type)) }} · Hafalan: {{ $statusMem[$milestone->memorization_status] ?? $milestone->memorization_status }} · Penjagaan: {{ $statusRetention[$milestone->retention_status] ?? $milestone->retention_status }}</small><p>{{ $milestone->notes }}</p></div></div>
    @if($milestone->memorization_status==='completed')
    <form class="inline-form" method="post" action="{{ route('teacher.quran-journey.retention.store',[$student,$milestone]) }}">@csrf
        <select name="result"><option value="maintained">Terjaga</option><option value="strengthening_needed">Perlu penguatan</option><option value="reactivation_needed">Perlu dipanggil kembali</option></select>
        <select name="assistance_level"><option value="none">Tanpa bantuan</option><option value="little">Sedikit bantuan</option><option value="several">Beberapa bantuan</option><option value="much">Banyak bantuan</option></select>
        <input type="date" name="next_check_date"><input name="notes" placeholder="Catatan pemeriksaan"><button class="button small secondary">Catat penjagaan</button>
    </form>
    @endif
    @empty<p class="empty">Belum ada milestone.</p>@endforelse
</section>

<section class="card">
    <div class="section-head"><div><span class="eyebrow">PROGRAM QUR’AN</span><h2>Khatam 30 Hari & Fami Bisyauqin</h2><p class="hint">Dapat diarahkan untuk tilawah, Murāja‘ah, atau keduanya. Jadwal tidak digunakan untuk memberi label gagal.</p></div></div>
    <form class="stack compact" method="post" action="{{ route('teacher.quran-journey.programs.store',$student) }}">@csrf
        <label>Program<select name="quran_program_template_id">@foreach($templates as $template)<option value="{{ $template->id }}">{{ $template->name }}</option>@endforeach</select></label>
        <div class="form-grid"><label>Tujuan<select name="purpose"><option value="tilawah">Tilawah</option><option value="murajaah">Murāja‘ah</option><option value="both">Tilawah + Murāja‘ah</option></select></label><label>Jadwal<select name="schedule_mode"><option value="flexible">Fleksibel</option><option value="daily">Mengikuti hari program</option></select></label></div>
        <label>Mulai<input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}"></label><label>Catatan<textarea name="notes" rows="2"></textarea></label><button class="button primary">Mulai program untuk santri</button>
    </form>
    @foreach($enrollments as $enrollment)
        @php($done=$enrollment->progress->where('status','completed')->count())
        @php($total=$enrollment->progress->count())
        <div class="item-card static" style="margin-top:12px"><div><strong>{{ $enrollment->template?->name }}</strong><small>{{ $purposeLabel[$enrollment->purpose] ?? $enrollment->purpose }} · {{ $done }}/{{ $total }} bagian · {{ $enrollment->status }}</small></div><span>{{ $total ? round(($done/$total)*100) : 0 }}%</span></div>
        <div class="cards-list">
            @foreach($enrollment->progress->sortBy(fn($p)=>$p->step?->sequence) as $progress)
            <div class="list-row"><div><strong>{{ $progress->step?->mnemonic_letter ? $progress->step->mnemonic_letter.' · ' : '' }}{{ $progress->step?->label }}</strong><small>{{ ['pending'=>'Belum','in_progress'=>'Berjalan','completed'=>'Selesai'][$progress->status] ?? $progress->status }}</small></div>
            <form class="inline-form" method="post" action="{{ route('teacher.quran-journey.programs.step',[$student,$enrollment]) }}">@csrf @method('PUT')<input type="hidden" name="step_id" value="{{ $progress->step_id }}"><select name="status"><option value="pending" @selected($progress->status==='pending')>Belum</option><option value="in_progress" @selected($progress->status==='in_progress')>Berjalan</option><option value="completed" @selected($progress->status==='completed')>Selesai</option></select><button class="button small ghost">Simpan</button></form></div>
            @endforeach
        </div>
    @endforeach
</section>

<section class="card">
    <div class="section-head"><div><span class="eyebrow">PETA MUSHAF & WARISAN ULAMA</span><h2>Istilah yang dipakai dalam perjalanan</h2><p class="hint">Dikenalkan sambil dipakai agar literasi mushaf hidup dalam praktik.</p></div></div>
    <div class="grid two">@foreach($heritageTerms as $term)<div class="item-card static"><div><strong>{{ $term->name }} @if($term->arabic_name) · {{ $term->arabic_name }} @endif</strong><small>{{ $term->short_description }}</small><p>{{ $term->practical_use }}</p></div></div>@endforeach</div>
</section>
@endif
@endsection
