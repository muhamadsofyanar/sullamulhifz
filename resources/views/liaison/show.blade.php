@extends('layouts.app',['pageTitle'=>'Buku Penghubung'])
@section('content')
<div class="page-head"><div><span class="eyebrow">{{ str_replace('_',' ',$thread->category) }}</span><h1>{{ $thread->subject }}</h1><p>{{ $thread->student->full_name }}</p></div><a class="button ghost" href="{{ route('liaison.index') }}">Kembali</a></div>
<section class="card conversation">@foreach($thread->messages as $message)<div class="message {{ $message->sender_user_id===auth()->id()?'mine':'' }}"><div class="message-meta"><strong>{{ $message->sender->name }}</strong><span>{{ $message->created_at->format('d M Y H:i') }}</span></div><p>{!! nl2br(e($message->message)) !!}</p></div>@endforeach<form class="reply-box" method="post" action="{{ route('liaison.reply',$thread) }}">@csrf<textarea name="message" required placeholder="Tulis tanggapan..."></textarea><button class="button primary">Kirim</button></form></section>
@endsection
