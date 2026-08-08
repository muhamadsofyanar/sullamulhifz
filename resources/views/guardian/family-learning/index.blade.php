@extends('layouts.app',['pageTitle'=>'Aktivitas Keluarga'])
@section('content')
<div class="page-head"><div><span class="eyebrow">AKTIVITAS KELUARGA</span><h1>Langkah kecil yang dikerjakan bersama.</h1><p>Aktivitas ini adalah ajakan pendampingan dari guru. Refleksi membantu guru memahami pengalaman di rumah dan bukan nilai untuk anak atau keluarga.</p></div><a class="button secondary" href="{{ route('academy.portal.index') }}">Parent Academy</a></div>

<section class="card"><div class="section-head"><h2>Aktivitas saya</h2><span class="badge">{{ $activities->whereIn('status',['assigned','in_progress'])->count() }} aktif</span></div><div class="cards-list">
@forelse($activities as $item)<div class="list-row"><div style="width:100%"><strong>{{ $item->student->full_name }} · {{ $item->title }}</strong><small>{{ str_replace('_',' ',$item->activity_type) }} · {{ $item->status }} @if($item->due_at) · target {{ $item->due_at->format('d M Y H:i') }} @endif</small><p>{{ $item->instructions }}</p>
@if($item->lesson)<p><a href="{{ route('academy.portal.lesson',$item->lesson) }}">Buka materi pendamping: {{ $item->lesson->title }} →</a></p>@endif
@if(in_array($item->status,['assigned','in_progress'],true))
<form class="stack" method="post" action="{{ route('guardian.family-learning.complete',$item) }}">@csrf @method('put')<label>Refleksi singkat setelah mencoba<textarea name="guardian_reflection" rows="4" required placeholder="Apa yang berjalan baik? Apa yang terasa sulit? Apa yang anak butuhkan?"></textarea></label><button class="button primary">Tandai selesai & kirim refleksi</button></form>
@else
    @if($item->guardian_reflection)
    <p><b>Refleksi Anda:</b> {{ $item->guardian_reflection }}</p>
    @endif
    @if($item->teacher_follow_up)
    <p><b>Tindak lanjut guru:</b> {{ $item->teacher_follow_up }}</p>
    @endif
@endif
</div></div>@empty<p class="empty">Belum ada aktivitas keluarga dari guru. Tidak ada yang perlu dikerjakan saat ini.</p>@endforelse
</div></section>
@endsection
