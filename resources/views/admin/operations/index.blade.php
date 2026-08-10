{{-- @phase 5.1 SaaS Production Readiness --}}
@extends('layouts.app',['pageTitle'=>'Operasional SaaS'])
@section('content')
<div class="v530-page">
    <section class="v530-hero"><div><span class="personal-kicker">FASE 10 · PRODUCTION READINESS</span><h1>Operasional SaaS</h1><p>Pemeriksaan kritis dipisahkan dari bukti operasional nyata. Backup, restore drill, dan load test tidak pernah dianggap selesai hanya karena kode tersedia.</p></div><span class="v530-badge">v5.1</span></section>
    <section class="v530-grid v530-grid-3">
        <article class="card"><span class="eyebrow">PASS</span><strong class="v530-metric">{{ $summary['counts']['pass'] }}</strong></article>
        <article class="card"><span class="eyebrow">WARNING</span><strong class="v530-metric">{{ $summary['counts']['warning'] }}</strong></article>
        <article class="card"><span class="eyebrow">FAIL</span><strong class="v530-metric">{{ $summary['counts']['fail'] }}</strong></article>
    </section>
    <section class="card"><div class="v530-row"><div><span class="eyebrow">CHECKLIST LIVE</span><h2>{{ $summary['critical_ready'] ? 'Tidak ada kegagalan kritis' : 'Ada kegagalan kritis' }}</h2></div><form method="post" action="{{ route('admin.operations.run') }}">@csrf<button class="button primary" type="submit">Simpan hasil pemeriksaan</button></form></div><div class="v530-listing">@foreach($checks as $check)<article><div><strong>{{ str($check['key'])->replace('_',' ')->headline() }}</strong><small>{{ $check['message'] }}</small></div><span class="v530-status v530-status-{{ $check['status'] }}">{{ strtoupper($check['status']) }}</span></article>@endforeach</div></section>
    <section class="v530-grid v530-grid-2"><article class="card"><span class="eyebrow">AUDIT</span><h2>{{ $auditCount }} aktivitas audit / 30 hari</h2><p>Keputusan penting tetap dicatat di activity log.</p></article><article class="card"><span class="eyebrow">BUKTI OPERATOR</span><h2>Marker produksi</h2><p>Gunakan environment <code>SULLAM_BACKUP_VERIFIED_AT</code>, <code>SULLAM_RESTORE_DRILL_VERIFIED_AT</code>, dan <code>SULLAM_LOAD_TEST_VERIFIED_AT</code> hanya setelah pengujian nyata dilakukan.</p></article></section>
    <section class="card"><span class="eyebrow">HISTORI</span><h2>Pemeriksaan tersimpan</h2><div class="v530-listing">@forelse($history as $run)<article><div><strong>{{ str($run->check_key)->replace('_',' ')->headline() }}</strong><small>{{ $run->checked_at?->translatedFormat('d M Y H:i') }} · {{ $run->message }}</small></div><span class="v530-status v530-status-{{ $run->status }}">{{ strtoupper($run->status) }}</span></article>@empty<p class="muted">Belum ada snapshot pemeriksaan tersimpan.</p>@endforelse</div></section>
</div>
@endsection
