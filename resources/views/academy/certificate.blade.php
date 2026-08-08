@extends($academyLayout ?? 'layouts.academy',['pageTitle'=>'Sertifikat Academy'])
@section('content')
<article class="card" style="max-width:900px;margin:24px auto;padding:42px;text-align:center;border:2px solid #d5b45b">
    <span class="eyebrow">SULLAMUL ḤIFẒ ACADEMY</span>
    <h1 style="font-size:clamp(32px,5vw,54px);margin:12px 0">Sertifikat Penyelesaian</h1>
    <p>Diberikan kepada</p>
    <h2 style="font-size:32px">{{ $certificate->user->name }}</h2>
    <p>atas penyelesaian program</p>
    <h2>{{ $certificate->program->title }}</h2>
    <p>{{ $certificate->institution->name }} · {{ $certificate->issued_at?->format('d M Y') }}</p>
    <hr style="margin:30px 0;border:0;border-top:1px solid #ddd">
    <small>Nomor: {{ $certificate->certificate_number }}</small><br>
    <small>Verifikasi: {{ route('certificate.verify',$certificate->verification_code) }}</small>
    <div style="margin-top:28px"><button type="button" class="button primary" onclick="window.print()">Cetak / Simpan PDF</button></div>
</article>
@endsection
