@extends('layouts.app',['pageTitle'=>'Pengumuman'])
@section('content')
<div class="page-head"><div><h1>Pengumuman</h1><p>Informasi lembaga, kelas, dan kelompok yang relevan dengan akun Anda.</p></div></div>
<div class="cards-list">@forelse($items as $item)<article class="card content-card {{ $item->is_pinned ? 'pinned-card' : '' }}"><div class="item-card static"><span><span class="eyebrow">{{ $item->schoolClass?->name ?? $item->learningGroup?->name ?? 'Seluruh Lembaga' }}</span><h2>{{ $item->title }}</h2></span>@if($item->is_pinned)<span class="badge">Penting</span>@endif</div><small>{{ $item->publish_at?->format('d M Y H:i') }}</small><div class="prose">{!! nl2br(e($item->content)) !!}</div>@if($item->attachment_media_id || $item->attachment_path)<p><a class="button secondary small" href="{{ route('media.announcement',$item) }}" target="_blank" rel="noopener">Buka lampiran</a></p>@endif
@if($item->require_acknowledgement)
    @if($item->reads->first()?->acknowledged_at)<span class="badge">Sudah dikonfirmasi</span>@else<form method="post" action="{{ route('feed.announcements.acknowledge',$item) }}">@csrf<button class="button primary small">Konfirmasi sudah membaca</button></form>@endif
@endif</article>@empty<div class="card empty">Belum ada pengumuman.</div>@endforelse</div>{{ $items->links() }}
@endsection
