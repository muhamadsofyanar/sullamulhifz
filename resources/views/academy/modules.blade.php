@extends($academyLayout ?? 'layouts.app',['pageTitle'=>'Modul Academy'])
@section('content')
<div class="academy-section-heading"><div><span class="eyebrow">MODUL</span><h1>Peta belajar per modul</h1><p>Lihat struktur materi tanpa harus membuka seluruh program satu per satu.</p></div><span class="badge">{{ $modules->count() }} modul</span></div>
<div class="academy-module-grid">
@foreach($modules as $module)
<article class="academy-module-card">
    <span class="academy-content-type">{{ $module->program->title }}</span><h2>{{ $module->title }}</h2><p>{{ $module->summary }}</p>
    <div class="academy-module-lessons">@foreach($module->lessons->take(5) as $lesson)<a href="{{ route('academy.portal.lesson',$lesson) }}"><span>{{ $lesson->title }}</span><strong>→</strong></a>@endforeach</div>
    @if($module->lessons->count()>5)<p class="hint">+ {{ $module->lessons->count()-5 }} materi lainnya</p>@endif
</article>
@endforeach
</div>
@endsection
