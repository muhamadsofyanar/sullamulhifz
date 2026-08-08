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
        <label>Pola pelaksanaan<select name="cadence_mode"><option value="flexible" selected>Fleksibel</option><option value="daily">Harian</option><option value="weekly">Mingguan</option><option value="custom">Khusus</option></select><small>Default fleksibel. Porsi Marhalah adalah standar satu sesi, bukan kewajiban harian.</small></label>
        <label>Arahan guru tahap awal<textarea name="cadence_notes" rows="2" placeholder="Contoh: setoran Senin dan Kamis; satu porsi tetap mengikuti Marhalah"></textarea></label>
        <label>Alasan posisi awal<textarea name="reason" rows="3" placeholder="Catatan guru saat memulai penggunaan Qur’an Journey"></textarea></label>
        <button class="button primary">Mulai Qur’an Journey</button>
    </form>
</section>
@else
<div class="stats-grid four">
    <div class="stat-card"><span>Juz aktif</span><strong>{{ $profile->current_juz_number }}</strong></div>
    <div class="stat-card"><span>Marhalah</span><strong>{{ $profile->marhalah?->name }}</strong></div>
    <div class="stat-card"><span>Porsi sesi</span><strong>{{ $rule['portion'] }}</strong></div>
    <div class="stat-card"><span>Hafalan selesai</span><strong>{{ $summary['completedJuz'] }}/30</strong><small>Terjaga {{ $summary['maintainedJuz'] }}/30</small></div>
</div>

<section class="card">
    <div class="section-head"><div><span class="eyebrow">MARHALAH AKTIF</span><h2>Juz {{ $profile->current_juz_number }} · {{ $profile->marhalah?->name }}</h2><p class="hint">{{ $profile->marhalah?->description }}</p></div><span class="badge">{{ $rule['portion'] }} / sesi</span></div>
    @php
        $cadenceLabels = ['flexible'=>'Fleksibel','daily'=>'Harian','weekly'=>'Mingguan','custom'=>'Khusus'];
        $completedStageHistories = $stageHistories->where('status','completed');
    @endphp
    <div class="grid two" style="margin-top:14px">
        <div class="item-card static">
            <div>
                <strong>Arahan tahap aktif</strong>
                <small>Juz {{ $profile->current_juz_number }} · {{ $profile->marhalah?->name }} · porsi {{ $rule['portion'] }}</small>
                <p><strong>Pola pelaksanaan:</strong> {{ $cadenceLabels[$profile->cadence_mode] ?? 'Fleksibel' }}</p>
                <p>{{ $profile->cadence_notes ?: 'Belum ada arahan khusus dari guru untuk tahap ini.' }}</p>
            </div>
        </div>
        <form class="stack compact" method="post" action="{{ route('teacher.quran-journey.cadence.update',$student) }}">
            @csrf @method('PUT')
            <label>Pola pelaksanaan
                <select name="cadence_mode">
                    <option value="flexible" @selected($profile->cadence_mode==='flexible')>Fleksibel</option>
                    <option value="daily" @selected($profile->cadence_mode==='daily')>Harian</option>
                    <option value="weekly" @selected($profile->cadence_mode==='weekly')>Mingguan</option>
                    <option value="custom" @selected($profile->cadence_mode==='custom')>Khusus</option>
                </select>
                <small>Porsi {{ $rule['portion'] }} tidak otomatis berarti harus disetor setiap hari.</small>
            </label>
            <label>Arahan guru tahap aktif<textarea name="cadence_notes" rows="2" placeholder="Contoh: Senin dan Kamis, satu porsi 3 baris per setoran">{{ $profile->cadence_notes }}</textarea></label>
            <button class="button secondary">Simpan arahan tahap ini</button>
        </form>
    </div>

    @if($completedStageHistories->isNotEmpty())
    <details style="margin-top:14px">
        <summary><strong>Riwayat arahan tahap sebelumnya</strong> · {{ $completedStageHistories->count() }} tahap</summary>
        <div class="cards-list" style="margin-top:10px">
            @foreach($completedStageHistories as $history)
            <div class="list-row"><div>
                <strong>{{ $history->journey_juz_number ? 'Juz '.$history->journey_juz_number.' · ' : '' }}{{ $history->marhalahType?->name }}</strong>
                <small>{{ $history->portion_label ?: 'Porsi mengikuti Marhalah saat itu' }} · {{ $cadenceLabels[$history->cadence_mode] ?? 'Fleksibel' }} · {{ $history->effective_from?->format('d M Y') }}@if($history->effective_until)–{{ $history->effective_until->format('d M Y') }}@endif</small>
                <p>{{ $history->cadence_notes ?: 'Tidak ada arahan khusus pada tahap ini.' }}</p>
            </div></div>
            @endforeach
        </div>
    </details>
    @endif
    @if(($rule['unit'] ?? null)==='line')
        <div class="alert info"><strong>Mushaf Line Engine aktif.</strong> Tsalātsiyyah/Khamsiyyah mengikuti slot fisik halaman Mushaf Madinah: blok 3/5 baris tetap. Nama surah atau basmalah yang menempati slot halaman tetap dihitung pada posisi fisiknya; batas hafalan disimpan sampai tingkat kata agar tidak dipaksa menjadi satu ayat penuh.</div>
    @elseif(($rule['unit'] ?? null)==='page')
        <div class="alert info">Porsi {{ $rule['portion'] }} mengikuti halaman Mushaf Madinah. Guru tetap memastikan titik awal/akhir sesuai halaman yang dipakai.</div>
    @endif
