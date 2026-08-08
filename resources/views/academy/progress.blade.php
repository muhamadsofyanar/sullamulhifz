@extends($academyLayout ?? 'layouts.app',['pageTitle'=>'Progres Belajar'])
@section('content')
<div class="academy-section-heading"><div><span class="eyebrow">PROGRES BELAJAR</span><h1>Jejak belajar Anda</h1><p>Progres membantu melanjutkan perjalanan, bukan menjadi alat membandingkan pengguna.</p></div></div>
<section class="academy-progress-summary">
    <div class="academy-progress-ring"><div><strong>{{ $percent }}%</strong><span>{{ $completed }} dari {{ $total }} materi selesai</span></div></div>
    <div class="academy-progress-programs">@foreach($programProgress as $item)<article class="academy-progress-row"><div class="academy-progress-row-head"><strong>{{ $item['program']->title }}</strong><span>{{ $item['done'] }}/{{ $item['total'] }} selesai · {{ $item['percent'] }}%</span></div><div class="academy-progress"><span style="width:{{ $item['percent'] }}%"></span></div></article>@endforeach</div>
</section>
<div class="academy-section-heading"><div><h2>Aktivitas terakhir</h2><p>Materi yang sudah dibuka atau diselesaikan.</p></div></div>
<div class="academy-catalog-grid">
@forelse($rows->take(12) as $row)
<a class="academy-content-card" href="{{ route($academyRoutePrefix.'lesson',$row->lesson) }}"><div class="academy-content-card-top"><span class="academy-content-type">{{ strtoupper($row->status) }}</span><span class="badge">{{ $row->progress_percent }}%</span></div><h3>{{ $row->lesson->title }}</h3><p>{{ $row->lesson->module->program->title }}</p><footer><span>{{ $row->updated_at?->format('d M Y H:i') }}</span><strong>Buka →</strong></footer></a>
@empty<div class="academy-empty">Belum ada aktivitas belajar. Pilih satu program dan mulai dari materi yang paling relevan.</div>@endforelse
</div>
@if($certificates->isNotEmpty())
<div class="academy-section-heading"><div><h2>Sertifikat</h2><p>Sertifikat terbit otomatis setelah seluruh materi wajib program selesai.</p></div></div>
<div class="academy-catalog-grid">
@foreach($certificates as $certificate)
<a class="academy-content-card" href="{{ route($academyRoutePrefix.'certificate',$certificate) }}"><div class="academy-content-card-top"><span class="academy-content-type">SERTIFIKAT</span><span class="badge">TERBIT</span></div><h3>{{ $certificate->program->title }}</h3><p>{{ $certificate->certificate_number }}</p><footer><span>{{ $certificate->issued_at?->format('d M Y') }}</span><strong>Lihat →</strong></footer></a>
@endforeach
</div>
@endif
@endsection
