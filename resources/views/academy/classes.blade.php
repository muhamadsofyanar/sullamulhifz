@extends($academyLayout ?? 'layouts.app',['pageTitle'=>'Kelas Saya'])
@section('content')
<div class="academy-section-heading"><div><span class="eyebrow">KELAS SAYA</span><h1>Ruang belajar yang tersedia untuk Anda</h1><p>Pada fase ini, kelas Academy mengikuti program yang sesuai dengan peran pengguna.</p></div><a class="button secondary" href="{{ route('academy.portal.progress') }}">Lihat progres</a></div>
<div class="academy-class-grid">
@foreach($programs as $program)
    @php($lessons=$program->modules->flatMap->lessons)
    @php($done=$lessons->whereIn('id',$completedIds)->count())
    @php($started=$lessons->filter(fn($lesson)=>$progressRows->has($lesson->id))->count())
    @php($percent=$lessons->count() ? (int)round(($done/$lessons->count())*100) : 0)
    <article class="academy-class-card">
        <span class="academy-content-type">{{ $started ? 'SEDANG DIPELAJARI' : 'SIAP DIMULAI' }}</span>
        <h2>{{ $program->title }}</h2><p>{{ $program->summary }}</p>
        <div class="academy-progress" style="margin-top:16px"><span style="width:{{ $percent }}%"></span></div>
        <div class="academy-program-meta"><span>{{ $done }}/{{ $lessons->count() }} selesai</span><strong>{{ $percent }}%</strong></div>
        <p style="margin-top:16px"><a class="button primary" href="{{ route('academy.portal.program',$program) }}">{{ $started ? 'Lanjut belajar' : 'Mulai kelas' }}</a></p>
    </article>
@endforeach
</div>
@endsection
