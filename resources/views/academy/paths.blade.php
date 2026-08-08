@extends('layouts.academy',['pageTitle'=>'Jalur Belajar'])
@section('content')
<div class="academy-page-head">
    <div><span class="eyebrow">LEARNING PATH</span><h1>Belajar dengan arah yang sederhana.</h1><p>Jalur belajar menggabungkan materi, aktivitas, dan latihan Qur’an agar pengguna tidak harus memilih dari terlalu banyak konten sekaligus.</p></div>
</div>
<div class="academy-path-grid">
@forelse($paths as $path)
    @php($p = $progress[$path->id] ?? ['done'=>0,'total'=>0,'percent'=>0])
    @php($locked = $lockedPathIds->contains($path->id))
    <article class="academy-path-card">
        <div class="academy-path-top"><span class="academy-path-phase">{{ strtoupper($path->category ?? 'ACADEMY') }}</span><span>{{ $p['percent'] }}%</span></div>
        <h2>{{ $path->title }}</h2>
        <p>{{ $path->summary }}</p>
        <div class="academy-mini-progress"><span style="width:{{ $p['percent'] }}%"></span></div>
        <div class="academy-path-footer"><small>{{ $p['done'] }} dari {{ $p['total'] }} langkah wajib selesai</small>@if($locked)<span>🔒 Prasyarat</span>@else<a href="{{ route('academy.portal.path',$path) }}">Buka jalur →</a>@endif</div>
    </article>
@empty
    <div class="academy-empty-card"><h2>Jalur belajar belum diterbitkan.</h2><p>Program dan materi Academy tetap dapat diakses dari menu Program.</p></div>
@endforelse
</div>
@endsection
