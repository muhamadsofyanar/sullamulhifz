{{-- @phase 4.6 Private Ustadz --}}
@extends('layouts.app', ['pageTitle' => 'Bimbingan Ustadz Privat'])

@section('content')
<div class="page-head expansion-head">
    <div>
        <span class="eyebrow">USTADZ PRIVAT · BERBASIS PERSETUJUAN</span>
        <h1>Bimbingan yang dekat, data tetap terjaga</h1>
        <p>Personal menentukan data yang boleh diringkas. Ustadz membantu melalui sesi dan catatan manusiawi—tanpa ranking dan tanpa membuka jurnal pribadi.</p>
    </div>
    <span class="phase-chip">Fase 5 · v4.6</span>
</div>

@if($canInviteMentor || $canInviteLearner)
<section class="card expansion-card">
    <div class="section-heading">
        <div>
            <span class="eyebrow">HUBUNGKAN AKUN</span>
            <h2>{{ $canInviteMentor ? 'Undang Ustadz' : 'Undang peserta Personal' }}</h2>
        </div>
        <span class="privacy-badge">Aktif setelah disetujui</span>
    </div>
    <form class="stack compact" method="post" action="{{ route('mentorship.relationships.store') }}">
        @csrf
        <label>Email akun tujuan
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        @if($canInviteMentor)
        <fieldset class="scope-fieldset">
            <legend>Ringkasan yang boleh dilihat setelah hubungan aktif</legend>
            <div class="scope-grid">
                @foreach($scopeOptions as $key => $label)
                <label class="check-card">
                    <input type="checkbox" name="visibility_scope[]" value="{{ $key }}" @checked(in_array($key, old('visibility_scope', ['progress_summary', 'goals']), true))>
                    <span>{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </fieldset>
        @endif
        <button class="button primary" type="submit">Kirim Undangan Privat</button>
    </form>
</section>
@endif

<div class="stack expansion-stack">
@forelse($connections as $connection)
@php
    $relationship = $connection['relationship'];
    $counterpart = $connection['counterpart'];
@endphp
<section class="card expansion-card connection-card">
    <div class="section-heading">
        <div>
            <span class="eyebrow">{{ $connection['is_learner'] ? 'USTADZ SAYA' : 'PESERTA BIMBINGAN' }}</span>
            <h2>{{ $counterpart->name }}</h2>
            <p>{{ $counterpart->email }}</p>
        </div>
        <span class="status-pill status-{{ $relationship->status }}">{{ ucfirst($relationship->status) }}</span>
    </div>

    @if($relationship->status === 'pending')
        @if((int) $relationship->created_by_user_id !== (int) auth()->id())
        <div class="consent-panel">
            <p>Permintaan ini belum membuka data apa pun. Pilih dengan sadar.</p>
            <form class="inline-form" method="post" action="{{ route('mentorship.relationships.respond', $relationship) }}">
                @csrf
                @method('PUT')
                <button class="button primary" name="decision" value="accepted">Terima</button>
                <button class="button ghost" name="decision" value="rejected">Tolak</button>
            </form>
        </div>
        @else
        <div class="alert">Menunggu persetujuan {{ $counterpart->name }}. Belum ada data yang dibagikan.</div>
        @endif
    @elseif($relationship->status === 'accepted')
        <div class="permission-summary">
            <strong>Batas akses aktif</strong>
            <div class="chip-row">
                @foreach($connection['scopes'] as $scope)
                <span class="soft-chip">{{ $scopeOptions[$scope] }}</span>
                @endforeach
            </div>
        </div>

        @if($connection['is_learner'])
        <details class="expansion-details">
            <summary>Ubah batas akses Ustadz</summary>
            <form class="stack compact" method="post" action="{{ route('mentorship.relationships.consent', $relationship) }}">
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
                <button class="button secondary" type="submit">Simpan Batas Akses</button>
            </form>
        </details>
        @endif

        @if($connection['snapshot'])
        @php $snapshot = $connection['snapshot']; @endphp
        <div class="snapshot-grid">
            <article class="snapshot-tile">
                <span>Cita-cita</span>
                <strong>{{ $snapshot['profile']?->aspiration ?: 'Belum diisi' }}</strong>
            </article>
            @if(isset($snapshot['latest_check_in']))
            <article class="snapshot-tile">
                <span>Kondisi terakhir</span>
                <strong>{{ $snapshot['latest_check_in']?->energy ?: 'Belum check-in' }}</strong>
            </article>
            @endif
            @if(isset($snapshot['practice']))
            <article class="snapshot-tile">
                <span>Latihan 30 hari</span>
                <strong>{{ $snapshot['practice']['sessions_30_days'] }} sesi · {{ $snapshot['practice']['minutes_30_days'] }} menit</strong>
            </article>
            @endif
        </div>
        @if(isset($snapshot['goals']))
        <div class="compact-list">
            <strong>Target aktif</strong>
            @forelse($snapshot['goals'] as $goal)
            <span>{{ $goal->title }} · {{ $goal->progress_value }}/{{ $goal->target_value }}</span>
            @empty
            <span>Belum ada target aktif.</span>
            @endforelse
        </div>
        @endif
        @if(isset($snapshot['portfolio']))
        <div class="compact-list">
            <strong>Judul portofolio yang diizinkan</strong>
            @forelse($snapshot['portfolio'] as $item)
            <span>{{ $item->title }}</span>
            @empty
            <span>Belum ada portofolio.</span>
            @endforelse
        </div>
        @endif
        @endif

        <div class="grid two session-grid">
            <div>
                <h3>{{ $connection['is_mentor'] ? 'Catat atau jadwalkan sesi' : 'Minta sesi bimbingan' }}</h3>
                <form class="stack compact" method="post" action="{{ route('mentorship.sessions.store', $relationship) }}">
                    @csrf
                    <label>Fokus sesi<input name="focus" maxlength="180" required placeholder="Contoh: memperbaiki makhraj Al-Fatihah"></label>
                    <label>Catatan peserta<textarea name="learner_note" rows="3" maxlength="2000"></textarea></label>
                    <div class="grid two compact-grid">
                        <label>Waktu (opsional)<input type="datetime-local" name="scheduled_at"></label>
                        <label>Durasi<select name="duration_minutes"><option value="">Pilih</option>@foreach([20,30,45,60,90] as $minutes)<option value="{{ $minutes }}">{{ $minutes }} menit</option>@endforeach</select></label>
                    </div>
                    <button class="button primary" type="submit">Simpan Sesi</button>
                </form>
            </div>
            <div>
                <h3>Riwayat sesi</h3>
                <div class="stack compact">
                @forelse($relationship->mentorshipSessions as $session)
                <article class="session-row">
                    <div>
                        <strong>{{ $session->focus }}</strong>
                        <small>{{ $session->scheduled_at?->format('d M Y H:i') ?: 'Belum dijadwalkan' }} · {{ $session->status }}</small>
                        @if($session->mentor_note)<p>{{ $session->mentor_note }}</p>@endif
                    </div>
                    @if($connection['is_mentor'] && ! in_array($session->status, ['completed', 'cancelled'], true))
                    <details class="expansion-details compact-control">
                        <summary>Kelola</summary>
                        <form class="stack compact" method="post" action="{{ route('mentorship.sessions.update', $session) }}">
                            @csrf
                            @method('PUT')
                            <label>Status<select name="status"><option value="scheduled">Terjadwal</option><option value="completed">Selesai</option><option value="cancelled">Dibatalkan</option></select></label>
                            <label>Waktu<input type="datetime-local" name="scheduled_at" value="{{ $session->scheduled_at?->format('Y-m-d\TH:i') }}"></label>
                            <label>Durasi<select name="duration_minutes">@foreach([20,30,45,60,90] as $minutes)<option value="{{ $minutes }}" @selected($session->duration_minutes === $minutes)>{{ $minutes }} menit</option>@endforeach</select></label>
                            <label>Catatan Ustadz<textarea name="mentor_note" rows="3" maxlength="3000">{{ $session->mentor_note }}</textarea></label>
                            <button class="button secondary" type="submit">Perbarui</button>
                        </form>
                    </details>
                    @elseif($connection['is_learner'] && ! in_array($session->status, ['completed', 'cancelled'], true))
                    <form method="post" action="{{ route('mentorship.sessions.update', $session) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <button class="button ghost" type="submit">Batalkan</button>
                    </form>
                    @endif
                </article>
                @empty
                <p>Belum ada sesi.</p>
                @endforelse
                </div>
            </div>
        </div>

        <form class="end-relationship" method="post" action="{{ route('relationships.destroy', $relationship) }}">
            @csrf
            @method('DELETE')
            <button class="button ghost" type="submit">Akhiri hubungan bimbingan</button>
        </form>
    @else
    <p>Hubungan ini berstatus {{ $relationship->status }} dan tidak membuka akses data.</p>
    @endif
</section>
@empty
<section class="card empty-state expansion-card">
    <h2>Belum ada bimbingan privat</h2>
    <p>Mulai dengan menghubungkan akun Personal dan akun Guru/Ustadz aktif.</p>
</section>
@endforelse
</div>
@endsection
