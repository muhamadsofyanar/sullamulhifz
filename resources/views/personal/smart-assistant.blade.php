{{-- @phase 5.2 Smart Assistant with human review --}}
@extends('layouts.app',['pageTitle'=>'Pendamping Cerdas'])
@section('content')
<div class="v530-page">
    <section class="v530-hero"><div><span class="personal-kicker">FASE 11 · PENDAMPING CERDAS</span><h1>Saran yang membantu, keputusan tetap manusiawi</h1><p>{{ $snapshot['privacy'] }}</p></div><span class="v530-badge">v5.2</span></section>
    <section class="v530-grid v530-grid-3"><article class="card"><span class="eyebrow">LATIHAN 30 HARI</span><strong class="v530-metric">{{ $snapshot['practice_sessions'] }}</strong><p>{{ $snapshot['practice_minutes'] }} menit tercatat</p></article><article class="card"><span class="eyebrow">HARI AKTIF</span><strong class="v530-metric">{{ $snapshot['days_practiced'] }}</strong><p>hari dengan latihan tercatat</p></article><article class="card"><span class="eyebrow">TARGET</span><strong class="v530-metric">{{ $snapshot['goals']->count() }}</strong><p>target Personal aktif</p></article></section>
    <section class="card"><span class="eyebrow">REKOMENDASI LOKAL</span><h2>Langkah yang dapat dipertimbangkan</h2><div class="v530-grid v530-grid-2">@foreach($snapshot['recommendations'] as $recommendation)<article class="v530-card"><span class="eyebrow">{{ strtoupper($recommendation['type']) }}</span><h3>{{ $recommendation['title'] }}</h3><p>{{ $recommendation['body'] }}</p></article>@endforeach</div><div class="personal-guardrail">Mesin lokal tidak memberi nilai, diagnosis, atau keputusan agama. Untuk arahan manusia, kirim draft kepada Ustadz yang sudah terhubung.</div>
    @if($hasActiveMentor)
    <form method="post" action="{{ route('personal.smart-assistant.review-request') }}" class="v530-action">@csrf<button class="button primary" type="submit">Minta review Ustadz</button></form>
    @else
    <div class="v530-action"><a class="button secondary" href="{{ route('mentorship.index') }}">Hubungkan Ustadz Privat dahulu</a></div>
    @endif
    </section>
    <section class="card"><span class="eyebrow">HUMAN REVIEW</span><h2>Riwayat draft</h2><div class="v530-listing">@forelse($drafts as $draft)<article><div><strong>{{ $draft->status === 'pending_review' ? 'Menunggu review Ustadz' : 'Review selesai' }}</strong><small>{{ $draft->created_at->translatedFormat('d M Y H:i') }} · {{ $draft->provider }}/{{ $draft->model }}</small>@if($draft->review?->final_text)<p class="v530-review-text">{{ $draft->review->final_text }}</p>@endif</div><span class="badge status-{{ $draft->status }}">{{ $draft->status }}</span></article>@empty<p class="muted">Belum ada draft yang diminta untuk direview.</p>@endforelse</div></section>
</div>
@endsection
