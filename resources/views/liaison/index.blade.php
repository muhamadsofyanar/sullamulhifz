@extends('layouts.app',['pageTitle'=>'Buku Penghubung'])
@section('content')
<div class="page-head"><div><h1>Buku penghubung</h1><p>Komunikasi pribadi guru dan wali yang terhubung dengan satu santri.</p></div><a class="button primary" href="{{ route('liaison.create') }}">+ Catatan Baru</a></div>
<div class="cards-list">@forelse($threads as $thread)<a class="item-card" href="{{ route('liaison.show',$thread) }}"><div><span class="eyebrow">{{ str_replace('_',' ',$thread->category) }}</span><strong>{{ $thread->subject }}</strong><small>{{ $thread->student->full_name }} · {{ $thread->last_message_at?->format('d M Y H:i') }}</small></div><span class="badge">{{ $thread->status }}</span></a>@empty<div class="card empty">Belum ada percakapan.</div>@endforelse</div>{{ $threads->links() }}
@endsection
