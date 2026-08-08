@extends('layouts.app')
@section('content')
@php
    $statusLabels = ['pending'=>'Menunggu review','revision'=>'Perlu perbaikan','verified'=>'Terverifikasi','rejected'=>'Belum diterima'];
    $typeLabels = ['tahfizh'=>'Tahfizh','reading'=>'Membaca Al-Qur’an','tahsin'=>'Tahsin','murajaah'=>'Murāja‘ah'];
@endphp
<div class="guided-page">
    <section class="guided-hero">
        <div>
            <span class="personal-kicker">BELAJAR · LATIHAN · SETORAN · KOREKSI</span>
            <h1>Belajar Al-Qur’an tidak harus sendirian</h1>
            <p>Ruang Personal tetap privat. Saat Anda memilih mengikuti program, hanya setoran yang Anda kirim yang masuk ke reviewer asatidz.</p>
        </div>
        <div class="guided-loop" aria-label="Alur belajar"><span>Belajar</span><b>→</b><span>Latihan</span><b>→</b><span>Setoran</span><b>→</b><span>Koreksi</span><b>→</b><span>Jaga</span></div>
    </section>

    <section class="card guided-audio-card" id="audio">
        <div class="section-head"><div><span class="eyebrow">DENGARKAN AL-QUR’AN</span><h2>Murattal untuk menyimak & menirukan</h2><p class="muted">Audio adalah pendamping latihan. Koreksi bacaan tetap membutuhkan guru/asatidz.</p></div></div>
        <div class="guided-audio-grid">
            <label>Qari
                <select data-guided-audio-source>
                    @foreach($audioSources as $source)
                        <option value="{{ $source['base_url'] }}">{{ $source['reciter_name'] }}</option>
                    @endforeach
                </select>
            </label>
            <label>Surah
                <select data-guided-audio-surah>
                    @foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach
                </select>
            </label>
            <div class="span-all"><audio controls preload="none" data-guided-audio-player></audio></div>
        </div>
        <small class="muted">Sumber murattal: MP3Quran.net. Pilih surah lalu tekan play.</small>
    </section>

    <section class="card" id="program">
        <div class="section-head"><div><span class="eyebrow">PROGRAM TERARAH</span><h2>Pilih cara bertumbuh</h2><p class="muted">Contoh: Tahfizh Juz 30, Tahsin, program membaca Al-Qur’an, atau murāja‘ah.</p></div></div>
        @if($programs->isEmpty())
            <div class="empty-state"><p>Belum ada program online yang dibuka. Anda tetap dapat memakai jurnal, target, dan audio secara mandiri.</p></div>
        @else
            <div class="guided-program-grid">
            @foreach($programs as $program)
                <article class="guided-program-card">
                    <div><span class="guided-chip">{{ $typeLabels[$program->program_type] ?? $program->program_type }} · {{ ucfirst($program->delivery_mode) }}</span><h3>{{ $program->title }}</h3><p>{{ $program->summary ?: 'Program belajar Al-Qur’an dengan jalur latihan dan review yang terstruktur.' }}</p><small>Penyelenggara: {{ $program->provider?->name }}</small></div>
                    @if($enrolledProgramIds->contains($program->id))
                        <span class="guided-enrolled">✓ Sudah diikuti</span>
                    @else
                        <form method="post" action="{{ route('personal.learning.enroll', $program) }}">@csrf<button class="button primary" type="submit">Ikuti program</button></form>
                    @endif
                </article>
            @endforeach
            </div>
        @endif
    </section>

    <section id="setoran">
        <div class="section-head"><div><span class="eyebrow">PROGRAM SAYA</span><h2>Setoran & umpan balik</h2></div></div>
        @forelse($enrollments as $enrollment)
        <article class="card guided-enrollment-card">
            <div class="guided-enrollment-head">
                <div><span class="guided-chip">{{ $typeLabels[$enrollment->program->program_type] ?? $enrollment->program->program_type }}</span><h3>{{ $enrollment->program->title }}</h3><p class="muted">{{ $enrollment->program->submission_guidance ?: 'Kirim bagian yang memang ingin Anda minta periksa. Reviewer tidak melihat jurnal pribadi Anda.' }}</p></div>
                @if($enrollment->program->academyProgram)
                    <a class="button secondary" href="{{ route('academy.program', $enrollment->program->academyProgram) }}">Buka materi Academy</a>
                @endif
            </div>

            <details class="personal-disclosure guided-submit-box">
                <summary>+ Kirim setoran untuk diperiksa</summary>
                <form method="post" enctype="multipart/form-data" action="{{ route('personal.learning.submit', $enrollment) }}" class="personal-form-grid compact">@csrf
                    <label>Jenis setoran<select name="submission_type" required><option value="memorization">Hafalan</option><option value="reading">Membaca</option><option value="tahsin">Tahsin</option><option value="murajaah">Murāja‘ah</option></select></label>
                    <label>Surah<select name="surah_id" required><option value="">Pilih surah</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
                    <label>Ayat mulai<input type="number" name="start_verse" min="1" required></label>
                    <label>Ayat akhir<input type="number" name="end_verse" min="1" required></label>
                    @if($enrollment->program->accepts_audio)<label class="span-all">Voice note / audio<input type="file" name="audio_evidence" accept="audio/*,.mp3,.m4a,.wav,.ogg,.opus,.webm" capture><small>Di ponsel dapat langsung merekam bila browser mendukung. MP3, M4A, WAV, OGG/OPUS, atau WebM.</small></label>@endif
                    @if($enrollment->program->accepts_text)<label class="span-all">Bukti/catatan teks<textarea name="evidence_text" rows="3" maxlength="5000" placeholder="Boleh diisi jika program menerima setoran teks."></textarea></label>@endif
                    <label class="span-all">Catatan untuk asatidz<textarea name="learner_notes" rows="2" maxlength="2000" placeholder="Contoh: mohon fokus koreksi makhraj ayat 5–7."></textarea></label>
                    <div class="span-all"><button class="button primary" type="submit">Kirim untuk review</button></div>
                </form>
            </details>

            <div class="guided-submission-list">
            @forelse($enrollment->submissions->sortByDesc('submitted_at')->take(8) as $submission)
                <article class="guided-submission">
                    <div class="guided-submission-title"><strong>{{ $submission->surah?->name_latin }} {{ $submission->start_verse }}–{{ $submission->end_verse }}</strong><span class="guided-status status-{{ $submission->review_status }}">{{ $statusLabels[$submission->review_status] ?? $submission->review_status }}</span></div>
                    <small>{{ $submission->submitted_at?->translatedFormat('d M Y H:i') }} · Setoran #{{ $submission->attempt_number }}</small>
                    @if($submission->audio_media_asset_id)<audio controls preload="none" src="{{ route('media.guided-submission', $submission) }}"></audio>@endif
                    @if($submission->evidence_text)<p>{{ $submission->evidence_text }}</p>@endif
                    @foreach($submission->reviews->sortBy('created_at') as $review)
                        <div class="guided-feedback"><strong>Feedback {{ $review->reviewer?->name }}</strong>@if($review->feedback_text)<p>{{ $review->feedback_text }}</p>@endif @if($review->feedback_audio_media_asset_id)<audio controls preload="none" src="{{ route('media.guided-feedback', $review) }}"></audio>@endif</div>
                    @endforeach
                </article>
            @empty
                <p class="muted">Belum ada setoran pada program ini.</p>
            @endforelse
            </div>
        </article>
        @empty
            <div class="card empty-state"><p>Anda belum mengikuti program. Pilih program di atas bila ingin mendapat pendampingan asatidz.</p></div>
        @endforelse
    </section>

    <p class="personal-privacy-note">🔒 Jurnal, target, dan refleksi Personal tidak otomatis dibagikan. Reviewer hanya mendapat setoran program yang Anda kirim secara sadar.</p>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const source = document.querySelector('[data-guided-audio-source]');
    const surah = document.querySelector('[data-guided-audio-surah]');
    const player = document.querySelector('[data-guided-audio-player]');
    if (!source || !surah || !player) return;
    const refresh = () => {
        const base = source.value.replace(/\/+$/, '');
        const number = String(surah.value).padStart(3, '0');
        player.src = `${base}/${number}.mp3`;
        player.load();
    };
    source.addEventListener('change', refresh);
    surah.addEventListener('change', refresh);
    refresh();
});
</script>
@endpush
