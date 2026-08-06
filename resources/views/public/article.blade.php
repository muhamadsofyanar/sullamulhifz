@extends('layouts.public')
@section('title', ($article->seo_title ?: $article->title).' — Sullamul Ḥifẓ')
@section('description', $article->seo_description ?: ($article->excerpt ?: 'Artikel Sullamul Ḥifẓ.'))
@section('content')
<section class="page-hero article-detail-hero">
    <div class="public-container page-hero-inner">
        <span class="public-eyebrow">ARTIKEL</span>
        <h1>{{ $article->title }}</h1>
        <p>{{ $article->excerpt }}</p>
        <small>{{ optional($article->published_at)->translatedFormat('d F Y') }}@if($article->author) · {{ $article->author->name }}@endif</small>
    </div>
</section>
<section class="public-section">
    <div class="public-container article-detail-grid">
        <article class="public-richtext">
            @if($article->cover_image_path)<img class="article-cover" src="{{ asset('storage/'.$article->cover_image_path) }}" alt="{{ $article->title }}">@endif
            {!! nl2br(e($article->content)) !!}
        </article>
        <aside class="prose-aside">
            <div class="aside-card soft">
                <h2>Jelajahi ekosistem</h2>
                <a class="text-link" href="{{ route('public.programs') }}">Program Sullamul Ḥifẓ →</a><br>
                <a class="text-link" href="{{ route('public.registration') }}">Pendaftaran santri →</a>
            </div>
        </aside>
    </div>
</section>
@endsection
