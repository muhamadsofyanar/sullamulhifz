@extends('layouts.app')
@section('content')
@php($statusLabels = ['pending'=>'Menunggu review','revision'=>'Perlu perbaikan','verified'=>'Terverifikasi','rejected'=>'Belum diterima'])
<div class="guided-page">
    <section class="page-heading"><div><span class="eyebrow">AMANAH REVIEW</span><h1>Setoran Al-Qur’an Online</h1><p class="muted">Periksa bukti yang memang dikirim peserta. Jurnal dan target Personal lainnya tetap di luar ruang review.</p></div></section>
    @forelse($submissions as $submission)
    <article class="card guided-review-card">
        <div class="guided-enrollment-head">
            <div><span class="guided-chip">{{ $submission->enrollment->program->title }}</span><h2>{{ $submission->student?->full_name }}</h2><p>{{ $submission->surah?->name_latin }} ayat {{ $submission->start_verse }}–{{ $submission->end_verse }} · {{ ucfirst($submission->submission_type) }}</p><small>{{ $submission->submitted_at?->translatedFormat('d M Y H:i') }}</small></div>
            <span class="guided-status status-{{ $submission->review_status }}">{{ $statusLabels[$submission->review_status] ?? $submission->review_status }}</span>
        </div>
        @if($submission->audio_media_asset_id)<div class="guided-review-audio"><strong>Dengarkan setoran</strong><audio controls preload="none" src="{{ route('media.guided-submission', $submission) }}"></audio></div>@endif
        @if($submission->evidence_text)<blockquote>{{ $submission->evidence_text }}</blockquote>@endif
        @if($submission->learner_notes)<p><strong>Catatan peserta:</strong> {{ $submission->learner_notes }}</p>@endif
        <form method="post" enctype="multipart/form-data" action="{{ route('guided-review.review', $submission) }}" class="personal-form-grid compact">@csrf @method('put')
            <label>Keputusan<select name="decision" required><option value="verified">Terverifikasi</option><option value="revision">Perlu perbaikan</option><option value="rejected">Belum diterima</option></select></label>
            <label>Voice note feedback<input type="file" name="feedback_audio" accept="audio/*,.mp3,.m4a,.wav,.ogg,.opus,.webm" capture></label>
            <label class="span-all">Feedback teks<textarea name="feedback_text" rows="3" maxlength="5000" placeholder="Sebutkan bagian yang sudah baik, bagian yang perlu dibenahi, dan langkah berikutnya."></textarea></label>
            <div class="span-all"><button class="button primary">Simpan review</button></div>
        </form>
        @if($submission->reviews->isNotEmpty())<details class="personal-disclosure"><summary>Riwayat review ({{ $submission->reviews->count() }})</summary>@foreach($submission->reviews as $review)<div class="guided-feedback"><strong>{{ $review->reviewer?->name }} · {{ ucfirst($review->decision) }}</strong><p>{{ $review->feedback_text }}</p>@if($review->feedback_audio_media_asset_id)<audio controls preload="none" src="{{ route('media.guided-feedback', $review) }}"></audio>@endif</div>@endforeach</details>@endif
    </article>
    @empty
        <div class="card empty-state"><p>Belum ada setoran pada program yang ditugaskan kepada Anda.</p></div>
    @endforelse
    {{ $submissions->links() }}
</div>
@endsection
