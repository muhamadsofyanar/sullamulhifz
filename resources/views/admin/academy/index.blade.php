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
                <div class="form-grid"><label>Kategori<select name="category"><option value="family">Family Learning</option><option value="teacher">Teacher Academy</option><option value="quran">Quran Learning</option><option value="personalization">Personalisasi</option><option value="parenting">Pendidikan Anak</option><option value="talent">Character & Talent</option><option value="general">Umum</option></select></label><label>Learning track<input name="learning_track" placeholder="contoh: parent, quran-life"></label></div>
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
                <div class="form-grid"><label>Jenis<select name="lesson_type"><option value="article">Bacaan</option><option value="video">Video</option><option value="audio">Audio</option><option value="activity">Aktivitas keluarga</option><option value="checklist">Checklist</option><option value="reflection">Refleksi</option><option value="pdf">PDF</option><option value="link">Tautan</option></select></label><label>Durasi<input type="number" name="duration_minutes" min="1" max="600" value="5"></label></div>
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

<section class="card academy-path-studio" style="margin-top:18px">
    <div class="section-head"><div><span class="eyebrow">LEARNING PATH STUDIO</span><h2>Susun jalur belajar tanpa redeploy</h2><p class="muted">Gabungkan materi Academy dan preset Audio Qur’an menjadi urutan belajar yang sederhana.</p></div><span class="badge">{{ $paths->count() }} jalur</span></div>
    <div class="grid two">
        <form method="post" action="{{ route('admin.academy.paths.store') }}" class="stack">
            @csrf
            <h3>Buat jalur baru</h3>
            <label>Judul<input name="title" required placeholder="Contoh: Murāja‘ah dengan Tenang"></label>
            <div class="form-grid"><label>Untuk<select name="audience"><option value="guardian">Orang tua</option><option value="teacher">Guru</option><option value="all">Semua</option></select></label><label>Kategori<input name="category" placeholder="quran / family / teacher"></label></div>
            <label>Ringkasan<textarea name="summary" rows="3"></textarea></label>
            <div class="form-grid"><label>Status<select name="status"><option value="draft">Draf</option><option value="published">Terbit</option></select></label><label class="check"><input type="checkbox" name="is_featured" value="1"> Tampilkan utama</label></div>
            <button class="button primary" type="submit">Buat jalur</button>
        </form>

        <form method="post" action="{{ route('admin.academy.path-items.store') }}" class="stack" data-path-item-form>
            @csrf
            <h3>Tambahkan langkah</h3>
            <label>Jalur<select name="academy_learning_path_id" required>@foreach($paths as $path)<option value="{{ $path->id }}">{{ $path->title }}</option>@endforeach</select></label>
            <label>Jenis<select name="item_type" data-path-item-type><option value="lesson">Materi Academy</option><option value="quran_preset">Preset Audio Qur’an</option></select></label>
            <label data-path-lessons>Materi<select name="item_id" data-path-lesson-select>@foreach($programs as $program)@foreach($program->modules as $module)@foreach($module->lessons as $lesson)<option value="{{ $lesson->id }}">{{ $program->title }} — {{ $lesson->title }}</option>@endforeach @endforeach @endforeach</select></label>
            <label data-path-presets hidden>Preset Qur’an<select data-path-preset-select>@foreach($quranPresets as $preset)<option value="{{ $preset->id }}">{{ $preset->title }}</option>@endforeach</select></label>
            <label>Judul alternatif, opsional<input name="title_override"></label>
            <label>Arahan singkat<textarea name="instruction" rows="2"></textarea></label>
            <label class="check"><input type="checkbox" name="is_required" value="1" checked> Langkah wajib</label>
            <button class="button primary" type="submit" @disabled($paths->isEmpty())>Tambah langkah</button>
        </form>
    </div>

    <div class="academy-path-admin-list">
    @forelse($paths as $path)
        <details class="academy-studio-panel">
            <summary><span>{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</span><div><strong>{{ $path->title }}</strong><small>{{ $path->audience }} · {{ $path->items->count() }} langkah · {{ $path->status }}</small></div></summary>
            <div class="stack academy-path-admin-body">
                <form method="post" action="{{ route('admin.academy.paths.update',$path) }}" class="stack">@csrf @method('put')
                    <div class="form-grid"><label>Judul<input name="title" value="{{ $path->title }}" required></label><label>Untuk<select name="audience"><option value="guardian" @selected($path->audience==='guardian')>Orang tua</option><option value="teacher" @selected($path->audience==='teacher')>Guru</option><option value="all" @selected($path->audience==='all')>Semua</option></select></label></div>
                    <div class="form-grid"><label>Kategori<input name="category" value="{{ $path->category }}"></label><label>Status<select name="status"><option value="draft" @selected($path->status==='draft')>Draf</option><option value="published" @selected($path->status==='published')>Terbit</option><option value="archived" @selected($path->status==='archived')>Arsip</option></select></label></div>
                    <label>Ringkasan<textarea name="summary" rows="2">{{ $path->summary }}</textarea></label>
                    <label class="check"><input type="checkbox" name="is_featured" value="1" @checked($path->is_featured)> Jalur utama</label>
                    <button class="button secondary" type="submit">Simpan jalur</button>
                </form>
                @foreach($path->items as $item)
                    <div class="list-row"><div><strong>{{ $item->title_override ?: strtoupper($item->item_type).' #'.$item->item_id }}</strong><small>{{ $item->instruction ?: ($item->is_required?'Wajib':'Opsional') }}</small></div><form method="post" action="{{ route('admin.academy.path-items.destroy',$item) }}">@csrf @method('delete')<button class="button secondary" type="submit">Hapus</button></form></div>
                @endforeach
                @if($path->status==='published')<a class="button secondary" href="{{ route('academy.portal.path',$path) }}">Pratinjau jalur</a>@endif
            </div>
        </details>
    @empty<p class="muted">Belum ada jalur belajar.</p>@endforelse
    </div>
</section>

<section class="card" style="margin-top:18px">
    <div class="section-head"><div><span class="eyebrow">LMS 2.0 · V2.7.0</span><h2>Prasyarat, kuis & worksheet</h2><p class="muted">Atur gate penyelesaian langsung dari Academy Studio tanpa mengubah source.</p></div><span class="badge">{{ $quizzes->count() }} kuis · {{ $worksheets->count() }} worksheet</span></div>
    <div class="grid two">
        <form method="post" action="{{ route('admin.academy.prerequisites.store') }}" class="stack">@csrf
            <h3>Prasyarat materi</h3><input type="hidden" name="subject_type" value="lesson"><input type="hidden" name="required_type" value="lesson">
            <label>Materi terkunci<select name="subject_id" required>@foreach($academyLessons as $lesson)<option value="{{ $lesson->id }}">{{ $lesson->module->program->title }} — {{ $lesson->title }}</option>@endforeach</select></label>
            <label>Harus selesaikan terlebih dahulu<select name="required_id" required>@foreach($academyLessons as $lesson)<option value="{{ $lesson->id }}">{{ $lesson->module->program->title }} — {{ $lesson->title }}</option>@endforeach</select></label>
            <button class="button secondary" type="submit">Simpan prasyarat materi</button>
        </form>
        <form method="post" action="{{ route('admin.academy.prerequisites.store') }}" class="stack">@csrf
            <h3>Prasyarat jalur belajar</h3><input type="hidden" name="subject_type" value="path"><input type="hidden" name="required_type" value="path">
            <label>Jalur terkunci<select name="subject_id" required>@foreach($paths as $path)<option value="{{ $path->id }}">{{ $path->title }}</option>@endforeach</select></label>
            <label>Jalur yang harus selesai<select name="required_id" required>@foreach($paths as $path)<option value="{{ $path->id }}">{{ $path->title }}</option>@endforeach</select></label>
            <button class="button secondary" type="submit" @disabled($paths->count()<2)>Simpan prasyarat jalur</button>
        </form>
    </div>
    @if($prerequisites->isNotEmpty())<div class="cards-list" style="margin-top:16px">@foreach($prerequisites as $prerequisite)<div class="list-row"><div><strong>{{ ucfirst($prerequisite->subject_type) }} #{{ $prerequisite->subject_id }}</strong><small>memerlukan {{ $prerequisite->required_type }} #{{ $prerequisite->required_id }}</small></div><form method="post" action="{{ route('admin.academy.prerequisites.destroy',$prerequisite) }}">@csrf @method('delete')<button class="button secondary" type="submit">Hapus</button></form></div>@endforeach</div>@endif
</section>

<section class="card" style="margin-top:18px">
    <div class="section-head"><div><span class="eyebrow">ASSESSMENT</span><h2>Kuis terstruktur</h2><p class="muted">Satu kuis per materi, dengan nilai lulus dan batas percobaan.</p></div></div>
    <form method="post" action="{{ route('admin.academy.quizzes.store') }}" class="stack">@csrf
        <label>Materi<select name="academy_lesson_id" required>@foreach($academyLessons as $lesson)<option value="{{ $lesson->id }}">{{ $lesson->module->program->title }} — {{ $lesson->title }}</option>@endforeach</select></label>
        <div class="form-grid"><label>Judul<input name="title" value="Kuis pemahaman" required></label><label>Nilai lulus (%)<input type="number" name="passing_percent" min="1" max="100" value="70" required></label></div>
        <label>Instruksi<textarea name="instructions" rows="2" placeholder="Pilih jawaban yang paling tepat."></textarea></label>
        <div class="form-grid"><label>Maks. percobaan<input type="number" name="max_attempts" min="1" max="10" value="3" required></label><label>Status<select name="status"><option value="draft">Draf</option><option value="published">Terbit</option></select></label></div>
        <button class="button primary" type="submit">Simpan kuis</button>
    </form>
    <div style="margin-top:18px">
    @foreach($quizzes as $quiz)
        <details class="academy-studio-panel"><summary><span>Q</span><div><strong>{{ $quiz->title }}</strong><small>{{ $quiz->lesson->title }} · {{ $quiz->questions->count() }} pertanyaan · lulus {{ $quiz->passing_percent }}%</small></div></summary>
            <div class="stack academy-path-admin-body">
                @foreach($quiz->questions as $question)<div class="list-row"><div><strong>{{ $loop->iteration }}. {{ $question->prompt }}</strong><small>{{ $question->points }} poin</small></div><form method="post" action="{{ route('admin.academy.quiz-questions.destroy',$question) }}">@csrf @method('delete')<button class="button secondary" type="submit">Hapus</button></form></div>@endforeach
                <form method="post" action="{{ route('admin.academy.quiz-questions.store') }}" class="stack">@csrf<input type="hidden" name="academy_quiz_id" value="{{ $quiz->id }}">
                    <label>Pertanyaan<textarea name="prompt" rows="2" required></textarea></label><input type="hidden" name="points" value="1">
                    @for($i=0;$i<4;$i++)<label>Opsi {{ chr(65+$i) }}<input name="options[{{ $i }}]" required></label>@endfor
                    <label>Jawaban benar<select name="correct_option"><option value="0">A</option><option value="1">B</option><option value="2">C</option><option value="3">D</option></select></label>
                    <label>Penjelasan, opsional<textarea name="explanation" rows="2"></textarea></label>
                    <button class="button secondary" type="submit">Tambah pertanyaan</button>
                </form>
            </div>
        </details>
    @endforeach
    </div>
</section>

<section class="card" style="margin-top:18px">
    <div class="section-head"><div><span class="eyebrow">AKTIVITAS</span><h2>Worksheet terstruktur</h2><p class="muted">Gunakan refleksi tertulis atau konfirmasi praktik sebagai syarat penyelesaian.</p></div></div>
    <form method="post" action="{{ route('admin.academy.worksheets.store') }}" class="stack">@csrf
        <label>Materi<select name="academy_lesson_id" required>@foreach($academyLessons as $lesson)<option value="{{ $lesson->id }}">{{ $lesson->module->program->title }} — {{ $lesson->title }}</option>@endforeach</select></label>
        <label>Judul<input name="title" required placeholder="Refleksi & tindak lanjut"></label>
        <label>Instruksi<textarea name="instructions" rows="3" placeholder="Tuliskan apa yang akan dipraktikkan setelah materi ini."></textarea></label>
        <div class="form-grid"><label>Mode<select name="completion_mode"><option value="reflection">Refleksi tertulis</option><option value="self_check">Konfirmasi praktik</option></select></label><label>Status<select name="status"><option value="draft">Draf</option><option value="published">Terbit</option></select></label></div>
        <label class="check"><input type="checkbox" name="is_required" value="1" checked> Wajib sebelum materi selesai</label>
        <button class="button primary" type="submit">Simpan worksheet</button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded',()=>{const form=document.querySelector('[data-path-item-form]');if(!form)return;const type=form.querySelector('[data-path-item-type]');const lessonWrap=form.querySelector('[data-path-lessons]');const presetWrap=form.querySelector('[data-path-presets]');const lessonSelect=form.querySelector('[data-path-lesson-select]');const presetSelect=form.querySelector('[data-path-preset-select]');const sync=()=>{const q=type.value==='quran_preset';lessonWrap.hidden=q;presetWrap.hidden=!q;if(q){lessonSelect.removeAttribute('name');presetSelect.setAttribute('name','item_id')}else{presetSelect.removeAttribute('name');lessonSelect.setAttribute('name','item_id')}};type.addEventListener('change',sync);sync();});
</script>

<div class="academy-studio-library">
<?php foreach ($programs as $program): ?>
    <section class="academy-studio-program card">
        <header>
            <div><span class="academy-studio-audience"><?= e($program->audience === 'guardian' ? 'PARENT ACADEMY' : ($program->audience === 'teacher' ? 'ACADEMY GURU' : 'SEMUA PENGGUNA')) ?></span><h2><?= e($program->title) ?></h2><p><?= e($program->summary) ?></p></div>
            <span class="badge"><?= e($program->status) ?></span>
        </header>
        <details class="academy-program-settings">
            <summary>Pengaturan program</summary>
            <form method="post" action="<?= e(route('admin.academy.programs.update',$program)) ?>" class="stack academy-studio-edit">
                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="_method" value="PUT">
                <label>Judul<input name="title" required value="<?= e($program->title) ?>"></label>
                <div class="form-grid"><label>Untuk<select name="audience"><option value="guardian" <?= $program->audience==='guardian'?'selected':'' ?>>Orang tua</option><option value="teacher" <?= $program->audience==='teacher'?'selected':'' ?>>Guru</option><option value="all" <?= $program->audience==='all'?'selected':'' ?>>Semua</option></select></label><label>Kategori<input name="category" value="<?= e($program->category) ?>"></label></div>
                <label>Learning track<input name="learning_track" value="<?= e($program->learning_track) ?>"></label>
                <label>Ringkasan<textarea name="summary" rows="2"><?= e($program->summary) ?></textarea></label><label>Deskripsi<textarea name="description" rows="3"><?= e($program->description) ?></textarea></label>
                <div class="form-grid"><label>Status<select name="status"><option value="draft" <?= $program->status==='draft'?'selected':'' ?>>Draf</option><option value="published" <?= $program->status==='published'?'selected':'' ?>>Terbit</option><option value="archived" <?= $program->status==='archived'?'selected':'' ?>>Arsip</option></select></label><label class="check"><input type="checkbox" name="is_featured" value="1" <?= $program->is_featured?'checked':'' ?>> Program utama</label></div>
                <button class="button secondary" type="submit">Simpan program</button>
            </form>
        </details>

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
                                <?php foreach (['article'=>'Bacaan','video'=>'Video','audio'=>'Audio','activity'=>'Aktivitas','checklist'=>'Checklist','reflection'=>'Refleksi','pdf'=>'PDF','link'=>'Tautan'] as $value=>$label): ?>
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
