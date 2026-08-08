@extends('layouts.app',['pageTitle'=>'Ekosistem Keluarga & Guru'])
@section('content')
<div class="page-head"><div><span class="eyebrow">FASE 6 · FAMILY & TEACHER ECOSYSTEM</span><h1>Keluarga bertumbuh, guru terus belajar.</h1><p>Aktivitas keluarga dan kompetensi guru dicatat sebagai perjalanan pendampingan. Tidak ada skor anak, leaderboard, atau ranking guru.</p></div><a class="button secondary" href="{{ route('admin.academy.index') }}">Kelola Academy</a></div>

<div class="grid two">
<section class="card">
<h2>Tambah kompetensi guru</h2>
<form class="stack" method="post" action="{{ route('admin.family-teacher.competencies.store') }}">@csrf
<div class="form-grid"><label>Kode<input name="code" placeholder="contoh: komunikasi-keluarga"></label><label>Kategori<select name="category" required><option value="pedagogy">Pedagogi</option><option value="quran">Pembelajaran Al-Qur’an</option><option value="family_communication">Komunikasi Keluarga</option><option value="child_safeguarding">Perlindungan Anak</option><option value="professional">Profesional</option></select></label></div>
<label>Judul<input name="title" required maxlength="180" placeholder="Contoh: Komunikasi hangat dengan wali"></label>
<label>Deskripsi<textarea name="description" rows="4" placeholder="Perilaku/kemampuan yang ingin dikembangkan."></textarea></label>
<label>Panduan bukti/refleksi<textarea name="evidence_guidance" rows="3" placeholder="Contoh bukti naratif atau praktik yang dapat direfleksikan."></textarea></label>
<label>Materi Teacher Academy (opsional)<select name="academy_lesson_id"><option value="">Tanpa materi terhubung</option>@foreach($teacherLessons as $lesson)<option value="{{ $lesson->id }}">{{ $lesson->module->program->title }} — {{ $lesson->title }}</option>@endforeach</select></label>
<input type="hidden" name="status" value="active"><button class="button primary">Tambah kompetensi</button>
</form>
</section>
<section class="card"><h2>Prinsip Fase 6</h2><ul class="principles"><li>Aktivitas keluarga harus spesifik, ringan, dan relevan dengan kebutuhan nyata.</li><li>Refleksi digunakan untuk tindak lanjut, bukan menilai kualitas keluarga.</li><li>Kompetensi guru dicatat sebagai proses belajar dan praktik, bukan ranking.</li><li>STIFIn, bila digunakan, hanya informasi tambahan; bukan label kemampuan, martabat, atau batas perkembangan anak.</li><li>Keputusan pendidikan tetap berada pada manusia: guru, keluarga, dan pengelola.</li></ul></section>
</div>

<section class="card"><div class="section-head"><h2>Kompetensi aktif</h2><span class="badge">{{ $competencies->where('status','active')->count() }}</span></div>
<div class="cards-list">@forelse($competencies as $item)<div class="list-row"><div><strong>{{ $item->title }}</strong><small>{{ $item->code }} · {{ str_replace('_',' ',$item->category) }} · {{ $item->status }}</small><p>{{ $item->description }}</p>@if($item->lesson)<small>Teacher Academy: {{ $item->lesson->title }}</small>@endif</div></div>@empty<p class="empty">Belum ada kompetensi. Tambahkan satu kompetensi untuk memulai uji Fase 6.</p>@endforelse</div>
</section>

<section class="card"><div class="section-head"><h2>Refleksi guru menunggu review</h2><span class="badge">{{ $progress->where('status','reflection_submitted')->count() }}</span></div>
<div class="cards-list">@forelse($progress as $row)<div class="list-row"><div style="width:100%"><strong>{{ $row->teacher->full_name }} · {{ $row->competency->title }}</strong><small>{{ str_replace('_',' ',$row->status) }} @if($row->submitted_at) · {{ $row->submitted_at->format('d M Y H:i') }} @endif</small>@if($row->reflection)<p><b>Refleksi:</b> {{ $row->reflection }}</p>@endif @if($row->evidence_note)<p><b>Bukti/praktik:</b> {{ $row->evidence_note }}</p>@endif
@if($row->status==='reflection_submitted')<form method="post" action="{{ route('admin.family-teacher.progress.review',$row) }}" class="stack">@csrf @method('put')<label>Catatan review<textarea name="review_note" rows="2"></textarea></label><div class="inline-actions"><button class="button secondary" name="status" value="needs_follow_up">Perlu tindak lanjut</button><button class="button primary" name="status" value="demonstrated">Praktik terkonfirmasi</button></div></form>@elseif($row->review_note)<p><b>Review:</b> {{ $row->review_note }}</p>@endif</div></div>@empty<p class="empty">Belum ada refleksi guru.</p>@endforelse</div>
</section>

<section class="card"><div class="section-head"><h2>Aktivitas keluarga terbaru</h2><span class="badge">{{ $activities->count() }}</span></div><div class="cards-list">@forelse($activities as $item)<div class="list-row"><div><strong>{{ $item->student->full_name }} · {{ $item->title }}</strong><small>{{ str_replace('_',' ',$item->activity_type) }} · {{ $item->status }} · oleh {{ $item->creator->name }}</small>@if($item->guardian_reflection)<p>{{ $item->guardian_reflection }}</p>@endif</div></div>@empty<p class="empty">Belum ada aktivitas keluarga.</p>@endforelse</div></section>
@endsection
