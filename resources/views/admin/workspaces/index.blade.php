{{-- @phase 4.4 Multi-tenant Institution Foundation --}}
@extends('layouts.app',['pageTitle'=>'Ruang & Lembaga'])
@section('content')
<div class="page-head"><div><span class="eyebrow">KENDALI MULTI-TENANT</span><h1>Ruang & Lembaga</h1><p>Periksa pendaftaran, jenis lembaga, jumlah anggota, dan status aktivasi.</p></div></div>
<section class="card table-wrap"><table><thead><tr><th>Lembaga</th><th>Jenis</th><th>Anggota</th><th>Onboarding</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>@forelse($workspaces as $workspace)<tr><td><strong>{{ $workspace->name }}</strong><small>{{ $workspace->code }}</small></td><td>{{ $institutionTypes[$workspace->institution_type]['label'] ?? $workspace->institution_type }}</td><td>{{ $workspace->workspace_memberships_count }}</td><td>{{ $workspace->onboarding_status }}</td><td><span class="badge">{{ $workspace->status }}</span></td><td><form class="inline-form" method="post" action="{{ route('admin.workspaces.status',$workspace) }}">@csrf @method('PUT')<select name="status"><option value="active">Aktifkan</option><option value="suspended">Tangguhkan</option><option value="rejected">Tolak</option></select><button class="button secondary" type="submit">Terapkan</button></form></td></tr>@empty<tr><td colspan="6">Belum ada ruang.</td></tr>@endforelse</tbody></table></section>
{{ $workspaces->links() }}
@endsection
