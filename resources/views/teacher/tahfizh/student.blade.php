{{-- @phase 6.0 Distraction-free Tahfizh and focus ladder --}}
@extends('layouts.app',['pageTitle'=>'Perjalanan Tahfizh'])
@section('content')
@php
$errorLabels=['makhraj'=>'Makhraj','tajwid'=>'Tajwid','mad'=>'Panjang-pendek','ghunnah'=>'Ghunnah','waqf_ibtida'=>'Waqaf & ibtida','fluency'=>'Kelancaran','hesitation'=>'Terhenti/ragu','omission'=>'Ayat/kata terlewat','substitution'=>'Pergantian lafaz','sequence'=>'Urutan','prompt_dependency'=>'Ketergantungan bantuan','other'=>'Lainnya'];
$activeTargets = $student->memorizationTargets->whereIn('status',['active','in_progress','strengthening','paused']);
$soleActiveTargetId = $activeTargets->count() === 1 ? $activeTargets->first()?->id : null;
@endphp
<div class="page-head">
    <div><span class="eyebrow">PERJALANAN INDIVIDUAL</span><h1>{{ $student->full_name }}</h1><p>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Kelompok belajar' }} · jejak belajar tanpa perbandingan dengan santri lain.</p></div>
    <div class="actions"><a class="button secondary" href="{{ route('teacher.tahfizh.index') }}">Kembali</a><a class="button primary" href="#catat-setoran-cepat">Catat Setoran</a><a class="button secondary" href="#catat-murajaah-cepat">Catat Murāja‘ah</a>@if(\App\Support\Feature::enabled('quran_audio', auth()->user()->institution_id, true))<a class="button ghost" href="{{ route('quran-practice.index') }}">Audio Qur’an</a>@endif</div>
</div>
<div class="stats-grid four">
    <div class="stat-card"><span>Target aktif</span><strong>{{ $summary['activeTargets'] }}</strong></div>
    <div class="stat-card"><span>Murāja‘ah jatuh tempo</span><strong>{{ $summary['dueReviews'] }}</strong></div>
    <div class="stat-card"><span>Fokus koreksi terbuka</span><strong>{{ $summary['openErrors'] }}</strong></div>
    <div class="stat-card"><span>Siklus aktif</span><strong>{{ $summary['activeCycles'] }}</strong></div>
