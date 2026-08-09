@extends('layouts.app',['pageTitle'=>'Community Terbatas'])
@section('content')
<div class="personal-page personal-v4-page">
    <section class="personal-v4-hero compact"><div><span class="personal-kicker">COMMUNITY TERBATAS</span><h1>Berbagi tanpa membuka ruang privat</h1><p>Setiap tulisan diperiksa moderator. Jurnal, target, dan check-in Personal tidak pernah dibagikan otomatis.</p></div></section>
    <section class="personal-v4-grid">
        <div class="card">
            <span class="eyebrow">KIRIM TULISAN</span><h2>Bagikan pengalaman yang bermanfaat</h2>
            @if($spaces->isEmpty())<p class="muted">Belum ada ruang community aktif. Hubungi pengelola program.</p>@else
            <form method="post" action="{{ route('personal.community.store') }}" class="stack">@csrf
                <label>Ruang<select name="community_space_id" required>@foreach($spaces as $space)<option value="{{ $space->id }}">{{ $space->name }}</option>@endforeach</select></label>
                <label>Tulisan<textarea name="body" rows="6" maxlength="3000" required placeholder="Bagikan pembelajaran, pertanyaan, atau pengalaman yang relevan..."></textarea></label>
                <button class="button primary" type="submit">Kirim untuk moderasi</button>
            </form>@endif
        </div>
        <div class="card"><span class="eyebrow">MENUNGGU MODERASI</span><h2>Tulisan saya</h2><div class="stack">@forelse($myPendingPosts as $post)<article class="v4-pending-card"><small>{{ $post->space?->name }} · {{ $post->created_at->diffForHumans() }}</small><p>{{ $post->body }}</p><span class="badge">Menunggu review</span></article>@empty<p class="muted">Tidak ada tulisan yang menunggu.</p>@endforelse</div></div>
    </section>
    <section class="card"><div class="section-head"><div><span class="eyebrow">TERBIT</span><h2>Dari community</h2></div></div><div class="v4-community-feed">@forelse($posts as $post)<article><div><strong>{{ $post->creator?->name }}</strong><small>{{ $post->space?->name }} · {{ $post->published_at?->diffForHumans() }}</small></div><p>{{ $post->body }}</p></article>@empty<p class="muted">Belum ada tulisan yang diterbitkan.</p>@endforelse</div></section>
</div>
@endsection
