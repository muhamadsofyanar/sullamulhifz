@extends('layouts.public',['pageTitle'=>'Verifikasi Sertifikat'])
@section('content')
<section class="public-section"><div class="public-container" style="max-width:760px">
    @if($certificate)
    <article class="card" style="padding:32px;text-align:center"><span class="eyebrow">SERTIFIKAT VALID</span><h1>{{ $certificate->program->title }}</h1><p>Sertifikat penyelesaian ini valid dan tercatat pada Sullamul Ḥifẓ Academy.</p><p>{{ $certificate->institution->name }} · {{ $certificate->issued_at?->format('d M Y') }}</p><small>{{ $certificate->certificate_number }}</small><p class="muted">Nama peserta tidak ditampilkan pada halaman publik untuk menjaga privasi.</p></article>
    @else
    <article class="card" style="padding:32px;text-align:center"><span class="eyebrow">TIDAK DITEMUKAN</span><h1>Sertifikat tidak valid</h1><p>Kode verifikasi tidak ditemukan atau sertifikat sudah tidak aktif.</p></article>
    @endif
</div></section>
@endsection
