@extends('layouts.app')
@php
    $pageTitle='Impor Data';
@endphp
@section('content')
<div class="page-head"><div><span class="eyebrow">CSV TERVALIDASI</span><h1>Impor santri & wali</h1><p>Preview dilakukan sebelum data ditulis ke database.</p></div><a class="button secondary" href="{{ route('admin.imports.template') }}">Unduh template</a></div>
<div class="grid two"><form class="card stack" method="post" enctype="multipart/form-data" action="{{ route('admin.imports.preview') }}">@csrf<h2>Unggah CSV</h2><label>File CSV<input type="file" name="file" accept=".csv,text/csv" required></label><p class="hint">Gunakan template resmi. Maksimal 5 MB.</p><button class="button primary">Periksa & preview</button></form><div class="card"><h2>Riwayat impor</h2><div class="cards-list">@forelse($batches as $batch)<a class="item-card" href="{{ route('admin.imports.show',$batch) }}"><span><strong>{{ $batch->original_name }}</strong><small>{{ $batch->created_at->format('d/m/Y H:i') }} · {{ $batch->total_rows }} baris</small></span><span class="badge">{{ $batch->status }}</span></a>@empty<p class="empty">Belum ada impor.</p>@endforelse</div>{{ $batches->links() }}</div></div>
@endsection
