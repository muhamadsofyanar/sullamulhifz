@extends('layouts.app',['pageTitle'=>'Perkembangan Anak'])
@section('content')
@php
$statusTarget=['active'=>'Aktif','in_progress'=>'Berjalan','strengthening'=>'Penguatan','completed'=>'Selesai','paused'=>'Jeda'];
$attendanceLabel=['present'=>'Hadir','late'=>'Terlambat','permission'=>'Izin','sick'=>'Sakit','absent'=>'Tanpa keterangan'];
@endphp
<div class="page-head"><div><span class="eyebrow">{{ $student->currentEnrollment?->schoolClass?->name ?? 'Belum ditempatkan' }}</span><h1>{{ $student->full_name }}</h1><p>Jejak penting pembelajaran, bukan perbandingan dengan anak lain.</p></div><div class="actions"><a class="button secondary" href="{{ route('guardian.tasks.index') }}">Tugas</a>@if($quranEnabled)<a class="button ghost" href="{{ route('quran-practice.index') }}">Latihan Al-Qur’an</a>@endif</div></div>
<div class="stats-grid four"><div class="stat-card"><span>Pertemuan bulan ini</span><strong>{{ $monthlySummary['meetings'] }}</strong></div><div class="stat-card"><span>Kehadiran</span><strong>{{ $monthlySummary['attendance_percent'] !== null ? $monthlySummary['attendance_percent'].'%' : '-' }}</strong></div><div class="stat-card"><span>Setoran Tahfizh</span><strong>{{ $monthlySummary['memorization'] }}</strong></div><div class="stat-card"><span>Murāja‘ah</span><strong>{{ $monthlySummary['murajaah'] }}</strong></div></div>
<section class="card tahfizh-family-summary"><div class="section-head"><div><span class="eyebrow">PENJAGAAN HAFALAN</span><h2>Apa yang perlu didampingi di rumah?</h2><p class="hint">Ringkasan ini mengikuti arahan guru. Tidak ada ranking atau target otomatis.</p></div></div><div class="stats-grid four"><div class="stat-card"><span>Target aktif</span><strong>{{ $tahfizhSummary['activeTargets'] }}</strong></div><div class="stat-card"><span>Jadwal Murāja‘ah</span><strong>{{ $reviewPlans->count() }}</strong></div><div class="stat-card"><span>Siklus belajar</span><strong>{{ $tahfizhSummary['activeCycles'] }}</strong></div><div class="stat-card"><span>Status</span><strong>{{ $tahfizhSummary['needsFollowUp'] ? 'Perlu tindak lanjut' : 'Terpantau' }}</strong></div></div>@if($reviewPlans->isNotEmpty())<div class="list-row"><div><strong>Murāja‘ah berikutnya: {{ $reviewPlans->first()->surah?->name_latin }} {{ $reviewPlans->first()->start_verse }}–{{ $reviewPlans->first()->end_verse }}</strong><small>{{ $reviewPlans->first()->review_date?->format('d M Y') }}</small><p>{{ $reviewPlans->first()->notes }}</p></div>@if($quranEnabled)<a class="button small ghost" href="{{ route('quran-practice.index') }}">▶ Buka latihan</a>@endif</div>@endif @foreach($tahfizhCycles as $cycle) @if($cycle->guardian_guidance)<div class="list-row"><div><strong>Arahan keluarga</strong><small>{{ ucfirst(str_replace('_',' ',$cycle->cycle_type)) }} · {{ $cycle->target?->surah?->name_latin }}</small><p>{{ $cycle->guardian_guidance }}</p></div></div>@endif @endforeach</section>
@if($quranJourneyEnabled && $quranJourneySummary)
@php
$journeyProfile = $quranJourneySummary['profile'];
$journeyRule = $quranJourneySummary['rule'];
$retentionLabels = ['not_assessed'=>'Belum dinilai','strengthening'=>'Penguatan','maintained'=>'Terjaga','reactivation'=>'Perlu dipanggil kembali'];
@endphp
<section class="card">
    <div class="section-head"><div><span class="eyebrow">QUR’AN JOURNEY</span><h2>Posisi perjalanan Al-Qur’an</h2><p class="hint">Marhalah adalah porsi belajar pada Juz yang sedang ditempuh, bukan ranking kemampuan anak.</p></div></div>
    @if($journeyProfile)
    <div class="stats-grid four"><div class="stat-card"><span>Juz aktif</span><strong>{{ $journeyProfile->current_juz_number }}</strong></div><div class="stat-card"><span>Marhalah</span><strong>{{ $journeyProfile->marhalah?->name }}</strong></div><div class="stat-card"><span>Porsi sesi</span><strong>{{ $journeyRule['portion'] ?? '-' }}</strong></div><div class="stat-card"><span>Juz selesai</span><strong>{{ $quranJourneySummary['completedJuz'] }}/30</strong></div></div>
    <p>{{ $journeyProfile->marhalah?->description }}</p><p class="hint">Frekuensi dapat harian, mingguan, atau fleksibel sesuai arahan guru. Porsi bukan kewajiban setor setiap hari.</p>
    @else<p class="empty">Qur’an Journey belum diinisialisasi oleh guru.</p>@endif
    @if($quranJourneyMilestones->isNotEmpty())
    <h3>Milestone terbaru</h3>
    @foreach($quranJourneyMilestones->take(6) as $milestone)<div class="list-row"><div><strong>{{ $milestone->label }}</strong><small>{{ $milestone->memorization_status==='completed' ? 'Selesai hafalan' : 'Berjalan' }} · {{ $retentionLabels[$milestone->retention_status] ?? $milestone->retention_status }}</small><p>{{ $milestone->notes }}</p></div></div>@endforeach
    @endif
    @if($quranJourneyPrograms->isNotEmpty())
    <h3>Program Qur’an</h3>
    @foreach($quranJourneyPrograms as $program)@php($done=$program->progress->where('status','completed')->count()) @php($total=$program->progress->count())<div class="list-row"><div><strong>{{ $program->template?->name }}</strong><small>{{ $done }}/{{ $total }} bagian · {{ ['tilawah'=>'Tilawah','murajaah'=>'Murāja‘ah','both'=>'Tilawah + Murāja‘ah'][$program->purpose] ?? $program->purpose }}</small></div></div>@endforeach
    @endif
