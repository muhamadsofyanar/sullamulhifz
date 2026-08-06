@extends('layouts.app',['pageTitle'=>'Beranda Orang Tua'])
@section('content')
<div class="hero"><div><span class="eyebrow">ORANG TUA/WALI</span><h1>Assalamu‘alaikum, {{ $guardian->full_name }}</h1><p>Temani proses anak dengan langkah sederhana yang bermakna.</p></div></div>
<section class="card"><div class="section-head"><h2>Anak Saya</h2></div><div class="cards-list">@forelse($students as $student)
<a class="item-card" href="{{ route('guardian.children.show',$student) }}"><div><strong>{{ $student->full_name }}</strong><small>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Belum ditempatkan' }}</small></div><span>→</span></a>
@empty<p class="empty">Belum ada data anak yang terhubung. Hubungi admin.</p>@endforelse</div></section>
<div class="grid two"><section class="card"><div class="section-head"><h2>Tugas aktif</h2><a href="{{ route('guardian.tasks.index') }}">Lihat semua</a></div>
@forelse($activeTasks as $task)<a class="list-row" href="{{ route('guardian.tasks.show',$task) }}"><div><strong>{{ $task->assignment->title }}</strong><small>{{ $task->student->full_name }} · {{ $task->assignment->due_at?->format('d M H:i') ?? 'Tanpa tenggat' }}</small></div><span class="badge">{{ str_replace('_',' ',$task->status) }}</span></a>@empty<p class="empty">Tidak ada tugas aktif.</p>@endforelse</section>
<section class="card"><div class="section-head"><h2>Pembinaan Jumat</h2><a href="{{ route('feed.friday') }}">Arsip</a></div>@forelse($fridaySessions as $session)<div class="list-row"><div><strong>{{ $session->title }}</strong><small>{{ $session->session_date->format('d M Y') }}</small></div></div>@empty<p class="empty">Belum ada materi terbaru.</p>@endforelse</section></div>
@endsection
