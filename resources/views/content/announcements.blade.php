@extends('layouts.app',['pageTitle'=>'Pengumuman'])
@section('content')
<div class="page-head"><div><h1>Pengumuman</h1><p>Informasi lembaga dan kelas yang relevan dengan akun Anda.</p></div></div>
<div class="cards-list">@forelse($items as $item)<article class="card content-card"><span class="eyebrow">{{ $item->schoolClass?->name ?? 'Seluruh Lembaga' }}</span><h2>{{ $item->title }}</h2><small>{{ $item->publish_at?->format('d M Y H:i') }}</small><div class="prose">{!! nl2br(e($item->content)) !!}</div></article>@empty<div class="card empty">Belum ada pengumuman.</div>@endforelse</div>{{ $items->links() }}
@endsection
