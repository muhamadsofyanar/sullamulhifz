@extends('layouts.academy',['pageTitle'=>'Ekosistem 10 Fase'])
@section('content')
<div class="academy-ecosystem-hero">
    <span class="eyebrow">ROADMAP PRODUK</span>
    <h1>Sepuluh fase, satu arah pembinaan.</h1>
    <p>Fitur dapat berkembang spontan ketika bermanfaat, tetapi tetap ditempatkan pada fondasi yang jelas: Human Before Data, No Ranking Culture, pembinaan nyata, privasi anak, dan kesinambungan perjalanan.</p>
</div>
<div class="academy-phase-grid">
@foreach($phases as $phase)
    <article class="academy-phase-card {{ $phase['status'] }}">
        <div class="academy-phase-number">{{ $phase['phase'] }}</div>
        <div><span>{{ $phase['status'] === 'ready' ? 'SIAP DIGUNAKAN' : 'FONDASI TERSEDIA' }}</span><h2>{{ $phase['title'] }}</h2><ul>@foreach($phase['features'] as $feature)<li>{{ $feature }}</li>@endforeach</ul></div>
    </article>
@endforeach
</div>
<section class="academy-feature-state">
    <div><span class="eyebrow">FEATURE FLAG</span><h2>Modul lanjutan dapat diaktifkan bertahap.</h2><p>Community, AI Assist, pembayaran, multi-cabang, dan multi-lembaga tetap disiapkan tetapi tidak dipaksa aktif sebelum proses, moderasi, dan tata kelolanya siap.</p></div>
    <div class="academy-feature-chips">
        @foreach($enabled as $key=>$value)<span class="{{ $value ? 'on' : 'off' }}">{{ str($key)->replace('_',' ')->title() }} · {{ $value ? 'ON' : 'OFF' }}</span>@endforeach
    </div>
</section>
@endsection
