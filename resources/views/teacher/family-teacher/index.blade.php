@extends('layouts.app',['pageTitle'=>'Keluarga & Kompetensi Guru'])
@section('content')
<div class="page-head"><div><span class="eyebrow">FAMILY & TEACHER ECOSYSTEM</span><h1>Satu langkah keluarga, satu refleksi guru.</h1><p>Pilih aktivitas yang bermakna. Catat praktik dan refleksi tanpa membandingkan anak maupun guru.</p></div><a class="button secondary" href="{{ route('teacher.academy.index') }}">Rekomendasi Academy</a></div>

<div class="grid two">
<section class="card"><h2>Aktivitas keluarga baru</h2><form class="stack" method="post" action="{{ route('teacher.family-learning.activities.store') }}">@csrf
<label>Santri<select name="student_id" required><option value="">Pilih santri yang Anda ampu</option>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->full_name }}</option>@endforeach</select></label>
<div class="form-grid"><label>Jenis<select name="activity_type" required><option value="practice">Latihan bersama</option><option value="conversation">Percakapan keluarga</option><option value="habit">Kebiasaan kecil</option><option value="reflection">Refleksi</option><option value="project">Proyek keluarga</option></select></label><label>Tenggat (opsional)<input type="datetime-local" name="due_at"></label></div>
<label>Judul<input name="title" required maxlength="180" placeholder="Contoh: Murajaah 10 menit bersama"></label>
<label>Instruksi<textarea name="instructions" rows="5" required placeholder="Tulis satu langkah yang realistis dan jelas."></textarea></label>
<label>Materi Parent Academy (opsional)<select name="academy_lesson_id"><option value="">Tanpa materi terhubung</option>@foreach($parentLessons as $lesson)<option value="{{ $lesson->id }}">{{ $lesson->module->program->title }} — {{ $lesson->title }}</option>@endforeach</select></label>
<button class="button primary">Kirim ke keluarga</button></form></section>
<section class="card"><h2>Guardrail pendampingan</h2><ul class="principles"><li>Tulis kebutuhan atau perilaku yang dapat diamati, bukan label anak.</li><li>Jangan menjadikan tipe kepribadian/STIFIn sebagai batas kemampuan.</li><li>Satu aktivitas ringan yang selesai lebih baik daripada beban panjang.</li><li>Refleksi wali bukan nilai; gunakan untuk memahami kondisi rumah.</li></ul></section>
</div>

<section class="card"><div class="section-head"><h2>Aktivitas yang Anda kirim</h2><span class="badge">{{ $activities->count() }}</span></div><div class="cards-list">@forelse($activities as $item)<div class="list-row"><div style="width:100%"><strong>{{ $item->student->full_name }} · {{ $item->title }}</strong><small>{{ str_replace('_',' ',$item->activity_type) }} · {{ $item->status }} @if($item->due_at) · target {{ $item->due_at->format('d M Y H:i') }} @endif</small><p>{{ $item->instructions }}</p>@if($item->guardian_reflection)<p><b>Refleksi wali:</b> {{ $item->guardian_reflection }}</p>@endif
@if($item->status==='completed')<form class="stack" method="post" action="{{ route('teacher.family-learning.activities.review',$item) }}">@csrf @method('put')<label>Tindak lanjut guru<textarea name="teacher_follow_up" rows="2" placeholder="Apa langkah kecil berikutnya?"></textarea></label><button class="button primary">Review aktivitas</button></form>@elseif($item->teacher_follow_up)<p><b>Tindak lanjut:</b> {{ $item->teacher_follow_up }}</p>@endif</div></div>@empty<p class="empty">Belum ada aktivitas keluarga.</p>@endforelse</div></section>

<section class="card"><div class="section-head"><h2>Kompetensi & pelatihan saya</h2><span class="badge">{{ $competencies->count() }}</span></div><div class="cards-list">@forelse($competencies as $item)@php
    $state=$item->progress->first();
@endphp
<div class="list-row"><div style="width:100%"><strong>{{ $item->title }}</strong><small>{{ str_replace('_',' ',$item->category) }} · {{ str_replace('_',' ',$state?->status ?? 'belum dimulai') }}</small><p>{{ $item->description }}</p>@if($item->lesson)<a href="{{ route('academy.portal.lesson',$item->lesson) }}">Buka materi Teacher Academy →</a>@endif
@if(!in_array($state?->status,['demonstrated'],true))<form class="stack" method="post" action="{{ route('teacher.family-learning.competencies.submit',$item) }}">@csrf @method('put')<label>Refleksi<textarea name="reflection" rows="3">{{ $state?->reflection }}</textarea></label><label>Bukti/praktik naratif<textarea name="evidence_note" rows="2">{{ $state?->evidence_note }}</textarea></label><div class="inline-actions"><button class="button secondary" name="status" value="in_progress">Simpan proses</button><button class="button primary" name="status" value="reflection_submitted">Kirim untuk review</button></div></form>@endif
@if($state?->review_note)<p><b>Catatan reviewer:</b> {{ $state->review_note }}</p>@endif</div></div>@empty<p class="empty">Belum ada kompetensi aktif dari lembaga.</p>@endforelse</div></section>
@endsection
