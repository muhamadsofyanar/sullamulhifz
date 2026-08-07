@extends('layouts.academy',['pageTitle'=>'Ekosistem 10 Fase'])
@section('content')
<div class="academy-ecosystem-hero">
    <span class="eyebrow">ROADMAP PRODUK · RELEASE GATE</span>
    <h1>Sepuluh fase, satu arah pembinaan.</h1>
    <p>Persentase di bawah bukan sekadar jumlah menu. Implementasi dan validasi produksi dihitung terpisah; sebuah fase baru 100% ketika keduanya lulus. Launch penuh menunggu semua fase mencapai 100%.</p>
</div>
<div class="academy-phase-grid">
@foreach($phases as $phase)
    <article class="academy-phase-card {{ $phase['status'] }}">
        <div class="academy-phase-number">{{ $phase['number'] }}</div>
        <div>
            <span>{{ $phase['percent'] === 100 ? 'SELESAI 100%' : 'DALAM PENGEMBANGAN · '.$phase['percent'].'%' }}</span>
            <h2>{{ $phase['name'] }}</h2>
            <p>{{ $phase['purpose'] }}</p>
            <div class="roadmap-progress"><span style="width:{{ $phase['percent'] }}%"></span></div>
            <small>Implementasi {{ $phase['implementation_pct'] }}% · Validasi {{ $phase['validation_pct'] }}%</small>
            @if($phase['percent'] < 100)<small><b>Berikutnya:</b> {{ $phase['next'] }}</small>@endif
        </div>
    </article>
@endforeach
</div>
<section class="academy-feature-state">
    <div><span class="eyebrow">FEATURE FLAG</span><h2>Modul lanjutan dapat diaktifkan bertahap.</h2><p>Community, AI Assist, pembayaran, multi-cabang, dan multi-lembaga tetap disiapkan tetapi tidak dipaksa aktif sebelum proses, moderasi, keamanan, dan tata kelolanya siap.</p></div>
    <div class="academy-feature-chips">
        @foreach($enabled as $key=>$value)<span class="{{ $value ? 'on' : 'off' }}">{{ str($key)->replace('_',' ')->title() }} · {{ $value ? 'ON' : 'OFF' }}</span>@endforeach
    </div>
</section>
@endsection
