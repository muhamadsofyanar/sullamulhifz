@extends('layouts.app',['pageTitle'=>'Academy & Keluarga'])
@section('content')
<div class="page-head"><div><span class="eyebrow">KEMITRAAN KELUARGA</span><h1>Rekomendasikan satu materi yang tepat.</h1><p>Jangan membanjiri orang tua dengan banyak materi. Pilih yang paling relevan dengan kebutuhan santri saat ini.</p></div><a class="button secondary" href="{{ route('academy.portal.index') }}">Academy Guru</a></div>
<div class="grid two">
<section class="card"><h2>Rekomendasi untuk wali</h2><form method="post" action="{{ route('teacher.academy.recommendations.store') }}" class="stack">@csrf
<label>Santri<select name="student_id" required><option value="">Pilih santri</option>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->full_name }}</option>@endforeach</select></label>
<label>Materi Parent Academy<select name="academy_lesson_id" required><option value="">Pilih materi</option>@foreach($lessons as $lesson)<option value="{{ $lesson->id }}">{{ $lesson->module->program->title }} — {{ $lesson->title }}</option>@endforeach</select></label>
<label>Pesan singkat<textarea name="message" placeholder="Contoh: Materi ini cocok untuk mendampingi target murajaah pekan ini."></textarea></label>
<button class="button primary" type="submit">Kirim rekomendasi</button></form></section>
<section class="card"><h2>Prinsip rekomendasi</h2><ul class="principles"><li>Relevan dengan kebutuhan anak atau keluarga.</li><li>Satu materi yang dapat dilakukan lebih baik daripada daftar panjang.</li><li>Gunakan bahasa hangat, tanpa label negatif.</li><li>Buku Penghubung tetap digunakan untuk catatan pribadi anak.</li></ul></section>
</div>
<section class="card"><div class="section-head"><h2>Rekomendasi terakhir</h2><span class="badge">{{ $recommendations->count() }}</span></div><div class="cards-list">@forelse($recommendations as $item)<div class="list-row"><div><strong>{{ $item->student->full_name }} · {{ $item->lesson->title }}</strong><small>{{ $item->recommended_at?->format('d M Y H:i') }} · {{ $item->status }}</small><p>{{ $item->message }}</p></div></div>@empty<p class="empty">Belum ada rekomendasi.</p>@endforelse</div></section>
@endsection
