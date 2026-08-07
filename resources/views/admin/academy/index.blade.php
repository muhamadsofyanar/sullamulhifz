@extends('layouts.app', ['pageTitle' => 'Academy Studio'])

@section('content')
<div class="academy-studio-head">
    <div>
        <span class="eyebrow">ACADEMY STUDIO</span>
        <h1>Kelola Academy</h1>
        <p>Susun materi orang tua dan guru dengan format singkat, rapi, dan mudah dipakai dari ponsel.</p>
    </div>
    <a class="button secondary" href="{{ route('academy.portal.index') }}">Lihat sebagai peserta</a>
</div>

<div class="academy-studio-stats">
    <article><small>Program</small><strong>{{ $programs->count() }}</strong><span>aktif & draf</span></article>
    <article><small>Materi</small><strong>{{ $lessonCount }}</strong><span>semua format</span></article>
    <article><small>Selesai</small><strong>{{ $progressCount }}</strong><span>progress pengguna</span></article>
    <article><small>Rekomendasi</small><strong>{{ $recommendationCount }}</strong><span>aktif untuk keluarga</span></article>
</div>

<section class="academy-studio-compose card">
    <div class="section-head">
        <div><span class="eyebrow">BUAT KONTEN</span><h2>Tambahkan materi tanpa halaman yang rumit</h2></div>
    </div>
    <div class="academy-studio-compose-grid">
        <details class="academy-studio-panel">
            <summary><span>01</span><div><strong>Program</strong><small>Parent Academy, Academy Guru, atau program lain.</small></div></summary>
            <form method="post" action="{{ route('admin.academy.programs.store') }}" class="stack academy-studio-form">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <label>Judul<input name="title" required placeholder="Contoh: Parent Academy — Tahfizh di Rumah"></label>
                <label>Untuk<select name="audience"><option value="guardian">Orang tua / wali</option><option value="teacher">Guru</option><option value="all">Semua pengguna</option></select></label>
                <label>Ringkasan<textarea name="summary" rows="3" placeholder="Tujuan program dalam 1–2 kalimat"></textarea></label>
                <label>Deskripsi<textarea name="description" rows="4"></textarea></label>
                <div class="form-grid"><label>Status<select name="status"><option value="draft">Draf</option><option value="published">Terbit</option></select></label><label class="check"><input type="checkbox" name="is_featured" value="1"> Program utama</label></div>
                <button class="button primary" type="submit">Buat program</button>
            </form>
        </details>

        <details class="academy-studio-panel">
            <summary><span>02</span><div><strong>Modul</strong><small>Kelompokkan materi agar alurnya jelas.</small></div></summary>
            <form method="post" action="{{ route('admin.academy.modules.store') }}" class="stack academy-studio-form">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <label>Program<select name="academy_program_id" required>
                    <?php foreach ($programs as $program): ?>
                        <option value="<?= e($program->id) ?>"><?= e($program->title) ?></option>
                    <?php endforeach; ?>
                </select></label>
                <label>Judul modul<input name="title" required placeholder="Contoh: Mendampingi Murajaah"></label>
                <label>Ringkasan<textarea name="summary" rows="3"></textarea></label>
                <button class="button primary" type="submit">Tambah modul</button>
            </form>
        </details>

        <details class="academy-studio-panel" open>
            <summary><span>03</span><div><strong>Materi</strong><small>Video, bacaan, audio, checklist, atau aktivitas keluarga.</small></div></summary>
            <form method="post" action="{{ route('admin.academy.lessons.store') }}" class="stack academy-studio-form">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <label>Modul<select name="academy_module_id" required>
                    <?php foreach ($programs as $program): ?>
                        <?php foreach ($program->modules as $module): ?>
                            <option value="<?= e($module->id) ?>"><?= e($program->title) ?> — <?= e($module->title) ?></option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select></label>
                <div class="form-grid"><label>Jenis<select name="lesson_type"><option value="article">Bacaan</option><option value="video">Video</option><option value="audio">Audio</option><option value="activity">Aktivitas keluarga</option><option value="checklist">Checklist</option><option value="pdf">PDF</option><option value="link">Tautan</option></select></label><label>Durasi<input type="number" name="duration_minutes" min="1" max="600" value="5"></label></div>
                <label>Judul<input name="title" required></label>
                <label>Ringkasan<textarea name="summary" rows="3"></textarea></label>
                <label>Isi materi<textarea name="body" rows="7" placeholder="Gunakan paragraf pendek dan bahasa yang hangat."></textarea></label>
                <label>URL media<input type="url" name="media_url" placeholder="YouTube Shorts, YouTube, audio, PDF, atau tautan lain"></label>
                <div class="form-grid"><label>Status<select name="status"><option value="draft">Draf</option><option value="published">Terbit</option></select></label><label class="check"><input type="checkbox" name="requires_action" value="1"> Ada tindak lanjut</label></div>
                <button class="button primary" type="submit">Simpan materi</button>
            </form>
        </details>
    </div>
