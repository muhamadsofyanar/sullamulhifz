@extends('layouts.academy',['pageTitle'=>'Program Qur’an'])
@section('content')
@php($purposeLabel=['tilawah'=>'Tilawah','murajaah'=>'Murāja‘ah','both'=>'Tilawah + Murāja‘ah'])
<div class="academy-page-head">
    <div><span class="eyebrow">QUR’AN JOURNEY</span><h1>Program Qur’an</h1><p>Khatam, tilawah, dan Murāja‘ah berjalan berdampingan dengan hafalan. Jadwal membantu kesinambungan, bukan memberi label gagal.</p></div>
    <a class="button secondary" href="{{ route('academy.portal.audio') }}">Buka Mushaf & Audio</a>
</div>

<div class="academy-program-grid">
@foreach($templates as $template)
<article class="academy-program-card">
    <div class="academy-program-card-body"><span class="academy-type-chip">{{ $template->duration_days }} BAGIAN</span><h2>{{ $template->name }}</h2><p>{{ $template->description }}</p><small>{{ $template->scholarly_note }}</small></div>
    @if($template->code==='fami-bisyauqin')<div class="academy-progress-programs">@foreach($template->steps as $step)<div class="academy-progress-row"><span>{{ $step->sequence }}</span><div><strong>{{ $step->mnemonic_letter }}</strong><small>{{ $step->label }}</small></div></div>@endforeach</div>@endif
    <form class="stack compact" method="post" action="{{ route('academy.portal.quran-journey.programs.start') }}">@csrf<input type="hidden" name="quran_program_template_id" value="{{ $template->id }}">
        <label>Tujuan<select name="purpose"><option value="tilawah">Tilawah</option><option value="murajaah">Murāja‘ah</option><option value="both">Tilawah + Murāja‘ah</option></select></label>
        <label>Ritme<select name="schedule_mode"><option value="daily">Mengikuti hari program</option><option value="flexible">Fleksibel</option></select></label>
        <label>Mulai<input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}"></label>
        <button class="button primary">Mulai program</button>
    </form>
</article>
@endforeach
</div>

<section class="academy-content-card">
    <div class="academy-section-heading"><div><span class="eyebrow">PROGRES</span><h2>Perjalanan aktif & riwayat</h2><p>Jika satu hari terlewat, lanjutkan saat siap. Sistem tidak memberi status gagal.</p></div></div>
    @forelse($enrollments as $enrollment)
        @php($done=$enrollment->progress->where('status','completed')->count())
        @php($total=$enrollment->progress->count())
        <div class="academy-progress-row"><span>{{ $total ? round(($done/$total)*100) : 0 }}%</span><div><strong>{{ $enrollment->template?->name }}</strong><small>{{ $purposeLabel[$enrollment->purpose] ?? $enrollment->purpose }} · {{ $done }}/{{ $total }} bagian</small></div></div>
        <div class="academy-progress-programs">
        @foreach($enrollment->progress->sortBy(fn($p)=>$p->step?->sequence) as $progress)
            <div class="academy-progress-row"><span>{{ $progress->step?->sequence }}</span><div><strong>{{ $progress->step?->mnemonic_letter ? $progress->step->mnemonic_letter.' · ' : '' }}{{ $progress->step?->label }}</strong><small>{{ ['pending'=>'Belum','in_progress'=>'Berjalan','completed'=>'Selesai'][$progress->status] ?? $progress->status }}</small></div><form class="inline-form" method="post" action="{{ route('academy.portal.quran-journey.programs.step',$enrollment) }}">@csrf @method('PUT')<input type="hidden" name="step_id" value="{{ $progress->step_id }}"><select name="status"><option value="pending" @selected($progress->status==='pending')>Belum</option><option value="in_progress" @selected($progress->status==='in_progress')>Berjalan</option><option value="completed" @selected($progress->status==='completed')>Selesai</option></select><button class="button small ghost">Simpan</button></form></div>
        @endforeach
        </div>
    @empty<p class="empty">Belum ada program yang dimulai.</p>@endforelse
</section>

<section class="academy-content-card">
    <div class="academy-section-heading"><div><span class="eyebrow">PETA MUSHAF & WARISAN ULAMA</span><h2>Mengenal penanda sambil menggunakannya</h2><p>{{ $divisionCounts['juz'] ?? 0 }} Juz · {{ $divisionCounts['hizb'] ?? 0 }} Ḥizb · {{ $divisionCounts['rubu'] ?? 0 }} Rubu‘ al-Ḥizb · {{ $divisionCounts['fami_manzil'] ?? 0 }} Manzil Fami.</p></div></div>
    <div class="academy-program-grid">@foreach($heritageTerms as $term)<article class="academy-program-card"><div class="academy-program-card-body"><span class="academy-type-chip">{{ $term->arabic_name ?: 'MUSHAF' }}</span><h3>{{ $term->name }}</h3><p>{{ $term->short_description }}</p><small>{{ $term->practical_use }}</small></div></article>@endforeach</div>
    <p class="hint">Penanda/pembagian ini membantu interaksi dengan mushaf dan pembelajaran; bukan tambahan pada teks wahyu Al-Qur’an.</p>
</section>
@endsection
