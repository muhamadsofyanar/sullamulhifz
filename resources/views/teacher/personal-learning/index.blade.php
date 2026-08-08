@extends('layouts.app')
@section('title','Personalisasi Belajar')
@section('content')
<div class="page-heading"><div><span class="eyebrow">FASE 7 · PERSONAL LEARNING SYSTEM</span><h1>Personalisasi berbasis evidence</h1><p>Observasi dan progres nyata membantu menyusun draf. Guru tetap menentukan keputusan akhir.</p></div></div>

<section class="card">
    <div class="section-head"><div><h2>Pilih santri</h2><p class="hint">Hanya santri dalam penugasan Anda yang tersedia.</p></div></div>
    <form method="get" action="{{ route('teacher.personal-learning.index') }}" class="form-grid">
        <label>Santri<select name="student_id" required><option value="">Pilih santri yang Anda ampu</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected($selected?->id===$student->id)>{{ $student->full_name }}</option>@endforeach</select></label>
        <div class="inline-actions" style="align-self:end"><button class="button primary">Buka profil personalisasi</button></div>
    </form>
</section>

@if($selected)
<section class="card">
    <div class="section-head"><div><h2>1 · Evidence untuk {{ $selected->full_name }}</h2><p class="hint">Catat respons yang terlihat. Hindari label kepribadian atau kesimpulan permanen.</p></div><span class="badge">{{ $observations->count() }} observasi</span></div>
    <form method="post" action="{{ route('teacher.learning-plan.observations.store') }}" class="stack">@csrf
        <input type="hidden" name="student_id" value="{{ $selected->id }}">
        <div class="form-grid"><label>Kategori<select name="category"><option value="learning_method">Metode belajar</option><option value="readiness">Kesiapan</option><option value="focus">Fokus</option><option value="communication">Komunikasi</option><option value="family_support">Dukungan keluarga</option></select></label><label>Metode/hal yang dicoba<input name="method_name" required placeholder="Contoh: pengulangan audio 5×"></label></div>
        <label>Konteks<input name="context" placeholder="Contoh: sebelum setoran sore"></label>
        <label>Respons nyata<textarea name="response" rows="3" placeholder="Apa yang benar-benar terlihat atau terdengar?"></textarea></label>
        <div class="form-grid"><label>Efektivitas<select name="effectiveness"><option value="needs_more_observation">Perlu observasi lagi</option><option value="helpful">Membantu</option><option value="partly_helpful">Sebagian membantu</option><option value="not_yet_helpful">Belum membantu</option></select></label><label>Catatan tambahan<input name="notes"></label></div>
        <button class="button secondary">Simpan observasi</button>
    </form>
    @if($observations->isNotEmpty())<div class="cards-list" style="margin-top:18px">@foreach($observations->take(5) as $item)<div class="list-row"><div><strong>{{ $item->method_name }}</strong><small>{{ str_replace('_',' ',$item->effectiveness ?: 'belum dinilai') }} · {{ $item->observed_at?->format('d M Y H:i') }}</small>@if($item->response)<p>{{ $item->response }}</p>@endif</div></div>@endforeach</div>@endif
</section>

<section class="card">
    <div class="section-head"><div><h2>2 · Draf rekomendasi</h2><p class="hint">Sistem hanya membaca evidence observasi, Tahfizh, dan Murāja‘ah. STIFIn tidak digunakan sebagai input rekomendasi.</p></div></div>
    <form method="post" action="{{ route('teacher.personal-learning.recommendations.generate',$selected) }}">@csrf<button class="button primary">Susun rekomendasi dari evidence</button></form>
</section>

<section class="card">
    <div class="section-head"><div><h2>3 · Review guru</h2><p class="hint">Terima, ubah, atau tolak. Tidak ada rekomendasi yang berlaku otomatis.</p></div><span class="badge">{{ $insights->where('status','pending_review')->count() }} menunggu review</span></div>
    <div class="cards-list">@forelse($insights as $insight)<div class="list-row"><div style="width:100%"><strong>{{ $insight->title }}</strong><small>{{ str_replace('_',' ',$insight->status) }} · {{ $insight->generated_at?->format('d M Y H:i') }}</small><p>{{ $insight->summary }}</p>
        @if($insight->recommendationReview)
            <p><b>Keputusan guru:</b> {{ str_replace('_',' ',$insight->recommendationReview->decision) }}</p>
            @if($insight->recommendationReview->final_recommendation)<p><b>Rekomendasi final:</b> {{ $insight->recommendationReview->final_recommendation }}</p>@endif
            @if($insight->recommendationReview->review_note)<p><b>Catatan review:</b> {{ $insight->recommendationReview->review_note }}</p>@endif
        @else
            <form method="post" action="{{ route('teacher.personal-learning.recommendations.review',$insight) }}" class="stack">@csrf @method('put')
                <label>Jika diubah<textarea name="final_recommendation" rows="3" placeholder="Isi hanya bila memilih Ubah"></textarea></label>
                <label>Catatan keputusan<textarea name="review_note" rows="2" placeholder="Alasan singkat, terutama bila diubah atau ditolak"></textarea></label>
                <div class="inline-actions"><button class="button primary" name="decision" value="accepted">Terima</button><button class="button secondary" name="decision" value="modified">Ubah</button><button class="button secondary" name="decision" value="rejected">Tolak</button></div>
            </form>
        @endif
    </div></div>@empty<p class="empty">Belum ada draf rekomendasi untuk santri ini.</p>@endforelse</div>
</section>
@endif
@endsection
