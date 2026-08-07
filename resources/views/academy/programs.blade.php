@extends($academyLayout ?? 'layouts.app',['pageTitle'=>'Program Academy'])
@section('content')
<div class="academy-section-heading"><div><span class="eyebrow">PROGRAM</span><h1>E-course Sullamul Ḥifẓ Academy</h1><p>Pilih jalur belajar yang sesuai dengan kebutuhan Anda saat ini.</p></div><span class="badge">{{ $programs->count() }} program</span></div>
<section class="academy-program-grid">
@foreach($programs as $program)
    @php($lessons=$program->modules->flatMap->lessons)
    @php($done=$lessons->whereIn('id',$completedIds)->count())
    @php($percent=$lessons->count() ? (int)round(($done/$lessons->count())*100) : 0)
    <a class="academy-program-card" href="{{ route($academyRoutePrefix.'program',$program) }}"><div class="academy-program-top"><span class="academy-audience">{{ $program->audience==='guardian'?'ORANG TUA':($program->audience==='teacher'?'GURU':($program->audience==='admin'?'PENGELOLA':'BERSAMA')) }}</span>@if($program->is_featured)<span class="badge">Utama</span>@endif</div><h2>{{ $program->title }}</h2><p>{{ $program->summary }}</p><div class="academy-progress"><span style="width:{{ $percent }}%"></span></div><div class="academy-program-meta"><span>{{ $program->modules->count() }} modul · {{ $lessons->count() }} materi</span><strong>{{ $percent }}%</strong></div><div class="academy-open">Buka program <span>→</span></div></a>
@endforeach
</section>
@endsection
