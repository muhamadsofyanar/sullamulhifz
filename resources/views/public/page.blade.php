@extends('layouts.public')
@section('title', ($page->seo_title ?: $page->title).' — Sullamul Ḥifẓ')
@section('description', $page->seo_description ?: ($page->summary ?: 'Informasi resmi Sullamul Ḥifẓ.'))
@section('content')
<section class="page-hero">
    <div class="public-container page-hero-inner">
        <span class="public-eyebrow">SULLAMUL ḤIFẒ</span>
        <h1>{{ $page->title }}</h1>
        @if($page->summary)<p>{{ $page->summary }}</p>@endif
    </div>
</section>
<section class="public-section">
    <div class="public-container prose-grid">
        <article class="prose-main public-richtext">{!! nl2br(e($page->content)) !!}</article>
        <aside class="prose-aside">
            <div class="aside-card soft">
                <h2>Butuh informasi?</h2>
                <p>Hubungi tim Sullamul Ḥifẓ atau lakukan pendaftaran awal.</p>
                <a class="public-button primary" href="{{ route('public.registration') }}">Pendaftaran awal</a>
            </div>
        </aside>
    </div>
</section>
@endsection
