@extends('layouts.app')
@section('content')
<div class="guided-page">
    <section class="page-heading"><div><span class="eyebrow">PROGRAM ONLINE & HYBRID</span><h1>Program Al-Qur’an</h1><p class="muted">Kelola program yang bisa diikuti Personal atau santri lembaga. Reviewer hanya mendapat akses ke setoran program.</p></div></section>
    <section class="card">
        <div class="section-head"><div><h2>Buat program</h2><p class="muted">Materi Academy bersifat opsional; kaitkan bila peserta perlu belajar mandiri sebelum setoran.</p></div></div>
        <form method="post" action="{{ route('admin.guided-learning.programs.store') }}" class="personal-form-grid">@csrf
            <label class="span-all">Nama program<input name="title" maxlength="190" required placeholder="Contoh: Tahfizh Juz 30 Online"></label>
            <label>Jenis<select name="program_type"><option value="tahfizh">Tahfizh</option><option value="reading">Membaca Al-Qur’an</option><option value="tahsin">Tahsin</option><option value="murajaah">Murāja‘ah</option></select></label>
            <label>Mode<select name="delivery_mode"><option value="online">Online</option><option value="hybrid">Hybrid</option><option value="offline">Offline</option></select></label>
            <label>Target juz<input type="number" min="1" max="30" name="target_juz"></label>
            <label>Materi Academy<select name="academy_program_id"><option value="">Tanpa materi khusus</option>@foreach($academyPrograms as $academy)<option value="{{ $academy->id }}">{{ $academy->title }}</option>@endforeach</select></label>
            <label class="span-all">Ringkasan<textarea name="summary" rows="2" maxlength="2000"></textarea></label>
            <label class="span-all">Panduan setoran<textarea name="submission_guidance" rows="3" maxlength="5000" placeholder="Contoh: kirim voice note per 5–10 ayat, maksimal satu setoran per sesi latihan."></textarea></label>
            <label><input type="checkbox" name="accepts_audio" value="1" checked> Terima audio/voice note</label>
            <label><input type="checkbox" name="accepts_text" value="1" checked> Terima teks</label>
            <label><input type="checkbox" name="is_public" value="1"> Tampil untuk pengguna Personal</label>
            <label>Status<select name="status"><option value="draft">Draft</option><option value="published">Published</option></select></label>
            <div class="span-all"><button class="button primary">Simpan program</button></div>
        </form>
    </section>

    @foreach($programs as $program)
    <section class="card guided-admin-program">
        <div class="guided-enrollment-head"><div><span class="guided-chip">{{ ucfirst($program->delivery_mode) }} · {{ $program->is_public ? 'Publik Personal' : 'Internal' }}</span><h2>{{ $program->title }}</h2><p class="muted">{{ $program->summary }}</p></div><strong>{{ $program->enrollments->count() }} peserta</strong></div>
        <div class="guided-admin-columns">
            <div><h3>Reviewer asatidz</h3><p class="muted">{{ $program->reviewers->where('status','active')->pluck('reviewer.name')->filter()->join(', ') ?: 'Belum ada reviewer.' }}</p>
                <form method="post" action="{{ route('admin.guided-learning.reviewers.store', $program) }}" class="inline-form">@csrf<select name="reviewer_user_id" required><option value="">Pilih asatidz</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select><button class="button secondary">Tambahkan</button></form>
            </div>
            <div><h3>Santri TPA / lembaga</h3><p class="muted">Santri offline/hybrid dapat dimasukkan ke program yang sama.</p>
                <form method="post" action="{{ route('admin.guided-learning.students.store', $program) }}" class="inline-form">@csrf<select name="student_id" required><option value="">Pilih santri</option>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->full_name }}</option>@endforeach</select><button class="button secondary">Tambahkan</button></form>
            </div>
        </div>
    </section>
    @endforeach
</div>
@endsection
