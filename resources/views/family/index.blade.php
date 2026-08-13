{{-- @phase 4.8 Family & Parent Portal; @phase 6.0 Consented memorization summary --}}
@extends('layouts.app', ['pageTitle' => 'Portal Keluarga'])

@section('content')
<div class="page-head expansion-head">
    <div>
        <span class="eyebrow">KELUARGA · AMAN · TANPA RANKING</span>
        <h1>Tumbuh bersama, tetap menghormati ruang pribadi</h1>
        <p>Anak dan orang tua/wali saling menyetujui hubungan. Ringkasan belajar dibuka sesuai izin; jurnal pribadi dan isi portofolio tidak otomatis terlihat.</p>
    </div>
    <span class="phase-chip">Fase 7 · v4.8</span>
</div>

<section class="card expansion-card">
    <div class="section-heading">
        <div><span class="eyebrow">HUBUNGKAN KELUARGA</span><h2>Tambah anak atau orang tua/wali</h2></div>
        <span class="privacy-badge">Persetujuan dua pihak</span>
    </div>
    <form class="stack compact" method="post" action="{{ route('family.relationships.store') }}">
        @csrf
        <label>Email akun keluarga<input type="email" name="email" value="{{ old('email') }}" required></label>
        <div class="alert">Satu akun harus berprofil anak/remaja dan satu akun lain berprofil dewasa atau memiliki peran Wali.</div>
        <button class="button primary" type="submit">Kirim Permintaan Keluarga</button>
    </form>
</section>

<div class="stack expansion-stack">
@forelse($connections as $connection)
@php
    $relationship = $connection['relationship'];
    $counterpart = $connection['counterpart'];