</section>

<div class="academy-studio-library">
<?php foreach ($programs as $program): ?>
    <section class="academy-studio-program card">
        <header>
            <div><span class="academy-studio-audience"><?= e($program->audience === 'guardian' ? 'PARENT ACADEMY' : ($program->audience === 'teacher' ? 'ACADEMY GURU' : 'SEMUA PENGGUNA')) ?></span><h2><?= e($program->title) ?></h2><p><?= e($program->summary) ?></p></div>
            <span class="badge"><?= e($program->status) ?></span>
        </header>

        <?php foreach ($program->modules as $module): ?>
            <div class="academy-studio-module">
                <div class="academy-studio-module-head"><div><small>MODUL</small><h3><?= e($module->title) ?></h3></div><span><?= e($module->lessons->count()) ?> materi</span></div>
                <div class="academy-studio-lessons">
                <?php foreach ($module->lessons as $lesson): ?>
                    <details class="academy-studio-lesson">
                        <summary>
                            <span class="academy-studio-type"><?= e(strtoupper($lesson->lesson_type)) ?></span>
                            <div><strong><?= e($lesson->title) ?></strong><small><?= e($lesson->duration_minutes ?? 5) ?> menit · <?= e($lesson->status) ?></small></div>
                            <span class="academy-studio-chevron">⌄</span>
                        </summary>
                        <form method="post" action="<?= e(route('admin.academy.lessons.update', $lesson)) ?>" class="stack academy-studio-edit">
                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="_method" value="PUT">
                            <label>Judul<input name="title" required value="<?= e($lesson->title) ?>"></label>
                            <div class="form-grid"><label>Jenis<select name="lesson_type">
                                <?php foreach (['article'=>'Bacaan','video'=>'Video','audio'=>'Audio','activity'=>'Aktivitas','checklist'=>'Checklist','pdf'=>'PDF','link'=>'Tautan'] as $value=>$label): ?>
                                    <option value="<?= e($value) ?>" <?= $lesson->lesson_type === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select></label><label>Durasi<input type="number" name="duration_minutes" min="1" max="600" value="<?= e($lesson->duration_minutes ?? 5) ?>"></label></div>
                            <label>Ringkasan<textarea name="summary" rows="3"><?= e($lesson->summary) ?></textarea></label>
                            <label>Isi materi<textarea name="body" rows="7"><?= e($lesson->body) ?></textarea></label>
                            <label>URL media<input type="url" name="media_url" value="<?= e($lesson->media_url) ?>" placeholder="https://..."></label>
                            <div class="form-grid"><label>Status<select name="status"><option value="draft" <?= $lesson->status === 'draft' ? 'selected' : '' ?>>Draf</option><option value="published" <?= $lesson->status === 'published' ? 'selected' : '' ?>>Terbit</option><option value="archived" <?= $lesson->status === 'archived' ? 'selected' : '' ?>>Arsip</option></select></label><label class="check"><input type="checkbox" name="requires_action" value="1" <?= $lesson->requires_action ? 'checked' : '' ?>> Ada tindak lanjut</label></div>
                            <div class="academy-studio-edit-actions"><button class="button primary" type="submit">Simpan perubahan</button><?php if ($lesson->status === 'published'): ?><a class="button secondary" href="<?= e(route('academy.portal.lesson', $lesson)) ?>">Pratinjau</a><?php endif; ?></div>
                        </form>
                    </details>
                <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
<?php endforeach; ?>
</div>
@endsection
