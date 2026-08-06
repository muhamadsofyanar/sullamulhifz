@extends('layouts.public')
@section('title', 'Artikel Sullamul Ḥifẓ')
@section('description', 'Catatan tentang hafalan, pembinaan, kesiapan, KUAT, dan perjalanan bersama Al-Qur’an.')
@section('content')
<section class="page-hero"><div class="public-container page-hero-inner"><span class="public-eyebrow">ARTIKEL & GAGASAN</span><h1>Membaca ulang perjalanan tahfizh.</h1><p>Catatan untuk guru, orang tua, pengelola lembaga, dan pembelajar Al-Qur’an.</p></div></section>
<section class="public-section"><div class="public-container article-grid">
@forelse($articles ?? [] as $article)
<a class="article-card" href="{{ route('public.article', $article) }}">
    <span>ARTIKEL</span>
    <h2>{{ $article->title }}</h2>
    <p>{{ $article->excerpt }}</p>
    <small>{{ optional($article->published_at)->translatedFormat('d M Y') }} →</small>
</a>
@empty
@foreach([
['Mengapa Banyak Orang Berhenti Menghafal','Perjalanan dapat terputus bukan hanya karena kurangnya kemauan, tetapi juga karena sistem yang tidak membaca keadaan.','Perjalanan'],
['Al-Qur’an Bukan Proyek yang Diselesaikan','Hafalan bukan tugas yang selesai ketika target tercapai. Ia memerlukan penjagaan yang terus hidup.','Filosofi'],
['KUAT sebagai Jalan Pembinaan','KUAT adalah kerangka keputusan, bukan tuntutan agar peserta menanggung semua tekanan.','KUAT'],
] as [$title,$excerpt,$category])
<article class="article-card"><span>{{ $category }}</span><h2>{{ $title }}</h2><p>{{ $excerpt }}</p><small>Segera diterbitkan</small></article>
@endforeach
@endforelse
</div>@if(isset($articles)){{ $articles->links() }}@endif</section>
@endsection
