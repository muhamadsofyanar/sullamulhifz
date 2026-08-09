{{-- @phase 4.7 Institution Suite --}}
@extends('layouts.app', ['pageTitle' => 'Suite Lembaga'])

@section('content')
<div class="page-head expansion-head">
    <div>
        <span class="eyebrow">OPERASIONAL LEMBAGA · MULTI-WORKSPACE</span>
        <h1>{{ $institution->name }}</h1>
        <p>Kelola kesiapan dan anggota pada ruang ini saja. Menonaktifkan anggota tidak memengaruhi akun atau ruang lain miliknya.</p>
    </div>
    <span class="phase-chip">Fase 6 · v4.7</span>
</div>

@if(session('institution_invitation_url'))
<section class="card expansion-card invitation-result">
    <span class="eyebrow">TAUTAN UNDANGAN · TAMPIL SEKALI</span>
    <h2>Salin dan kirim secara pribadi</h2>
    <div class="activation-link-box">
        <input type="text" value="{{ session('institution_invitation_url') }}" readonly data-copy-source aria-label="Tautan undangan ruang">
        <button type="button" class="button secondary" data-copy-button>Salin</button>
    </div>
</section>
@endif

<div class="grid two institution-summary-grid">
    <section class="card expansion-card readiness-card">
        <div class="section-heading"><div><span class="eyebrow">KESIAPAN DASAR</span><h2>{{ $readinessPercent }}%</h2></div><span class="privacy-badge">{{ collect($checklist)->where('complete', true)->count() }}/{{ count($checklist) }} lengkap</span></div>
        <div class="progress-track"><span style="width:{{ $readinessPercent }}%"></span></div>
        <div class="checklist-list">
            @foreach($checklist as $item)
            <div class="checklist-row {{ $item['complete'] ? 'complete' : '' }}"><span>{{ $item['complete'] ? '✓' : '○' }}</span><strong>{{ $item['label'] }}</strong></div>
            @endforeach
        </div>
    </section>

    <section class="card expansion-card">
        <div class="section-heading"><div><span class="eyebrow">UNDANG ANGGOTA</span><h2>Tambah ke ruang {{ $institution->term('student') }}</h2></div><span class="privacy-badge">Berlaku 7 hari</span></div>
        @if($canManage)
        <form class="stack compact" method="post" action="{{ route('admin.institution-suite.invitations.store') }}">
            @csrf
            <label>Email akun aktif<input type="email" name="email" value="{{ old('email') }}" required></label>
            <label>Peran di lembaga<select name="role" required>@foreach($invitableRoles as $key => $role)<option value="{{ $key }}">{{ $role['label'] }}</option>@endforeach</select></label>
            <div class="alert">Undangan menambah ruang baru pada akun yang sama. Data ruang Personal atau lembaga lain tetap terpisah.</div>
            <button class="button primary" type="submit">Buat Tautan Undangan</button>
        </form>
        @else
        <p>Mode baca. Hanya Admin Lembaga yang dapat membuat undangan.</p>
        @endif
    </section>
</div>

<section class="card expansion-card">
    <div class="section-heading"><div><span class="eyebrow">DIREKTORI RUANG</span><h2>Anggota dan peran</h2></div><span class="privacy-badge">{{ $members->total() }} keanggotaan</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Peran</th><th>Jenis</th><th>Status</th><th>Bergabung</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($members as $membership)
            <tr>
                <td><strong>{{ $membership->user?->name }}</strong><small class="table-subtitle">{{ $membership->user?->email }}</small></td>
                <td>{{ $membership->role?->display_name ?: 'Anggota' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $membership->membership_type)) }}</td>
                <td><span class="status-pill status-{{ $membership->status }}">{{ ucfirst($membership->status) }}</span></td>
                <td>{{ $membership->joined_at?->format('d M Y') ?: '—' }}</td>
                <td>
                    @if($canManage && $membership->membership_type !== 'owner' && (int) $membership->user_id !== (int) auth()->id())
                    <form method="post" action="{{ route('admin.institution-suite.members.update', $membership) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="{{ $membership->status === 'active' ? 'suspended' : 'active' }}">
                        <button class="button ghost" type="submit">{{ $membership->status === 'active' ? 'Suspend di ruang ini' : 'Aktifkan' }}</button>
                    </form>
                    @else
                    <span>—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6">Belum ada anggota.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $members->links() }}
</section>

<section class="card expansion-card">
    <div class="section-heading"><div><span class="eyebrow">LEDGER UNDANGAN</span><h2>Riwayat undangan terbaru</h2></div><span class="privacy-badge">Audit tersimpan</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Email</th><th>Peran</th><th>Status</th><th>Kedaluwarsa</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($invitations as $invitation)
            <tr>
                <td>{{ $invitation->email }}</td>
                <td>{{ $invitation->role?->display_name }}</td>
                <td><span class="status-pill status-{{ $invitation->status }}">{{ ucfirst($invitation->status) }}</span></td>
                <td>{{ $invitation->expires_at->format('d M Y H:i') }}</td>
                <td>
                    @if($canManage && $invitation->status === 'pending')
                    <form method="post" action="{{ route('admin.institution-suite.invitations.destroy', $invitation) }}">
                        @csrf
                        @method('DELETE')
                        <button class="button ghost" type="submit">Batalkan</button>
                    </form>
                    @else
                    <span>—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5">Belum ada undangan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
