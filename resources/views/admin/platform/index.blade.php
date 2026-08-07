@extends('layouts.app',['pageTitle'=>'Fondasi Platform'])
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">SULLAMUL ḤIFẒ V2.3</span>
        <h1>Fondasi Platform</h1>
        <p>Kelola cabang, periode akademik, dan modul pengembangan tanpa mengubah source code.</p>
    </div>
</div>

<section class="card" style="margin-bottom:18px">
    <div class="section-head"><div><h2>Roadmap 10 Fase</h2><p class="muted">Fase 1–6 sudah memiliki implementasi yang dapat dipakai. Fase 7–10 memiliki fondasi data dan feature flag agar pengembangan berikutnya tidak perlu membongkar arsitektur.</p></div><span class="badge">v2.3</span></div>
    <div class="admin-roadmap-grid">
        @foreach($roadmapPhases as $number=>$phase)
            <div class="admin-roadmap-phase {{ $phase[1] }}"><b>{{ $number }}</b><div><small>{{ $phase[1]==='ready'?'SIAP':'FONDASI' }}</small><strong>{{ $phase[0] }}</strong></div></div>
        @endforeach
    </div>
</section>

<div class="grid two">
    <section class="card icon-system-card">
        <h2>Modul dan pengembangan</h2>
        <p class="muted">Modul baru dapat disiapkan lebih dahulu, lalu diaktifkan ketika konten dan alurnya siap.</p>
        <div class="stack">
            @foreach($features as $feature)
                @php($meta = $featureCatalog[$feature->feature_key] ?? [str($feature->feature_key)->headline(), 'Modul tambahan.'])
                <form method="post" action="{{ route('admin.platform.features.update', $feature) }}" class="list-row">
                    @csrf @method('put')
                    <div>
                        <strong>{{ $meta[0] }}</strong>
                        <small>{{ $meta[1] }}</small>
                    </div>
                    <label class="switch" aria-label="Aktifkan {{ $meta[0] }}">
                        <input type="checkbox" name="enabled" value="1" @checked($feature->enabled) onchange="this.form.submit()">
                        <span></span>
                    </label>
                </form>
            @endforeach
        </div>
    </section>

    <section class="card">
        <h2>Tambah cabang</h2>
        <form method="post" action="{{ route('admin.platform.branches.store') }}" class="form-grid">
            @csrf
            <label>Nama cabang<input name="name" required maxlength="120" placeholder="Cabang Utama"></label>
            <label>Kode<input name="code" required maxlength="30" placeholder="UTAMA"></label>
            <label>Nomor telepon<input name="phone" maxlength="40"></label>
            <label class="span-2">Alamat<textarea name="address" rows="3" maxlength="2000"></textarea></label>
            <div class="span-2"><button class="button primary" type="submit">Tambah Cabang</button></div>
        </form>
    </section>
</div>

<section class="card table-card">
    <div class="section-head" style="padding:22px 22px 0">
        <h2>Daftar cabang</h2>
        <span class="badge">{{ $branches->count() }} cabang</span>
    </div>
    <table>
        <thead><tr><th>Cabang</th><th>Kontak</th><th>Status</th><th>Utama</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($branches as $branch)
            @php($formId = 'branch-form-'.$branch->id)
            <tr>
                <td>
                    <input form="{{ $formId }}" name="name" value="{{ $branch->name }}" required>
                    <small>{{ $branch->code }}</small>
                    <textarea form="{{ $formId }}" name="address" rows="2">{{ $branch->address }}</textarea>
                </td>
                <td><input form="{{ $formId }}" name="phone" value="{{ $branch->phone }}"></td>
                <td>
                    <select form="{{ $formId }}" name="status">
                        <option value="active" @selected($branch->status==='active')>Aktif</option>
                        <option value="inactive" @selected($branch->status==='inactive')>Nonaktif</option>
                    </select>
                </td>
                <td><input form="{{ $formId }}" type="checkbox" name="is_main" value="1" @checked($branch->is_main) aria-label="Jadikan cabang utama"></td>
                <td><button form="{{ $formId }}" class="button secondary" type="submit">Simpan</button></td>
            </tr>
        @empty
            <tr><td colspan="5">Belum ada cabang.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
@foreach($branches as $branch)
    <form id="branch-form-{{ $branch->id }}" method="post" action="{{ route('admin.platform.branches.update', $branch) }}">
        @csrf @method('put')
    </form>
@endforeach

<div class="grid two">
    <section class="card">
        <h2>Tambah periode akademik</h2>
        <form method="post" action="{{ route('admin.platform.periods.store') }}" class="form-grid">
            @csrf
            <label>Tahun ajaran<select name="academic_year_id" required>@foreach($years as $year)<option value="{{ $year->id }}">{{ $year->name }}</option>@endforeach</select></label>
            <label>Nama periode<input name="name" required placeholder="Semester 1"></label>
            <label>Tanggal mulai<input type="date" name="start_date" required></label>
            <label>Tanggal selesai<input type="date" name="end_date" required></label>
            <label>Status<select name="status"><option value="draft">Draft</option><option value="active">Aktif</option><option value="closed">Ditutup</option></select></label>
            <div class="span-2"><button class="button primary" type="submit">Simpan Periode</button></div>
        </form>
    </section>
    <section class="card">
        <h2>Periode tersimpan</h2>
        @forelse($years as $year)
            <div class="list-row">
                <div><strong>{{ $year->name }}</strong><small>{{ optional($year->start_date)->format('d M Y') }}–{{ optional($year->end_date)->format('d M Y') }}</small></div>
                <span class="badge">{{ $year->periods->count() }} periode</span>
            </div>
            @foreach($year->periods as $period)
                <div class="list-row inset"><span>{{ $period->name }}</span><small>{{ $period->start_date?->format('d M Y') }}–{{ $period->end_date?->format('d M Y') }} · {{ $period->status }}</small></div>
            @endforeach
        @empty
            <p>Belum ada tahun ajaran.</p>
        @endforelse
    </section>
</div>
@endsection
