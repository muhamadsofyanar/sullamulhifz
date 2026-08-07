@extends('layouts.app', ['pageTitle' => 'Kelola Academy'])

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">FAMILY LEARNING & ACADEMY</span>
        <h1>Kelola Academy</h1>
        <p>Bangun materi singkat yang membantu orang tua dan guru. Prioritaskan komunikasi dan praktik nyata.</p>
    </div>
    <a class="button secondary" href="{{ route('academy.index') }}">Lihat Academy</a>
</div>

<div class="stats-grid three">
    <div class="stat-card">
        <span>Program</span>
        <strong>{{ $programs->count() }}</strong>
    </div>
    <div class="stat-card">
        <span>Materi</span>
        <strong>{{ $lessonCount }}</strong>
    </div>
    <div class="stat-card">
        <span>Materi selesai / rekomendasi aktif</span>
        <strong>{{ $progressCount }} / {{ $recommendationCount }}</strong>
    </div>
</div>

<div class="grid two">
    <section class="card">
        <h2>Tambah program</h2>
        <form method="post" action="{{ route('admin.academy.programs.store') }}" class="stack">
            @csrf
            <label>
                Judul
                <input name="title" required placeholder="Contoh: Parent Academy — Tahfizh di Rumah">
            </label>
            <label>
                Untuk
                <select name="audience">
                    <option value="guardian">Orang tua/wali</option>
                    <option value="teacher">Guru</option>
                    <option value="all">Semua pengguna</option>
                </select>
            </label>
            <label>
                Ringkasan
                <textarea name="summary" placeholder="Tujuan program dalam 1–2 kalimat"></textarea>
            </label>
            <label>
                Deskripsi
                <textarea name="description"></textarea>
            </label>
            <div class="form-grid">
                <label>
                    Status
                    <select name="status">
                        <option value="draft">Draf</option>
                        <option value="published">Terbit</option>
                    </select>
                </label>
                <label class="check">
                    <input type="checkbox" name="is_featured" value="1"> Jadikan program utama
                </label>
            </div>
            <button class="button primary" type="submit">Buat program</button>
        </form>
    </section>

    <section class="card">
        <h2>Tambah modul</h2>
        <form method="post" action="{{ route('admin.academy.modules.store') }}" class="stack">
            @csrf
            <label>
                Program
                <select name="academy_program_id" required>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->title }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Judul modul
                <input name="title" required placeholder="Contoh: Mendampingi Murajaah">
            </label>
            <label>
                Ringkasan
                <textarea name="summary"></textarea>
            </label>
            <button class="button primary" type="submit">Tambah modul</button>
        </form>
    </section>
</div>

<section class="card">
    <h2>Tambah materi</h2>
    <form method="post" action="{{ route('admin.academy.lessons.store') }}" class="stack">
        @csrf
        <div class="form-grid">
            <label>
                Modul
                <select name="academy_module_id" required>
                    @foreach($programs as $program)
                        @foreach($program->modules as $module)
                            <option value="{{ $module->id }}">{{ $program->title }} — {{ $module->title }}</option>
                        @endforeach
                    @endforeach
                </select>
            </label>
            <label>
                Jenis
                <select name="lesson_type">
                    <option value="article">Artikel/bacaan</option>
                    <option value="activity">Aktivitas keluarga</option>
                    <option value="checklist">Checklist</option>
                    <option value="video">Video</option>
                    <option value="audio">Audio</option>
                    <option value="pdf">PDF</option>
                    <option value="link">Tautan</option>
                </select>
            </label>
        </div>
        <label>
            Judul
            <input name="title" required>
        </label>
        <label>
            Ringkasan
            <textarea name="summary"></textarea>
        </label>
        <label>
            Isi materi
            <textarea name="body" rows="9" placeholder="Gunakan paragraf pendek dan bahasa hangat."></textarea>
        </label>
        <div class="form-grid">
            <label>
                URL media (opsional)
                <input type="url" name="media_url">
            </label>
            <label>
                Durasi menit
                <input type="number" name="duration_minutes" min="1" max="600" value="5">
            </label>
        </div>
        <div class="form-grid">
            <label>
                Status
                <select name="status">
                    <option value="draft">Draf</option>
                    <option value="published">Terbit</option>
                </select>
            </label>
            <label class="check">
                <input type="checkbox" name="requires_action" value="1"> Ada aktivitas/tindak lanjut
            </label>
        </div>
        <button class="button primary" type="submit">Simpan materi</button>
    </form>
</section>

@foreach($programs as $program)
    <section class="card academy-admin-program">
        <div class="section-head">
            <div>
                <h2>{{ $program->title }}</h2>
                <p class="hint">{{ $program->summary }}</p>
            </div>
            <span class="badge">{{ $program->status }} · {{ $program->audience }}</span>
        </div>

        @foreach($program->modules as $module)
            <h3>{{ $module->title }}</h3>
            <div class="cards-list">
                @foreach($module->lessons as $lesson)
                    <div class="list-row">
                        <div>
                            <strong>{{ $lesson->title }}</strong>
                            <small>{{ $lesson->lesson_type }} · {{ $lesson->duration_minutes ?? 5 }} menit</small>
                        </div>
                        <form method="post" action="{{ route('admin.academy.lessons.update', $lesson) }}" class="academy-inline-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="title" value="{{ $lesson->title }}">
                            <input type="hidden" name="summary" value="{{ $lesson->summary }}">
                            <select name="status">
                                <option value="draft" {{ $lesson->status === 'draft' ? 'selected' : '' }}>Draf</option>
                                <option value="published" {{ $lesson->status === 'published' ? 'selected' : '' }}>Terbit</option>
                                <option value="archived" {{ $lesson->status === 'archived' ? 'selected' : '' }}>Arsip</option>
                            </select>
                            <button class="button small secondary" type="submit">Simpan</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endforeach
    </section>
@endforeach
@endsection
