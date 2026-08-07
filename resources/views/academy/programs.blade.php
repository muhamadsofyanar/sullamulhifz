@extends($academyLayout ?? 'layouts.app',['pageTitle'=>'Program Academy'])
@section('content')
@php
$trackLabels=[
    'parent'=>'Parent Academy','teacher'=>'Teacher Academy','stifin'=>'STIFIn Learning','stifin-parenting'=>'STIFIn Parenting',
    'quran-life'=>'Al-Qur’an & Kehidupan','child-education'=>'Pendidikan Anak','teacher-development'=>'Pengembangan Guru',
    'character-talent'=>'Karakter & Bakat'
];
@endphp
<div class="academy-section-heading"><div><span class="eyebrow">PROGRAM</span><h1>E-course Sullamul Ḥifẓ Academy</h1><p>Pilih jalur belajar yang sesuai dengan kebutuhan Anda saat ini.</p></div><span class="badge">{{ $allPrograms->count() }} program</span></div>
@if($tracks->isNotEmpty())
<nav class="academy-filter-chips" aria-label="Filter program Academy">
    <a href="{{ route($academyRoutePrefix.'programs') }}" class="{{ $selectedTrack===''?'active':'' }}">Semua</a>
    @foreach($tracks as $track)
        <a href="{{ route($academyRoutePrefix.'programs',['track'=>$track]) }}" class="{{ $selectedTrack===$track?'active':'' }}">{{ $trackLabels[$track] ?? ucwords(str_replace('-',' ',$track)) }}</a>
    @endforeach
</nav>
@endif
<section class="academy-program-grid">
@forelse($programs as $program)
    @php($lessons=$program->modules->flatMap->lessons)
    @php($done=$lessons->whereIn('id',$completedIds)->count())
    @php($percent=$lessons->count() ? (int)round(($done/$lessons->count())*100) : 0)
    <a class="academy-program-card" href="{{ route($academyRoutePrefix.'program',$program) }}">
        <div class="academy-program-top">
            <span class="academy-audience">{{ $program->audience==='guardian'?'ORANG TUA':($program->audience==='teacher'?'GURU':($program->audience==='admin'?'PENGELOLA':'BERSAMA')) }}</span>
            @if($program->learning_track)<span class="academy-track-tag">{{ $trackLabels[$program->learning_track] ?? ucwords(str_replace('-',' ',$program->learning_track)) }}</span>@endif
            @if($program->is_featured)<span class="badge">Utama</span>@endif
        </div>
        <h2>{{ $program->title }}</h2><p>{{ $program->summary }}</p><div class="academy-progress"><span style="width:{{ $percent }}%"></span></div><div class="academy-program-meta"><span>{{ $program->modules->count() }} modul · {{ $lessons->count() }} materi</span><strong>{{ $percent }}%</strong></div><div class="academy-open">Buka program <span>→</span></div>
    </a>
@empty
    <div class="empty-state"><h3>Belum ada program pada kategori ini.</h3><p>Pilih kategori lain atau kembali ke semua program.</p></div>
@endforelse
</section>
@endsection
