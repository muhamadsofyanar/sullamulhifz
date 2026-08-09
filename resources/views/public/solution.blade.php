{{-- @phase 4.2 Brand & Universal Home --}}
@extends('layouts.public')
@section('title', $solution['title'].' — Sullamul Ḥifẓ')
@section('description', $solution['lead'])
@section('content')
<section class="page-hero solution-hero"><div class="public-container page-hero-inner"><span class="public-eyebrow">{{ $solution['eyebrow'] }}</span><h1>{{ $solution['title'] }}</h1><p>{{ $solution['lead'] }}</p><div class="hero-actions"><a class="public-button primary" href="{{ $solution['url'] }}">{{ $solution['cta'] }}</a><a class="public-button secondary" href="{{ route('public.features') }}">Lihat seluruh fitur</a></div></div></section>
<section class="public-section"><div class="public-container solution-layout"><div><span class="public-eyebrow">POLA HUBUNGAN</span><h2>{{ $solution['actors'] }}</h2><p>Setiap pihak masuk melalui akun sendiri. Akses diberikan berdasarkan hubungan dan ruang aktif, sehingga data dari konteks lain tidak otomatis terbuka.</p></div><div class="solution-feature-list">@foreach($solution['features'] as $feature)<article><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><strong>{{ $feature }}</strong></article>@endforeach</div></div></section>
<section class="public-cta"><div class="public-container cta-inner"><div><span class="public-eyebrow light">RUANG YANG BISA BERTUMBUH</span><h2>Mulai dari kebutuhan saat ini, tambahkan hubungan saat diperlukan.</h2></div><a class="public-button gold" href="{{ $solution['url'] }}">{{ $solution['cta'] }}</a></div></section>
@endsection