</div>
@if($summary['nextReview'])
<section class="card phase-highlight"><span class="eyebrow">BERIKUTNYA</span><h2>Murāja‘ah {{ $summary['nextReview']->surah?->name_latin }} {{ $summary['nextReview']->start_verse }}–{{ $summary['nextReview']->end_verse }}</h2><p>{{ $summary['nextReview']->review_date?->format('d M Y') }} · {{ $summary['nextReview']->notes ?: 'Ikuti kondisi hafalan saat pertemuan.' }}</p></section>
@endif
<details class="card legacy-detailed-entry"><summary><b>Perencanaan siklus dan jadwal manual</b><span>Buka bila perlu menyiapkan siklus atau jadwal di luar alur harian.</span></summary>
<div class="grid two">
<section class="card">
<h2>Mulai / lanjutkan siklus belajar</h2>
<form class="stack compact" method="post" action="{{ route('teacher.tahfizh.cycles.store') }}">@csrf
<input type="hidden" name="student_id" value="{{ $student->id }}">
<label>Target terkait<select name="memorization_target_id"><option value="" @selected(!$soleActiveTargetId)>Tanpa target khusus</option>@foreach($activeTargets as $target)<option value="{{ $target->id }}" @selected($soleActiveTargetId===$target->id)>{{ $target->mushaf_page_number ? 'Hal. '.$target->mushaf_page_number.($target->mushaf_end_page_number && $target->mushaf_end_page_number !== $target->mushaf_page_number ? '–'.$target->mushaf_end_page_number : '').' · baris '.$target->mushaf_start_line.'–'.$target->mushaf_end_line.' · ' : '' }}{{ $target->surah?->name_latin }} {{ $target->start_verse }}–{{ $target->end_verse }}</option>@endforeach</select>@if($soleActiveTargetId)<small>Satu target aktif ditemukan, jadi diprioritaskan otomatis. “Tanpa target khusus” tetap tersedia untuk latihan bebas.</small>@endif</label>
<div class="form-grid"><label>Jenis siklus<select name="cycle_type"><option value="new_memorization">Hafalan baru</option><option value="initial_repetition">Pengulangan awal</option><option value="murajaah">Murāja‘ah</option><option value="talaqqi">Talaqqi</option><option value="tasmi">Tasmi‘</option><option value="exam">Ujian</option></select></label><label>Persiapan<select name="preparation_method"><option value="talaqqi">Talaqqi</option><option value="audio_repetition">Audio berulang</option><option value="reading_repetition">Membaca berulang</option><option value="writing">Menulis</option><option value="word_arrangement">Susun kata</option><option value="movement">Gerak</option><option value="teach_back">Ajarkan kembali</option><option value="mixed">Campuran</option><option value="custom">Khusus</option></select></label></div>
<label>Arahan guru<textarea name="teacher_guidance" rows="3"></textarea></label><label>Arahan keluarga<textarea name="guardian_guidance" rows="3"></textarea></label>
<button class="button primary">Siapkan siklus</button></form>
</section>
<section class="card">
<h2>Jadwalkan Murāja‘ah</h2><p class="hint">Guru menentukan tanggal berdasarkan kebutuhan nyata; tidak ada rumus interval wajib.</p>
<form class="stack compact" method="post" action="{{ route('teacher.tahfizh.reviews.store') }}">@csrf
<input type="hidden" name="student_id" value="{{ $student->id }}">
<label>Target terkait<select name="memorization_target_id"><option value="">Opsional</option>@foreach($student->memorizationTargets as $target)<option value="{{ $target->id }}">{{ $target->mushaf_page_number ? 'Hal. '.$target->mushaf_page_number.($target->mushaf_end_page_number && $target->mushaf_end_page_number !== $target->mushaf_page_number ? '–'.$target->mushaf_end_page_number : '').' · baris '.$target->mushaf_start_line.'–'.$target->mushaf_end_line.' · ' : '' }}{{ $target->surah?->name_latin }} {{ $target->start_verse }}–{{ $target->end_verse }}</option>@endforeach</select></label>
<label>Surah<select name="surah_id" required>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
<div class="form-grid"><label>Ayat awal<input type="number" min="1" name="start_verse" required></label><label>Ayat akhir<input type="number" min="1" name="end_verse" required></label></div>
<div class="form-grid"><label>Tanggal<input type="date" name="review_date" value="{{ now()->addDay()->format('Y-m-d') }}" required></label><label>Jenis<select name="review_type"><option value="scheduled">Terjadwal</option><option value="random_recall">Pemanggilan acak</option><option value="continuation">Sambung ayat</option><option value="tasmi">Tasmi‘</option><option value="home">Di rumah</option></select></label></div>
<label>Prioritas<select name="priority"><option value="normal">Penjagaan</option><option value="strengthen">Penguatan</option><option value="recall">Panggil kembali</option></select></label><label>Catatan<input name="notes"></label>
<button class="button primary">Simpan jadwal</button></form>
</section>
</div>
</details>
<section class="card phase-highlight" id="setoran-tanpa-distraksi">
    <div class="section-head"><div><span class="eyebrow">SETORAN TANPA DISTRAKSI</span><h2>Dengarkan dahulu, koreksi langsung, catat kesimpulannya.</h2><p class="hint">Saat anak membaca, jangan isi apa pun. Setelah selesai, pilih satu keputusan dan catatan singkat bila perlu.</p></div></div>
    @if($activeFocus)
        @php($focusLabels=['accuracy'=>'Ketepatan lafaz & urutan','fluency'=>'Kelancaran','independence'=>'Kemandirian','makhraj_tajwid'=>'Makhraj & tajwid dominan','retention'=>'Kekuatan setelah jeda'])
        <div class="focus-reminder"><b>Fokus {{ $student->nickname ?: $student->full_name }}:</b> {{ $focusLabels[$activeFocus->focus_key] ?? $activeFocus->focus_key }} @if($activeFocus->notes)<span>· {{ $activeFocus->notes }}</span>@endif</div>
    @endif
    <div class="grid two">
        <div class="card soft-card" id="catat-setoran-cepat"><h3>Hasil setoran</h3>
            @include('teacher.tahfizh.partials.quick-memorization-form',[
                'quickAction'=>route('teacher.tahfizh.quick-memorization.store',$student),
                'quickTargets'=>$activeTargets,
                'quickSurahs'=>$surahs,
                'quickAutoTargetId'=>$soleActiveTargetId,
            ])
        </div>
        <div class="card soft-card" id="catat-murajaah-cepat"><h3>Hasil Murāja‘ah</h3>
            @include('teacher.tahfizh.partials.quick-murajaah-form',[
                'quickAction'=>route('teacher.tahfizh.quick-murajaah.store',$student),
                'quickReviewPlans'=>$reviewPlans->where('status','scheduled'),
                'quickSurahs'=>$surahs,
            ])
        </div>
    </div>
