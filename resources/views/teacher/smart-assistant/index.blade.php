{{-- @phase 5.2 Smart Assistant with human review --}}
@extends('layouts.app',['pageTitle'=>'Review Pendamping Cerdas'])
@section('content')
<div class="v530-page">
    <section class="v530-hero"><div><span class="personal-kicker">FASE 11 · HUMAN REVIEW</span><h1>Review draft Pendamping Cerdas</h1><p>Hanya draft dari peserta dengan hubungan Ustadz Privat aktif yang muncul. Ustadz dapat menerima, mengubah, atau menolak.</p></div><span class="v530-badge">v5.2</span></section>
    @forelse($drafts as $draft)
    <section class="card">
        <div class="v530-row"><div><span class="eyebrow">PESERTA</span><h2>{{ $draft->creator?->name }}</h2><small>{{ $draft->created_at->translatedFormat('d M Y H:i') }}</small></div><span class="badge status-{{ $draft->status }}">{{ $draft->status }}</span></div>
        <pre class="v530-draft">{{ $draft->draft_text }}</pre>
        @if(!$draft->review)
        <form method="post" action="{{ route('teacher.smart-assistant.review',$draft) }}" class="stack">@csrf @method('PUT')
            <label>Keputusan<select name="decision" required><option value="accepted">Terima draft</option><option value="modified">Ubah lalu setujui</option><option value="rejected">Tolak</option></select></label>
            <label>Teks final jika diubah<textarea name="final_text" rows="7">{{ $draft->draft_text }}</textarea></label>
            <label>Catatan review<textarea name="review_note" rows="3"></textarea></label>
            <button class="button primary" type="submit">Simpan review</button>
        </form>
        @else
        <div class="personal-guardrail">Review: {{ $draft->review->decision }} · {{ $draft->review->review_note ?: 'tanpa catatan tambahan' }}</div>
        @endif
    </section>
    @empty
    <section class="card"><h2>Belum ada draft untuk direview</h2><p class="muted">Draft akan muncul setelah peserta yang Anda dampingi meminta human review.</p></section>
    @endforelse
</div>
@endsection
