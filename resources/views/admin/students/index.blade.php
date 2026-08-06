@extends('layouts.app',['pageTitle'=>'Data Santri'])
@section('content')
<div class="page-head"><div><h1>Santri</h1><p>Riwayat kelas dan hubungan wali tersimpan tanpa menimpa data lama.</p></div><a class="button primary" href="{{ route('admin.students.create') }}">+ Tambah Santri</a></div>
<form class="filter-bar" method="get"><input name="q" value="{{ request('q') }}" placeholder="Cari nama santri"><button class="button secondary">Cari</button></form>
<section class="card table-card"><table><thead><tr><th>Santri</th><th>Kelas</th><th>Wali</th><th>Status</th><th></th></tr></thead><tbody>@forelse($students as $student)<tr><td><strong>{{ $student->full_name }}</strong><small>{{ $student->student_code }}</small></td><td>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Belum ditempatkan' }}</td><td>{{ $student->guardians->pluck('full_name')->join(', ') ?: 'Belum terhubung' }}</td><td><span class="badge">{{ $student->status }}</span></td><td><a href="{{ route('admin.students.show',$student) }}">Buka</a></td></tr>@empty<tr><td colspan="5" class="empty">Belum ada data santri.</td></tr>@endforelse</tbody></table></section>
{{ $students->links() }}
@endsection