</section>
<div class="grid two">
<section class="card">
    <div class="section-head"><div><h2>Tangga Fokus</h2><p class="hint">Satu fokus aktif per anak; tidak perlu dinilai pada setiap setoran.</p></div><span>{{ $activeFocus ? 'Aktif' : 'Belum dipilih' }}</span></div>
    <form class="stack compact" method="post" action="{{ route('teacher.tahfizh.focus.update',$student) }}">@csrf @method('PUT')
        <label>Fokus pembinaan<select name="focus_key" required>
            <option value="accuracy" @selected($activeFocus?->focus_key==='accuracy')>1 · Ketepatan lafaz dan urutan</option>
            <option value="fluency" @selected($activeFocus?->focus_key==='fluency')>2 · Kelancaran menyambung ayat</option>
            <option value="independence" @selected($activeFocus?->focus_key==='independence')>3 · Kemandirian tanpa bantuan</option>
            <option value="makhraj_tajwid" @selected($activeFocus?->focus_key==='makhraj_tajwid')>4 · Makhraj dan tajwid dominan</option>
            <option value="retention" @selected($activeFocus?->focus_key==='retention')>5 · Kekuatan setelah jeda/uji acak</option>
        </select></label>
        <label>Pengingat singkat <small>(opsional)</small><input name="notes" maxlength="1000" value="{{ $activeFocus?->notes }}" placeholder="Contoh: latihan sambung ayat tanpa pancingan"></label>
        <button class="button secondary">Tetapkan fokus</button>
    </form>
</section>
<details class="card"><summary><b>Asesmen berkala lima aspek</b><span>Gunakan saat awal, bulanan, tasmi‘, ujian, atau kenaikan tahap.</span></summary>
    <form class="stack compact" method="post" action="{{ route('teacher.tahfizh.assessments.store',$student) }}">@csrf
        <div class="form-grid"><label>Jenis<select name="assessment_type"><option value="monthly">Bulanan</option><option value="initial">Asesmen awal</option><option value="completion">Selesai surat/tahap</option><option value="tasmi">Tasmi‘</option><option value="exam">Ujian</option><option value="stagnation">Evaluasi hambatan</option></select></label><label>Tanggal<input type="date" name="assessed_on" value="{{ today()->toDateString() }}" required></label></div>
        @php($assessmentLevels=['strong'=>'Kuat','developing'=>'Berkembang','needs_support'=>'Perlu dukungan'])
        @foreach(['accuracy_status'=>'Ketepatan','fluency_status'=>'Kelancaran','independence_status'=>'Kemandirian','makhraj_tajwid_status'=>'Makhraj & tajwid','retention_status'=>'Daya tahan hafalan'] as $field=>$label)
            <label>{{ $label }}<select name="{{ $field }}" required>@foreach($assessmentLevels as $value=>$text)<option value="{{ $value }}">{{ $text }}</option>@endforeach</select></label>
        @endforeach
        <label>Saran fokus berikutnya<select name="recommended_focus"><option value="">Belum ditentukan</option><option value="accuracy">Ketepatan</option><option value="fluency">Kelancaran</option><option value="independence">Kemandirian</option><option value="makhraj_tajwid">Makhraj & tajwid</option><option value="retention">Daya tahan hafalan</option></select></label>
        <label>Ringkasan<textarea name="summary" rows="3" maxlength="3000"></textarea></label>
        <button class="button primary">Simpan asesmen berkala</button>
    </form>
