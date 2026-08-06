@extends('layouts.app', ['pageTitle' => 'Ikrar Santri'])

@section('content')
<div class="page-head pledge-no-print">
    <div>
        <span class="eyebrow">NILAI BERSAMA</span>
        <h1>{{ $pledge['title'] }}</h1>
        <p>Ikrar TPA Al-Insyirah untuk dibaca, dipahami, dan dihidupkan bersama.</p>
    </div>
    <button class="button secondary" type="button" onclick="window.print()">Cetak Ikrar</button>
</div>

<section class="portal-pledge-board">
    <div class="portal-pledge-head">
        <img src="/brand/logo-mark.svg" alt="" aria-hidden="true">
        <div>
            <span>{{ $pledge['eyebrow'] }} · {{ $pledge['institution_descriptor'] }}</span>
            <h2>{{ $pledge['title'] }}</h2>
            <p>{{ $pledge['intro'] }}</p>
        </div>
    </div>

    <div class="portal-pledge-layout">
        <ol class="portal-pledge-list">
            @foreach($pledge['items'] as $item)
                <li>
                    <span class="portal-pledge-number">{{ $item['number'] }}</span>
                    <span>
                        <small>{{ $item['short_title'] }}</small>
                        <strong>{{ $item['title'] }}</strong>
                        @if(filled($item['description']))<em>{{ $item['description'] }}</em>@endif
                    </span>
                </li>
            @endforeach
        </ol>
        <aside class="portal-pledge-aside">
            <span class="eyebrow">ARAH BERSAMA</span>
            <blockquote>“{{ $pledge['aspiration'] }}”</blockquote>
            <p>Santri didampingi untuk bertumbuh. Tidak ada ranking berdasarkan hafalan, nilai ikrar, maupun kecepatan perkembangan.</p>
        </aside>
    </div>

    <div class="portal-pledge-closing"><strong>{{ $pledge['closing'] }}</strong><small>{{ $pledge['institution_motto'] }}</small></div>
</section>

<div class="grid three pledge-practice-cards pledge-no-print">
    @foreach($pledge['practice'] as $practice)
        <article class="card">
            <span class="eyebrow">PEMBIASAAN</span>
            <h2>{{ $practice['place'] }}</h2>
            <p class="muted">{{ $practice['description'] }}</p>
        </article>
    @endforeach
</div>

<div class="card pledge-no-print">
    <div class="section-head"><h2>Lima budaya bersama</h2></div>
    <div class="portal-pledge-values">
        @foreach($pledge['values'] as $value)
            <article><strong>{{ $value['title'] }}</strong><span>{{ $value['description'] }}</span></article>
        @endforeach
    </div>
</div>
@endsection
