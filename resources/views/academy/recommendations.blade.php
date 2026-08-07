@extends($academyLayout ?? 'layouts.app',['pageTitle'=>'Rekomendasi Guru'])
@section('content')
<div class="academy-section-heading"><div><span class="eyebrow">REKOMENDASI GURU</span><h1>Materi yang dipilih untuk keluarga</h1><p>Guru dapat memilih satu materi ketika ada kebutuhan pendampingan yang spesifik.</p></div><span class="badge">{{ $recommendations->count() }} rekomendasi</span></div>
@if($recommendations->isNotEmpty())
<div class="academy-recommendation-list">@foreach($recommendations as $recommendation)<a class="academy-recommendation-card" href="{{ route('academy.portal.lesson',$recommendation->lesson) }}"><div class="academy-icon-circle"><x-icon name="guidance"/></div><div><small>Untuk {{ $recommendation->student->full_name }} · {{ $recommendation->status === 'completed' ? 'SELESAI' : 'AKTIF' }}</small><strong>{{ $recommendation->lesson->title }}</strong><p>{{ $recommendation->message ?: $recommendation->lesson->summary }}</p><span>{{ $recommendation->lesson->module->program->title }}</span></div><b>Buka →</b></a>@endforeach</div>
@else
<div class="academy-empty"><x-icon name="guidance" size="34"/><p>Belum ada rekomendasi khusus. Anda tetap dapat memilih materi Academy secara mandiri.</p><a class="button primary" href="{{ route('academy.portal.programs') }}">Lihat program</a></div>
@endif
@endsection