</details>
</div>
<details class="card legacy-detailed-entry"><summary><b>Pencatatan rinci untuk tasmi‘, ujian, atau kasus khusus</b><span>Form lama tetap tersedia dan data lama tidak dihapus.</span></summary>
<section class="card">
<div class="section-head"><div><span class="eyebrow">ALUR TERPADU</span><h2>Catat hasil tanpa keluar dari perjalanan santri</h2><p class="hint">Gunakan halaman ini untuk pencatatan individual. Operasional Hari Ini tetap cocok untuk pencatatan satu kelas secara massal.</p></div></div>
<div class="grid two">
<div id="catat-setoran" class="card soft-card">
<h3>Catat Setoran Tahfizh</h3><p class="hint">Setoran akan langsung masuk ke riwayat, memperbarui siklus/target, membuat jadwal Murāja‘ah bila tanggal diisi, dan membuka fokus koreksi bila dipilih.</p>
<form class="stack compact" method="post" action="{{ route('teacher.tahfizh.memorization.store',$student) }}">@csrf
<label>Target terkait<select name="memorization_target_id" id="journey-target"><option value="">Cari otomatis dari surah dan ayat</option>@foreach($activeTargets as $target)<option value="{{ $target->id }}" @selected($soleActiveTargetId===$target->id) data-surah="{{ $target->surah_id }}" data-start="{{ $target->start_verse }}" data-end="{{ $target->end_verse }}" data-marhalah="{{ $target->marhalah_type_id }}">{{ $target->mushaf_page_number ? 'Hal. '.$target->mushaf_page_number.($target->mushaf_end_page_number && $target->mushaf_end_page_number !== $target->mushaf_page_number ? '–'.$target->mushaf_end_page_number : '').' · baris '.$target->mushaf_start_line.'–'.$target->mushaf_end_line.' · ' : '' }}{{ $target->surah?->name_latin }} {{ $target->start_verse }}–{{ $target->end_verse }} · {{ $target->marhalah?->name ?? 'Tanpa marhalah' }}</option>@endforeach</select></label>
<div class="form-grid"><label>Jenis<select name="record_type"><option value="new_memorization">Hafalan baru</option><option value="initial_repetition">Pengulangan awal</option><option value="home_submission">Setoran rumah</option><option value="class_submission">Setoran kelas</option><option value="tasmi">Tasmi‘</option><option value="exam">Ujian</option></select></label><label>Cara penyampaian<select name="delivery_mode"><option value="talaqqi">Talaqqi dengan guru</option><option value="individual_submission">Setoran individual</option><option value="group_tasmi">Tasmi‘ kelompok</option><option value="home_submission">Setoran dari rumah</option><option value="exam">Ujian</option></select></label></div>
<div class="form-grid"><label>Marhalah<select name="marhalah_type_id" id="journey-marhalah"><option value="">Tidak dipilih</option>@foreach($marhalah as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></label><label>Surah<select name="surah_id" id="journey-surah" required>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label></div>
<div class="form-grid"><label>Ayat awal<input id="journey-start" type="number" name="start_verse" min="1" required></label><label>Ayat akhir<input id="journey-end" type="number" name="end_verse" min="1" required></label></div>
<label>Hasil<select name="result"><option value="fluent">Lulus / lancar</option><option value="fair">Lulus dengan penguatan</option><option value="repeat_needed">Perlu diulang</option><option value="postponed">Belum dinilai</option></select></label>
<div class="form-grid"><label>Kelancaran<select name="fluency_status"><option value="strong">Kuat</option><option value="developing">Berkembang</option><option value="needs_repetition">Perlu pengulangan</option></select></label><label>Tajwid<select name="tajwid_status"><option value="strong">Kuat</option><option value="developing">Berkembang</option><option value="needs_correction">Perlu koreksi</option></select></label><label>Jumlah kesalahan<input type="number" min="0" name="error_count"></label><label>Prompt guru<input type="number" min="0" name="prompt_count"></label><label>Koreksi mandiri<input type="number" min="0" name="self_correction_count"></label><label>Bantuan<select name="assistance_level"><option value="none">Tanpa bantuan</option><option value="little">Sedikit bantuan</option><option value="several">Beberapa kali</option><option value="much">Banyak bantuan</option></select></label></div>
<fieldset class="choice-field"><legend>Fokus koreksi, opsional</legend><div class="chip-checks"><label><input type="checkbox" name="error_categories[]" value="hesitation"> Ragu/terhenti</label><label><input type="checkbox" name="error_categories[]" value="omission"> Terlewat</label><label><input type="checkbox" name="error_categories[]" value="substitution"> Salah lafaz</label><label><input type="checkbox" name="error_categories[]" value="sequence"> Urutan</label><label><input type="checkbox" name="error_categories[]" value="tajwid"> Tajwid</label><label><input type="checkbox" name="error_categories[]" value="prompt_dependency"> Banyak prompt</label></div></fieldset>
<div class="form-grid"><label>Ayat fokus<input type="number" min="1" name="error_ayah"></label><label>Keterangan fokus<input name="error_note"></label></div>
<label>Rekomendasi latihan<input name="review_recommendation" placeholder="Contoh: Al-Husary 10× per ayat"></label><label>Murāja‘ah berikutnya<input type="date" name="next_review_date"></label><label>Tindak lanjut<input name="follow_up" placeholder="Contoh: ulang ayat 1–5 sebelum pertemuan berikutnya"></label><label>Catatan guru<textarea name="teacher_notes" rows="3"></textarea></label><label class="check-row"><input type="checkbox" name="portion_confirmed" value="1"> <span>Jika ini hafalan baru tanpa target terkait, saya sudah memastikan porsi sesuai Marhalah Juz pada Mushaf Madinah.</span></label>
<button class="button primary">Simpan Setoran</button></form>
</div>
<div id="catat-murajaah" class="card soft-card">
<h3>Catat Murāja‘ah</h3><p class="hint">Jika memilih jadwal penjagaan, rentang ayat akan diisi otomatis dan jadwal tersebut ditandai selesai setelah catatan tersimpan.</p>
<form class="stack compact" method="post" action="{{ route('teacher.tahfizh.murajaah.store',$student) }}">@csrf
<label>Jadwal terkait<select name="review_plan_id" id="journey-review-plan"><option value="">Tanpa jadwal khusus</option>@foreach($reviewPlans->where('status','scheduled') as $plan)<option value="{{ $plan->id }}" data-surah="{{ $plan->surah_id }}" data-start="{{ $plan->start_verse }}" data-end="{{ $plan->end_verse }}">{{ $plan->surah?->name_latin }} {{ $plan->start_verse }}–{{ $plan->end_verse }} · {{ $plan->review_date?->format('d M Y') }}</option>@endforeach</select></label>
<div class="form-grid"><label>Jenis<select name="murajaah_type"><option value="scheduled">Terjadwal</option><option value="random_recall">Pemanggilan acak</option><option value="continuation">Sambung ayat</option><option value="tasmi">Tasmi‘</option><option value="home">Di rumah</option></select></label><label>Surah<select name="surah_id" id="journey-review-surah" required>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label></div>
<div class="form-grid"><label>Ayat awal<input id="journey-review-start" type="number" name="start_verse" min="1" required></label><label>Ayat akhir<input id="journey-review-end" type="number" name="end_verse" min="1" required></label></div>
<label>Hasil<select name="result"><option value="maintained">Masih terjaga</option><option value="strengthening_needed">Perlu dikuatkan</option><option value="reactivation_needed">Perlu dipanggil kembali</option></select></label><div class="form-grid"><label>Kekuatan hafalan<select name="strength_status"><option value="strong">Kuat</option><option value="fair">Cukup terjaga</option><option value="weak">Perlu penguatan</option><option value="recall_needed">Perlu dipanggil kembali</option></select></label><label>Bantuan<select name="assistance_level"><option value="none">Tanpa bantuan</option><option value="little">Sedikit bantuan</option><option value="several">Beberapa kali</option><option value="much">Banyak bantuan</option></select></label><label>Prompt guru<input type="number" min="0" name="prompt_count"></label><label>Koreksi mandiri<input type="number" min="0" name="self_correction_count"></label></div>
<fieldset class="choice-field"><legend>Fokus koreksi, opsional</legend><div class="chip-checks"><label><input type="checkbox" name="error_categories[]" value="hesitation"> Ragu/terhenti</label><label><input type="checkbox" name="error_categories[]" value="omission"> Terlewat</label><label><input type="checkbox" name="error_categories[]" value="substitution"> Salah lafaz</label><label><input type="checkbox" name="error_categories[]" value="sequence"> Urutan</label><label><input type="checkbox" name="error_categories[]" value="prompt_dependency"> Banyak prompt</label></div></fieldset>
<div class="form-grid"><label>Ayat fokus<input type="number" min="1" name="error_ayah"></label><label>Keterangan fokus<input name="error_note"></label></div><label>Jadwalkan ulang<input type="date" name="next_review_date"></label><label>Rekomendasi / tindak lanjut<input name="review_recommendation" placeholder="Contoh: ulang satu surat 3× di rumah"></label><label>Catatan guru<textarea name="teacher_notes" rows="3"></textarea></label>
<button class="button primary">Simpan Murāja‘ah</button></form>
</div>
</div>
</section>
</details>
<section class="card"><div class="section-head"><div><h2>Siklus belajar</h2><p class="hint">Status menjelaskan posisi proses, bukan nilai anak.</p></div><span>{{ $cycles->count() }}</span></div>
@forelse($cycles as $cycle)<div class="list-row"><div><strong>{{ ucfirst(str_replace('_',' ',$cycle->cycle_type)) }} · {{ $cycle->target?->surah?->name_latin ?? 'Tanpa target terkait' }}</strong><small>{{ ucfirst(str_replace('_',' ',$cycle->preparation_method)) }} · {{ ucfirst(str_replace('_',' ',$cycle->status)) }}</small><p>{{ $cycle->teacher_guidance }} {{ $cycle->guardian_guidance }}</p></div><form class="inline-form" method="post" action="{{ route('teacher.tahfizh.cycles.update',$cycle) }}">@csrf @method('PUT')<select name="status"><option value="preparing" @selected($cycle->status==='preparing')>Persiapan</option><option value="ready" @selected($cycle->status==='ready')>Siap</option><option value="submitted" @selected($cycle->status==='submitted')>Sudah setoran</option><option value="strengthening" @selected($cycle->status==='strengthening')>Penguatan</option><option value="completed" @selected($cycle->status==='completed')>Selesai</option><option value="paused" @selected($cycle->status==='paused')>Jeda</option><option value="cancelled" @selected($cycle->status==='cancelled')>Dibatalkan</option></select><input type="hidden" name="teacher_guidance" value="{{ $cycle->teacher_guidance }}"><input type="hidden" name="guardian_guidance" value="{{ $cycle->guardian_guidance }}"><button class="button small secondary">Simpan</button></form></div>@empty<p class="empty">Belum ada siklus belajar.</p>@endforelse
</section>
<div class="grid two">
<section class="card"><div class="section-head"><h2>Jadwal penjagaan</h2><span>{{ $reviewPlans->count() }}</span></div>
@forelse($reviewPlans as $plan)<div class="list-row"><div><strong>{{ $plan->surah?->name_latin }} {{ $plan->start_verse }}–{{ $plan->end_verse }}</strong><small>{{ $plan->review_date?->format('d M Y') }} · {{ ucfirst(str_replace('_',' ',$plan->status)) }} · {{ ['normal'=>'Penjagaan','strengthen'=>'Penguatan','recall'=>'Panggil kembali'][$plan->priority] ?? $plan->priority }}</small><p>{{ $plan->notes }}</p></div>@if($plan->status==='scheduled')<form method="post" action="{{ route('teacher.tahfizh.reviews.update',$plan) }}">@csrf @method('PUT')<input type="hidden" name="status" value="skipped"><button class="button small ghost">Lewati</button></form>@endif</div>@empty<p class="empty">Belum ada jadwal Murāja‘ah.</p>@endforelse
</section>
<section class="card"><div class="section-head"><h2>Fokus koreksi</h2><span>{{ $correctionItems->whereNull('resolved_at')->count() }} terbuka</span></div>
@forelse($correctionItems as $error)<div class="list-row"><div><strong>{{ $errorLabels[$error->category] ?? ucfirst(str_replace('_',' ',$error->category)) }}</strong><small>{{ ucfirst($error->record_type) }} · {{ $error->created_at?->format('d M Y') }} @if($error->ayah_number) · ayat {{ $error->ayah_number }} @endif</small><p>{{ $error->note }}</p></div>@if(!$error->resolved_at)<form method="post" action="{{ route('teacher.tahfizh.errors.resolve',$error) }}">@csrf @method('PUT')<button class="button small secondary">Sudah ditindaklanjuti</button></form>@else<span class="status-pill success">Selesai</span>@endif</div>@empty<p class="empty">Belum ada fokus koreksi terstruktur.</p>@endforelse
</section>
</div>
<section class="card"><div class="section-head"><h2>Riwayat setoran & Murāja‘ah</h2><span>Terbaru</span></div><div class="grid two">
<div><h3>Setoran</h3>@forelse($student->memorizationRecords as $record)<div class="list-row"><div><strong>{{ $record->surah?->name_latin }} {{ $record->start_verse }}–{{ $record->end_verse }}</strong><small>{{ $record->recorded_at?->format('d M Y') }} · {{ ['fluent'=>'Lancar','fair'=>'Lulus dengan penguatan','repeat_needed'=>'Perlu diulang','postponed'=>'Belum dinilai'][$record->result] ?? $record->result }} · {{ ucfirst(str_replace('_',' ',$record->delivery_mode ?? 'individual_submission')) }}</small><p>{{ $record->review_recommendation ?: $record->teacher_notes }}</p></div></div>@empty<p class="empty">Belum ada setoran.</p>@endforelse</div>
<div><h3>Murāja‘ah</h3>@forelse($student->murajaahRecords as $record)<div class="list-row"><div><strong>{{ $record->surah?->name_latin }} {{ $record->start_verse }}–{{ $record->end_verse }}</strong><small>{{ $record->recorded_at?->format('d M Y') }} · {{ ['maintained'=>'Masih terjaga','strengthening_needed'=>'Perlu dikuatkan','reactivation_needed'=>'Perlu dipanggil kembali'][$record->result] ?? $record->result }}</small><p>{{ $record->review_recommendation ?: $record->teacher_notes }}</p></div></div>@empty<p class="empty">Belum ada Murāja‘ah.</p>@endforelse</div>
</div></section>
<script>
(function () {
    function copyRange(selectId, surahId, startId, endId, marhalahId) {
        var select = document.getElementById(selectId);
        if (!select) return;
        function applySelected() {
            var option = select.options[select.selectedIndex];
            if (!option || !option.dataset.surah) return;
            var surah = document.getElementById(surahId);
            var start = document.getElementById(startId);
            var end = document.getElementById(endId);
            if (surah) surah.value = option.dataset.surah;
            if (start) start.value = option.dataset.start || '';
            if (end) end.value = option.dataset.end || '';
            if (marhalahId) {
                var marhalah = document.getElementById(marhalahId);
                if (marhalah && option.dataset.marhalah) marhalah.value = option.dataset.marhalah;
            }
        }
        select.addEventListener('change', applySelected);
        applySelected();
    }
    copyRange('journey-target', 'journey-surah', 'journey-start', 'journey-end', 'journey-marhalah');
    copyRange('journey-review-plan', 'journey-review-surah', 'journey-review-start', 'journey-review-end');
})();
</script>
@endsection
