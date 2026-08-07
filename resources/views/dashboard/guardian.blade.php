@extends('layouts.app',['pageTitle'=>'Beranda Orang Tua'])
@section('content')
<div class="family-dashboard-head"><span class="eyebrow">ORANG TUA / WALI</span><h1>Assalamu‘alaikum, {{ $guardian->full_name }}</h1><p>Yang penting hari ini: lihat kebutuhan anak, dampingi satu langkah, lalu lanjutkan dengan tenang.</p></div>

@if($academyEnabled && $academyRecommendations->isNotEmpty())
<section class="family-priority-card"><div class="family-priority-icon"><x-icon name="academic" size="30"/></div><div><small>REKOMENDASI GURU</small><h2>{{ $academyRecommendations->first()->lesson->title }}</h2><p>Untuk {{ $academyRecommendations->first()->student->full_name }} · {{ $academyRecommendations->first()->message ?: 'Materi pendamping untuk keluarga.' }}</p></div><a class="button primary" href="{{ route('academy.lesson',$academyRecommendations->first()->lesson) }}">Buka materi</a></section>
@endif

<section class="family-children-grid">
@forelse($students as $student)
<a class="family-child-card" href="{{ route('guardian.children.show',$student) }}"><div class="family-child-avatar">{{ strtoupper(mb_substr($student->full_name,0,1)) }}</div><div><small>Anak saya</small><strong>{{ $student->full_name }}</strong><span>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Belum ditempatkan' }}</span></div><b>→</b></a>
@empty<div class="card"><p class="empty">Belum ada data anak yang terhubung. Hubungi admin.</p></div>@endforelse
</section>

<div class="family-action-grid">
@if($quranEnabled)<a href="{{ route('quran-practice.index') }}" class="family-action primary"><x-icon name="audio" size="30"/><strong>Latihan Al-Qur’an</strong><span>Putar target atau murajaah</span></a>@endif
@if($academyEnabled)<a href="{{ route('academy.index') }}" class="family-action"><x-icon name="academic" size="30"/><strong>Parent Academy</strong><span>Belajar mendampingi anak</span></a>@endif
<a href="{{ route('guardian.tasks.index') }}" class="family-action"><x-icon name="assignment" size="30"/><strong>Tugas Anak</strong><span>{{ $activeTasks->count() }} tugas aktif</span></a>
<a href="{{ route('liaison.index') }}" class="family-action"><x-icon name="discussion" size="30"/><strong>Buku Penghubung</strong><span>Pesan pribadi dengan guru</span></a>
</div>

@if($todayRecords->isNotEmpty())
<section class="card family-today"><div class="section-head"><h2>Hari ini</h2><span class="badge">Ringkasan guru</span></div>@foreach($todayRecords as $record)@php($meeting=$record->meeting;$target=$meeting?->schoolClass ?: $meeting?->learningGroup)<div class="family-today-row"><div class="family-status-dot"></div><div><strong>{{ $record->student?->full_name }} · {{ $target?->name }}</strong><small>{{ ['present'=>'Hadir','late'=>'Terlambat','permission'=>'Izin','sick'=>'Sakit','absent'=>'Tanpa keterangan'][$record->status] ?? $record->status }}</small><p>{{ $meeting?->guardian_summary ?: 'Ringkasan belum ditulis.' }}</p></div></div>@endforeach</section>
@endif

@if($academyEnabled && $academyFeatured)
<section class="card family-academy-banner"><div><span class="eyebrow">PARENT ACADEMY</span><h2>{{ $academyFeatured->title }}</h2><p>{{ $academyFeatured->summary }}</p></div><a class="button primary" href="{{ route('academy.program',$academyFeatured) }}">Mulai belajar</a></section>
@endif

<div class="grid two family-secondary">
<section class="card"><div class="section-head"><h2>Tugas aktif</h2><a href="{{ route('guardian.tasks.index') }}">Lihat semua</a></div>@forelse($activeTasks->take(4) as $task)<a class="list-row" href="{{ route('guardian.tasks.show',$task) }}"><div><strong>{{ $task->assignment->title }}</strong><small>{{ $task->student->full_name }} · {{ $task->assignment->due_at?->format('d M H:i') ?? 'Tanpa tenggat' }}</small></div><span class="badge">{{ ['assigned'=>'Belum dikerjakan','submitted'=>'Sudah dikirim','revision_needed'=>'Perlu perbaikan'][$task->status] ?? str_replace('_',' ',$task->status) }}</span></a>@empty<p class="empty">Tidak ada tugas aktif.</p>@endforelse</section>
<section class="card"><div class="section-head"><h2>Pembinaan Jumat</h2><a href="{{ route('feed.friday') }}">Arsip</a></div>@forelse($fridaySessions as $session)<div class="list-row"><div><strong>{{ $session->title }}</strong><small>{{ $session->session_date->format('d M Y') }}</small></div></div>@empty<p class="empty">Belum ada materi terbaru.</p>@endforelse</section>
</div>
@endsection
