@extends('layouts.app',['pageTitle'=>'Tugas Anak'])
@section('content')
<div class="page-head"><div><h1>Tugas anak</h1><p>Instruksi, bukti, dan tanggapan guru tersimpan dalam satu alur.</p></div></div>
<div class="cards-list">@forelse($recipients as $recipient)<a class="item-card" href="{{ route('guardian.tasks.show',$recipient) }}"><div><strong>{{ $recipient->assignment->title }}</strong><small>{{ $recipient->student->full_name }} · {{ $recipient->assignment->due_at?->format('d M Y H:i') ?? 'Tanpa tenggat' }}</small></div><span class="badge">{{ str_replace('_',' ',$recipient->status) }}</span></a>@empty<div class="card empty">Belum ada tugas.</div>@endforelse</div>{{ $recipients->links() }}
@endsection