</section>
@endif
@if($academyEnabled && $academyRecommendations->isNotEmpty())
<section class="card academy-family-strip">
    <div class="section-head"><div><span class="eyebrow">Academy untuk keluarga</span><h2>Materi yang direkomendasikan untuk mendampingi {{ $student->full_name }}</h2></div><a class="button ghost" href="{{ route('academy.portal.index') }}">Buka Parent Academy</a></div>
    <div class="academy-recommendation-grid">
        @foreach($academyRecommendations as $recommendation)
            <a class="academy-recommendation-card" href="{{ route('academy.portal.lesson',$recommendation->lesson) }}">
                <span class="academy-type-chip">{{ ['video'=>'Video','article'=>'Artikel','audio'=>'Audio','activity'=>'Aktivitas','checklist'=>'Checklist','quiz'=>'Kuis'][$recommendation->lesson?->lesson_type] ?? 'Materi' }}</span>
                <strong>{{ $recommendation->lesson?->title }}</strong>
                <small>{{ $recommendation->lesson?->module?->program?->title }}</small>
                @if($recommendation->message)<p>“{{ $recommendation->message }}”</p>@endif
                <span class="academy-open-link">Pelajari materi →</span>
            </a>
        @endforeach
    </div>
</section>
@endif
@if($publishedMeetings->isNotEmpty())<section class="card"><div class="section-head"><h2>Ringkasan pertemuan terbaru</h2><span class="badge">Dari guru</span></div>@foreach($publishedMeetings as $meeting)@php($target=$meeting->schoolClass ?: $meeting->learningGroup)<div class="list-row"><div><strong>{{ $meeting->meeting_date->format('d M Y') }} · {{ $target?->name }}</strong><small>{{ $meeting->topic ?: 'Pembelajaran' }}</small><p>{{ $meeting->guardian_summary }}</p></div></div>@endforeach</section>@endif
<div class="grid three">
<section class="card"><h2>Target saat ini</h2>@forelse($student->memorizationTargets as $target)<div class="list-row"><div><strong>{{ $target->surah?->name_latin }} {{ $target->start_verse }}–{{ $target->end_verse }}</strong><small>{{ $target->rubu?->name ?? 'Segment legacy tidak digunakan' }} · {{ $target->marhalah?->name ?? 'Beban disesuaikan guru' }}</small><p>{{ $statusTarget[$target->status] ?? ucfirst(str_replace('_',' ',$target->status)) }}{{ $target->due_date ? ' · target '.$target->due_date->format('d M Y') : '' }}{{ $target->notes ? ' · '.$target->notes : '' }}</p>@if($quranEnabled)<a class="button small ghost" href="{{ route('quran-practice.target',$target) }}">▶ Latihan target</a>@endif</div></div>@empty<p class="empty">Belum ada target yang dibagikan.</p>@endforelse</section>
<section class="card"><h2>Kehadiran terbaru</h2>@forelse($student->attendanceRecords as $record)<div class="list-row"><div><strong>{{ $record->meeting?->meeting_date?->format('d M Y') }}</strong><small>{{ $attendanceLabel[$record->status] ?? $record->status }}{{ $record->notes ? ' · '.$record->notes : '' }}</small></div></div>@empty<p class="empty">Belum ada catatan.</p>@endforelse</section>
<section class="card"><h2>Setoran terbaru</h2>@forelse($student->memorizationRecords as $record)<div class="list-row"><div><strong>{{ $record->surah?->name_latin }} {{ $record->start_verse }}–{{ $record->end_verse }}</strong><small>{{ $record->recorded_at?->format('d M Y') }} · {{ ['fluent'=>'Lancar','fair'=>'Lulus dengan penguatan','repeat_needed'=>'Perlu diulang','postponed'=>'Belum dinilai'][$record->result] ?? $record->result }}</small><p>{{ $record->review_recommendation ?: $record->teacher_notes }}</p>@if($quranEnabled && $record->target)<a class="button small ghost" href="{{ route('quran-practice.target',$record->target) }}">▶ Ulangi bacaan</a>@endif</div></div>@empty<p class="empty">Belum ada catatan.</p>@endforelse</section>
<section class="card"><h2>Murāja‘ah</h2>@forelse($student->murajaahRecords as $record)<div class="list-row"><div><strong>{{ $record->surah?->name_latin }} {{ $record->start_verse }}–{{ $record->end_verse }}</strong><small>{{ $record->recorded_at?->format('d M Y') }} · {{ ['maintained'=>'Masih terjaga','strengthening_needed'=>'Perlu penguatan','reactivation_needed'=>'Perlu dipanggil kembali'][$record->result] ?? $record->result }}</small><p>{{ $record->review_recommendation ?: $record->teacher_notes }}</p></div></div>@empty<p class="empty">Belum ada catatan.</p>@endforelse</section>
<section class="card"><h2>Tahsīn</h2>@forelse($student->tahsinRecords as $record)<div class="list-row"><div><strong>{{ $record->material_text }}</strong><small>{{ $record->created_at->format('d M Y') }} · {{ ['good'=>'Baik','practice_needed'=>'Perlu latihan','guidance_needed'=>'Perlu pendampingan','special_correction'=>'Perlu koreksi khusus'][$record->overall_status] ?? $record->overall_status }}</small><p>{{ $record->teacher_notes }}</p></div></div>@empty<p class="empty">Belum ada catatan.</p>@endforelse</section>
@if($reportCardsEnabled)<section class="card"><h2>Rapor terbit</h2>@forelse($student->reportCards as $card)<div class="list-row"><div><strong>Semester {{ ucfirst(str_replace('_',' ',$card->semester)) }}</strong><small>{{ $card->academicYear?->name }} · {{ $card->published_at?->format('d M Y') }}</small><p>{{ $card->teacher_summary }}</p></div></div>@empty<p class="empty">Belum ada rapor yang diterbitkan.</p>@endforelse</section>@endif
</div>
@endsection
