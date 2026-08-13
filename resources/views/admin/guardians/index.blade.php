@extends('layouts.app')
@php
    $pageTitle='Data Wali';
@endphp
@section('content')
<div class="page-head"><div><span class="eyebrow">AKUN & KELUARGA</span><h1>Data wali</h1><p>Satu akun wali dapat terhubung ke beberapa santri.</p></div></div>
<form class="filter-bar" method="get"><input name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau nomor"><button class="button secondary">Cari</button></form>
<div class="card table-card"><table><thead><tr><th>Wali</th><th>Kontak</th><th>Santri</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($guardians as $guardian)<tr><td><strong>{{ $guardian->full_name }}</strong><small>{{ $guardian->occupation ?: 'Pekerjaan belum diisi' }}</small></td><td>{{ $guardian->phone }}<small>{{ $guardian->email }}</small></td><td>{{ $guardian->students->pluck('full_name')->join(', ') }}</td><td><span class="badge">{{ $guardian->status }}</span></td><td><a class="button small" href="{{ route('admin.guardians.show',$guardian) }}">Kelola</a></td></tr>@empty<tr><td colspan="5" class="empty">Belum ada data wali.</td></tr>@endforelse
</tbody></table></div>{{ $guardians->links() }}
@endsection