</section>

<section class="card">
    <div class="section-head"><div><span class="eyebrow">PORSI MARHALAH</span><h2>Rencanakan satu porsi hafalan baru</h2><p class="hint">Porsi mengikuti Juz aktif dan tidak berarti wajib setiap hari. Satu porsi tidak melewati batas Juz.</p></div><span class="badge">{{ $rule['portion'] }}</span></div>

    @if(($rule['unit'] ?? null)==='line')
        <div class="stats-grid two" style="margin-bottom:14px">
            <div class="stat-card"><span>Layout Mushaf</span><strong>{{ $mushafLineStatus['pages'] ?? 0 }}/604 halaman</strong><small>{{ ($mushafLineStatus['complete'] ?? false) ? 'Tersinkron penuh' : 'Sinkronisasi bertahap / on-demand' }}</small></div>
            <div class="stat-card"><span>Pola blok</span><strong>{{ (int)$rule['value'] === 3 ? '1–3 · 4–6 · 7–9 · 10–12 · 13–15' : '1–5 · 6–10 · 11–15' }}</strong><small>15 slot fisik per halaman</small></div>
        </div>

        @if($mushafPages->isNotEmpty())
        <form method="get" action="{{ route('teacher.quran-journey.student',$student) }}" class="inline-form" style="margin-bottom:14px">
            <label style="flex:1">Halaman pada Juz {{ $profile->current_juz_number }}
                <select name="mushaf_page" onchange="this.form.submit()">
                    @foreach($mushafPages as $page)<option value="{{ $page }}" @selected((int)$selectedMushafPage===(int)$page)>Halaman {{ $page }}</option>@endforeach
                </select>
            </label>
            <noscript><button class="button secondary">Tampilkan halaman</button></noscript>
        </form>
        @endif

        @if(empty($mushafLineBlocks))
            <div class="alert warning">Layout halaman ini belum tersedia. Sistem akan mencoba sinkronisasi halaman saat dibuka dan sinkronisasi 604 halaman berjalan di latar belakang setelah deploy.</div>
        @else
            <div class="cards-list">
            @foreach($mushafLineBlocks as $block)
                <div class="item-card static" style="display:block">
                    <div class="section-head" style="margin-bottom:8px">
                        <div><strong>Halaman {{ $block['page'] }} · Baris {{ $block['start_line'] }}–{{ $block['end_line'] }}</strong><small>{{ $block['ayah_line_count'] }} slot ayat @if($block['has_special_lines']) · ada header/basmalah pada blok fisik @endif</small></div>
                        @if($block['available'])<span class="badge">Siap dipilih</span>@elseif($block['crosses_juz'])<span class="badge">Batas Juz</span>@else<span class="badge">Belum tersedia</span>@endif
                    </div>
                    <div style="border:1px solid var(--border,#dde5df);border-radius:14px;overflow:hidden;margin:8px 0 12px">
                    @foreach($block['lines'] as $line)
                        <div style="display:grid;grid-template-columns:46px 1fr;gap:10px;padding:8px 12px;border-bottom:1px solid var(--border,#edf1ee);align-items:center">
                            <strong style="font-size:.82rem">{{ $line['line_number'] }}</strong>
                            @if($line['type']==='surah_name')
                                <div style="text-align:center"><strong>Nama Surah</strong> @if($line['text'])· {{ $line['text'] }}@endif</div>
                            @elseif($line['type']==='basmallah')
                                <div dir="rtl" style="text-align:center;font-size:1.35rem;line-height:1.8">{{ $line['text'] ?: '﷽' }}</div>
                            @elseif($line['type']==='ayah')
                                <div dir="rtl" style="text-align:right;font-size:1.25rem;line-height:1.9">{{ $line['text'] ?: 'Teks baris tersinkron; batas kata tersimpan.' }}</div>
                            @else
                                <div class="hint">Slot belum tersinkron.</div>
                            @endif
                        </div>
                    @endforeach
                    </div>
                    @if($block['available'])
                        <p class="hint">Batas presisi: {{ $block['first_word_location'] }} → {{ $block['last_word_location'] }}. Target Tahfizh tetap menampilkan rentang ayat untuk navigasi, sedangkan batas Mushaf disimpan sampai tingkat kata.</p>
                        <form class="stack compact" method="post" action="{{ route('teacher.quran-journey.line-portions.store',$student) }}">@csrf
                            <input type="hidden" name="page_number" value="{{ $block['page'] }}"><input type="hidden" name="start_line" value="{{ $block['start_line'] }}"><input type="hidden" name="block_size" value="{{ $block['slot_count'] }}">
                            <div class="form-grid"><label>Rencana mulai<input type="date" name="scheduled_for"></label><label>Batas fleksibel<input type="date" name="due_date"></label></div>
                            <label>Catatan<textarea name="notes" rows="2" placeholder="Opsional: arahan khusus untuk setoran porsi ini"></textarea></label>
                            <button class="button primary">Gunakan Baris {{ $block['start_line'] }}–{{ $block['end_line'] }} & buat target Tahfizh</button>
                        </form>
                    @elseif($block['crosses_juz'])
                        <div class="alert warning">Blok fisik ini menyentuh batas Juz. Sistem tidak membuat target otomatis; guru menyelesaikan batas Juz terlebih dahulu agar Marhalah tidak tercampur.</div>
                    @else
                        <div class="alert warning">Blok ini belum memiliki batas kata yang lengkap dan tidak dapat dipilih.</div>
                    @endif
                </div>
            @endforeach
            </div>
        @endif
    @else
        <form class="stack compact" method="post" action="{{ route('teacher.quran-journey.portions.store',$student) }}">@csrf
            <div class="form-grid">
                <label>Mulai surah<select name="start_surah_id" required><option value="">Pilih surah</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
                <label>Ayat mulai<input type="number" min="1" name="start_verse" required value="1"></label>
                <label>Sampai surah<select name="end_surah_id" required><option value="">Pilih surah</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
                <label>Ayat akhir<input type="number" min="1" name="end_verse" required value="1"></label>
            </div>
            <div class="form-grid"><label>Rencana mulai<input type="date" name="scheduled_for"></label><label>Batas fleksibel<input type="date" name="due_date"></label></div>
            <label>Catatan<textarea name="notes" rows="2" placeholder="Contoh: setoran pekan ini; boleh selesai dalam beberapa pertemuan"></textarea></label>
            <label class="checkbox-row"><input type="checkbox" name="teacher_confirmed" value="1" required><span>Saya sudah memastikan rentang ini sesuai porsi <strong>{{ $profile->marhalah?->name }} — {{ $rule['portion'] }}</strong> pada Mushaf Madinah.</span></label>
            <button class="button primary">Buat porsi & target setoran</button>
        </form>
    @endif

    @if($portions->isNotEmpty())
    <div class="cards-list" style="margin-top:14px">
        @foreach($portions as $portion)
        <div class="item-card static"><div><strong>{{ $portion->marhalah?->name }} · {{ $portion->portion_label }}</strong><small>Juz {{ $portion->journey_juz_number }} · {{ $portion->startSurah?->name_latin }} {{ $portion->start_verse }} → {{ $portion->endSurah?->name_latin }} {{ $portion->end_verse }} · {{ ucfirst(str_replace('_',' ',$portion->status)) }}</small><p>{{ $portion->targets->count() }} target setoran terhubung @if($portion->start_page_number) · halaman {{ $portion->start_page_number }}@if($portion->end_page_number && $portion->end_page_number!==$portion->start_page_number)–{{ $portion->end_page_number }}@endif @endif @if($portion->start_line_number) · baris {{ $portion->start_line_number }}–{{ $portion->end_line_number }} @endif</p>@if($portion->start_word_location)<small>Batas kata: {{ $portion->start_word_location }} → {{ $portion->end_word_location }}</small>@endif</div></div>
        @endforeach
    </div>
    @endif
</section>

<section class="card">
    <div class="section-head"><div><h2>Milestone Juz {{ $profile->current_juz_number }}</h2><p class="hint">“Selesai hafalan” dan “terjaga” adalah dua hal berbeda.</p></div></div>
    @php($current = $summary['currentMilestone'])
    <div class="list-row"><div><strong>{{ $current?->label ?? 'Juz '.$profile->current_juz_number }}</strong><small>Hafalan: {{ $statusMem[$current?->memorization_status ?? 'in_progress'] ?? '-' }} · Penjagaan: {{ $statusRetention[$current?->retention_status ?? 'not_assessed'] ?? '-' }}</small><p>{{ $current?->notes }}</p></div></div>
    <form class="inline-form" method="post" action="{{ route('teacher.quran-journey.milestones.current-juz',$student) }}">@csrf
        <select name="memorization_status"><option value="in_progress" @selected(($current?->memorization_status ?? 'in_progress')==='in_progress')>Masih berjalan</option><option value="completed" @selected(($current?->memorization_status ?? null)==='completed')>Selesai hafalan Juz ini</option></select>
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
