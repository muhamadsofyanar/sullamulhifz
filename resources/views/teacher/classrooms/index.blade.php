@extends('layouts.app',['pageTitle'=>'Kelas Saya'])
@section('content')
<div class="page-head"><div><h1>Kelas dan kelompok saya</h1><p>Pilih kelas, lalu mulai pertemuan. Catat hanya hal yang perlu ditindaklanjuti.</p></div><a class="button secondary" href="{{ route('teacher.assignments.create') }}">+ Buat Tugas</a></div>
<div class="cards-grid">@forelse($assignments as $item)@php($target=$item->schoolClass ?: $item->learningGroup)<a class="class-card" href="{{ $item->class_id?route('teacher.classrooms.class',$item->class_id):route('teacher.classrooms.group',$item->learning_group_id) }}"><span class="eyebrow">{{ $item->program?->name }}</span><h2>{{ $target?->name }}</h2><p>{{ $item->class_id ? $target->activeEnrollments->count() : $target->activeMemberships->count() }} santri</p><span class="button ghost">Buka →</span></a>@empty<div class="card empty">Belum ada penugasan aktif.</div>@endforelse</div>
@endsection
