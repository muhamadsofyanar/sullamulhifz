@extends('layouts.app',['pageTitle'=>'Perkembangan Anak'])
@section('content')
<div class="page-head">
    <div><span class="eyebrow">KELUARGA</span><h1>Perkembangan Anak</h1><p>Pilih anak untuk melihat perjalanan belajar, Tahfizh, Murāja‘ah, dan pendampingan yang telah dicatat guru.</p></div>
</div>

<section class="grid two">
@forelse($students as $student)
    <a class="card family-child-card" href="{{ route('guardian.children.show', $student) }}">
        <div class="family-child-avatar">{{ strtoupper(mb_substr($student->full_name,0,1)) }}</div>
        <div>
            <small>Anak saya</small>
            <strong>{{ $student->full_name }}</strong>
            <span>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Belum ditempatkan' }}</span>
        </div>
        <b>→</b>
    </a>
@empty
    <div class="card"><p class="empty">Belum ada data anak yang terhubung. Hubungi admin.</p></div>
@endforelse
</section>
@endsection
