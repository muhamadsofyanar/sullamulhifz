@extends('layouts.public')
@section('title', 'Artikel Sullamul Ḥifẓ')
@section('description', 'Catatan tentang hafalan, pembinaan, kesiapan, KUAT, dan perjalanan bersama Al-Qur’an.')
@section('content')
<section class="page-hero"><div class="public-container page-hero-inner"><span class="public-eyebrow">ARTIKEL & GAGASAN</span><h1>Membaca ulang perjalanan tahfizh.</h1><p>Halaman ini menampilkan katalog awal. Publikasi artikel lengkap akan dibuka bertahap.</p></div></section>
<section class="public-section"><div class="public-container article-grid">
@foreach([
['Mengapa Banyak Orang Berhenti Menghafal','Perjalanan dapat terputus bukan hanya karena kurangnya kemauan, tetapi juga karena sistem yang tidak membaca keadaan.','Perjalanan'],
['Al-Qur’an Bukan Proyek yang Diselesaikan','Hafalan bukan tugas yang selesai ketika target tercapai. Ia memerlukan penjagaan yang terus hidup.','Filosofi'],
['Ketika Jumlah Menjadi Satu-satunya Ukuran','Apa yang hilang ketika keberhasilan hanya dibaca melalui angka dan jumlah setoran?','Evaluasi'],
['Kesiapan Mendahului Beban','Beban yang tepat lahir dari pembacaan terhadap kesiapan, bukan dari penyeragaman.','Pembinaan'],
['KUAT sebagai Jalan Pembinaan','KUAT adalah kerangka keputusan, bukan tuntutan agar peserta menanggung semua tekanan.','KUAT'],
['Tangga Pertumbuhan','Pertumbuhan tidak selalu lurus. Ada fase naik, bertahan, turun, dan kembali.','Pertumbuhan'],
] as [$title,$excerpt,$category])
<article class="article-card"><span>{{ $category }}</span><h2>{{ $title }}</h2><p>{{ $excerpt }}</p><small>Segera diterbitkan</small></article>
@endforeach
</div></section>
@endsection
