@extends('layouts.app',['pageTitle'=>'Kesiapan Peluncuran'])
@section('content')
<div class="page-head"><div><span class="eyebrow">V2.4.0 · RELEASE GATE 10 FASE</span><h1>Kesiapan Peluncuran</h1><p>Semua pemeriksaan harus berdasarkan pengujian nyata, bukan sekadar ditandai selesai.</p></div><span class="launch-score">{{ $completion }}%</span></div>
<div class="launch-progress"><span style="width:{{ $completion }}%"></span></div>

<div class="stats-grid four">
    <div class="stat-card"><span>Santri aktif</span><strong>{{ $stats['students'] }}</strong></div>
    <div class="stat-card"><span>Akun aktif</span><strong>{{ $stats['activeUsers'] }}</strong></div>
    <div class="stat-card"><span>Pertemuan selesai</span><strong>{{ $stats['completedMeetings'] }}</strong></div>
    <div class="stat-card"><span>Korpus / audio Full Qur’an</span><strong>{{ $stats['quranAyahs'] }}/6236 · {{ $stats['quranTimings'] }}/12472</strong></div>
</div>

@foreach($checks as $category=>$items)
<section class="card launch-check-section"><div class="section-head"><h2>{{ $category }}</h2><span class="badge">{{ $items->where('status','done')->count() }}/{{ $items->count() }}</span></div>
<div class="cards-list">
@foreach($items as $check)
<form class="launch-check-row" method="post" action="{{ route('admin.launch-readiness.update',$check) }}">@csrf @method('PUT')
    <div><strong>{{ $check->label }}</strong><small>{{ $check->checked_at ? 'Diperiksa '.$check->checked_at->format('d M Y H:i').' oleh '.($check->checkedBy?->name ?? 'pengguna') : 'Belum dikonfirmasi' }}</small></div>
    <select name="status"><option value="pending" @selected($check->status==='pending')>Belum diuji</option><option value="in_progress" @selected($check->status==='in_progress')>Sedang diuji</option><option value="done" @selected($check->status==='done')>Selesai</option><option value="blocked" @selected($check->status==='blocked')>Terhambat</option></select>
    <input name="notes" value="{{ $check->notes }}" placeholder="Catatan atau bukti pengujian">
    <button class="button small secondary">Simpan</button>
</form>
@endforeach
</div></section>
@endforeach

<div class="grid two">
<section class="card"><div class="section-head"><h2>Login terbaru</h2></div>@forelse($recentLogins as $login)<div class="list-row"><div><strong>{{ $login->user?->name ?? $login->login_identifier }}</strong><small>{{ $login->logged_in_at?->format('d M Y H:i') }} · {{ $login->ip_address }} · {{ $login->was_successful ? 'Berhasil' : 'Gagal' }}</small></div></div>@empty<p class="empty">Belum ada riwayat.</p>@endforelse</section>
<section class="card"><div class="section-head"><h2>Aktivitas penting</h2></div>@forelse($recentActivities as $activity)<div class="list-row"><div><strong>{{ str_replace('.',' › ',$activity->action) }}</strong><small>{{ $activity->created_at?->format('d M Y H:i') }} · Pengguna #{{ $activity->user_id }}</small></div></div>@empty<p class="empty">Belum ada aktivitas tercatat.</p>@endforelse</section>
</div>
@endsection