@endphp
<section class="card expansion-card connection-card">
    <div class="section-heading">
        <div>
            <span class="eyebrow">{{ $connection['is_child'] ? 'PENDAMPING SAYA' : 'ANAK YANG DIDAMPINGI' }}</span>
            <h2>{{ $counterpart->name }}</h2>
            <p>{{ $counterpart->email }}</p>
        </div>
        <span class="status-pill status-{{ $relationship->status }}">{{ ucfirst($relationship->status) }}</span>
    </div>

    @if($relationship->status === 'pending')
        @if((int) $relationship->created_by_user_id !== (int) auth()->id())
        <div class="consent-panel">
            <p>Periksa identitas akun sebelum menyetujui. Tidak ada data yang terbuka selama status masih pending.</p>
            <form class="inline-form" method="post" action="{{ route('family.relationships.respond', $relationship) }}">
                @csrf
                @method('PUT')
                <button class="button primary" name="decision" value="accepted">Terima</button>
                <button class="button ghost" name="decision" value="rejected">Tolak</button>
            </form>
        </div>
        @else
        <div class="alert">Menunggu persetujuan {{ $counterpart->name }}.</div>
        @endif
    @elseif($relationship->status === 'accepted')
        <div class="permission-summary">
            <strong>Ringkasan yang dibagikan</strong>
            <div class="chip-row">
                @foreach($connection['scopes'] as $scope)
                <span class="soft-chip">{{ $scopeOptions[$scope] }}</span>
                @endforeach
            </div>
        </div>

        @if($connection['is_child'])
        <details class="expansion-details">
            <summary>Atur informasi yang boleh dilihat keluarga</summary>
            <form class="stack compact" method="post" action="{{ route('family.relationships.consent', $relationship) }}">
                @csrf
                @method('PUT')
                <div class="scope-grid">
                    @foreach($scopeOptions as $key => $label)
                    <label class="check-card">
                        <input type="checkbox" name="visibility_scope[]" value="{{ $key }}" @checked(in_array($key, $connection['scopes'], true))>
                        <span>{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                <button class="button secondary" type="submit">Simpan Batas Informasi</button>
            </form>
        </details>
        @endif

        @if($connection['snapshot'])
        @php $snapshot = $connection['snapshot']; @endphp
        <div class="snapshot-grid">
            <article class="snapshot-tile"><span>Cita-cita</span><strong>{{ $snapshot['profile']?->aspiration ?: 'Belum diisi' }}</strong></article>
            @if(isset($snapshot['latest_check_in']))
            <article class="snapshot-tile"><span>Kondisi belajar</span><strong>{{ $snapshot['latest_check_in']?->energy ?: 'Belum check-in' }}</strong></article>
            @endif
            @if(isset($snapshot['practice']))
            <article class="snapshot-tile"><span>Latihan 30 hari</span><strong>{{ $snapshot['practice']['sessions_30_days'] }} sesi · {{ $snapshot['practice']['minutes_30_days'] }} menit</strong></article>
            @endif
            @if(isset($snapshot['memorization']))
            <article class="snapshot-tile"><span>Setoran 7 hari</span><strong>{{ $snapshot['memorization']['submission_count'] }} setoran · {{ $snapshot['memorization']['murajaah_count'] }} Murāja‘ah</strong></article>
            @endif
        </div>
        @if(isset($snapshot['memorization']))
        @php
            $memorization=$snapshot['memorization'];
        @endphp
        <div class="compact-list"><strong>Yang perlu didampingi</strong>
            @if($memorization['latest'])<span>Terakhir: {{ $memorization['latest']->surah?->name_latin }} {{ $memorization['latest']->start_verse }}–{{ $memorization['latest']->end_verse }} · {{ ucfirst($memorization['latest']->daily_decision ?: 'tercatat') }}</span>@endif
            @if($memorization['latest_note'])<span>Arahan ustadz: {{ $memorization['latest_note'] }}</span>@endif
            @if($memorization['next_review'])<span>Berikutnya: Murāja‘ah {{ $memorization['next_review']->surah?->name_latin }} {{ $memorization['next_review']->start_verse }}–{{ $memorization['next_review']->end_verse }} pada {{ $memorization['next_review']->review_date?->format('d M Y') }}</span>@endif
        </div>
        @endif
        @if(isset($snapshot['goals']))
        <div class="compact-list"><strong>Target aktif</strong>@forelse($snapshot['goals'] as $goal)<span>{{ $goal->title }} · {{ $goal->progress_value }}/{{ $goal->target_value }}</span>@empty<span>Belum ada target aktif.</span>@endforelse</div>
        @endif
        @if(isset($snapshot['portfolio']))
        <div class="compact-list"><strong>Judul portofolio</strong>@forelse($snapshot['portfolio'] as $item)<span>{{ $item->title }}</span>@empty<span>Belum ada portofolio.</span>@endforelse</div>
        @endif
        @endif

        <div class="grid two session-grid">
            <div>
                <h3>Catatan dukungan keluarga</h3>
                <form class="stack compact" method="post" action="{{ route('family.notes.store', $relationship) }}">
                    @csrf
                    <label>Jenis catatan<select name="note_type"><option value="encouragement">Dukungan</option><option value="reflection">Refleksi bersama</option><option value="agreement">Kesepakatan keluarga</option></select></label>
                    <label>Tanggal<input type="date" name="observed_on" value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" required></label>
                    <label>Catatan<textarea name="body" rows="4" maxlength="2000" required></textarea></label>
                    <button class="button primary" type="submit">Simpan Catatan Privat</button>
                </form>
            </div>
            <div>
                <h3>Jejak dukungan</h3>
                <div class="stack compact">
                @forelse($relationship->familySupportNotes as $note)
                <article class="session-row">
                    <div><strong>{{ $note->author->name }}</strong><small>{{ ucfirst($note->note_type) }} · {{ $note->observed_on->format('d M Y') }}</small><p>{{ $note->body }}</p></div>
                </article>
                @empty
                <p>Belum ada catatan keluarga.</p>
                @endforelse
                </div>
            </div>
        </div>

        <form class="end-relationship" method="post" action="{{ route('relationships.destroy', $relationship) }}">
            @csrf
            @method('DELETE')
            <button class="button ghost" type="submit">Akhiri pendampingan</button>
        </form>
    @else
    <p>Hubungan berstatus {{ $relationship->status }} dan tidak membuka informasi.</p>
    @endif
</section>
@empty
<section class="card empty-state expansion-card"><h2>Belum ada hubungan keluarga</h2><p>Hubungkan akun anak/remaja dengan orang tua atau wali yang dipercaya.</p></section>
@endforelse
</div>
@endsection
