@extends('layouts.app',['pageTitle'=>'Beranda Guru'])
@section('content')
<div class="hero"><div><span class="eyebrow">GURU</span><h1>Assalamu‘alaikum, {{ $teacher->nickname ?: $teacher->full_name }}</h1><p>{{ now()->translatedFormat('l, d F Y') }}</p></div><a class="button primary" href="{{ route('teacher.classrooms.index') }}">Buka Kelas Saya</a></div>
<div class="stats-grid three"><div class="stat-card"><span>Penugasan aktif</span><strong>{{ $teachingAssignments->count() }}</strong></div><div class="stat-card"><span>Bukti menunggu</span><strong>{{ $pendingSubmissions }}</strong></div><div class="stat-card"><span>Percakapan aktif</span><strong>{{ $activeThreads }}</strong></div></div>
<section class="card"><div class="section-head"><h2>Kelas dan kelompok</h2><a href="{{ route('teacher.assignments.create') }}">+ Buat tugas</a></div>
<div class="cards-list">@forelse($teachingAssignments as $item)
@php($target=$item->schoolClass ?: $item->learningGroup)
<a class="item-card" href="{{ $item->class_id ? route('teacher.classrooms.class',$item->class_id) : route('teacher.classrooms.group',$item->learning_group_id) }}"><div><strong>{{ $target?->name }}</strong><small>{{ $item->program?->name }}</small></div><span>→</span></a>
@empty<p class="empty">Belum ada penugasan aktif. Hubungi admin lembaga.</p>@endforelse</div></section>
@endsection
