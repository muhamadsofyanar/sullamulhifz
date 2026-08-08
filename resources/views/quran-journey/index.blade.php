@extends('layouts.app',['pageTitle'=>'Program Qur’an Saya'])
@section('content')
@php($purposeLabel=['tilawah'=>'Tilawah','murajaah'=>'Murāja‘ah','both'=>'Tilawah + Murāja‘ah'])
<div class="page-head"><div><span class="eyebrow">QUR’AN JOURNEY</span><h1>Program Qur’an Saya</h1><p>Tilawah dan Murāja‘ah berjalan berdampingan dengan hafalan. Jadwal adalah alat bantu kesinambungan, bukan alat untuk memberi label gagal.</p></div><div class="actions"><a class="button ghost" href="{{ route('quran-practice.index') }}">Latihan Al-Qur’an</a></div></div>

<div class="grid two">
@foreach($templates as $template)
<section class="card">
    <span class="eyebrow">{{ $template->duration_days }} BAGIAN</span><h2>{{ $template->name }}</h2><p>{{ $template->description }}</p><p class="hint">{{ $template->scholarly_note }}</p>
    @if($template->code==='fami-bisyauqin')<div class="cards-list">@foreach($template->steps as $step)<div class="item-card static"><div><strong>{{ $step->mnemonic_letter }}</strong><small>{{ $step->label }}</small></div><span>{{ $step->sequence }}/7</span></div>@endforeach</div>@endif
    <form class="stack compact" method="post" action="{{ route('quran-journey.programs.start') }}">@csrf<input type="hidden" name="quran_program_template_id" value="{{ $template->id }}">
        <label>Tujuan<select name="purpose"><option value="tilawah">Tilawah</option><option value="murajaah">Murāja‘ah</option><option value="both">Tilawah + Murāja‘ah</option></select></label>
        <label>Ritme<select name="schedule_mode"><option value="daily">Mengikuti hari program</option><option value="flexible">Fleksibel</option></select></label>
        <label>Mulai<input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}"></label><button class="button primary">Mulai program</button>
    </form>
</section>
@endforeach
</div>

<section class="card"><div class="section-head"><div><h2>Perjalanan aktif & riwayat</h2><p class="hint">Jika satu hari terlewat, lanjutkan langkah berikutnya saat siap. Sistem tidak memberi status gagal.</p></div><span>{{ $enrollments->count() }} program</span></div>
@forelse($enrollments as $enrollment)
    @php($done=$enrollment->progress->where('status','completed')->count()) @php($total=$enrollment->progress->count())
    <div class="item-card static"><div><strong>{{ $enrollment->template?->name }}</strong><small>{{ $purposeLabel[$enrollment->purpose] ?? $enrollment->purpose }} · mulai {{ $enrollment->start_date?->format('d M Y') }} · {{ $done }}/{{ $total }} bagian</small></div><span>{{ $total ? round(($done/$total)*100) : 0 }}%</span></div>
    <div class="cards-list">
    @foreach($enrollment->progress->sortBy(fn($p)=>$p->step?->sequence) as $progress)
        <div class="list-row"><div><strong>{{ $progress->step?->mnemonic_letter ? $progress->step->mnemonic_letter.' · ' : '' }}{{ $progress->step?->label }}</strong><small>{{ ['pending'=>'Belum','in_progress'=>'Berjalan','completed'=>'Selesai'][$progress->status] ?? $progress->status }}</small></div><form class="inline-form" method="post" action="{{ route('quran-journey.programs.step',$enrollment) }}">@csrf @method('PUT')<input type="hidden" name="step_id" value="{{ $progress->step_id }}"><select name="status"><option value="pending" @selected($progress->status==='pending')>Belum</option><option value="in_progress" @selected($progress->status==='in_progress')>Berjalan</option><option value="completed" @selected($progress->status==='completed')>Selesai</option></select><button class="button small ghost">Simpan</button></form></div>
    @endforeach
    </div>
@empty<p class="empty">Belum ada program yang dimulai.</p>@endforelse
</section>

<section class="card"><div class="section-head"><div><span class="eyebrow">PETA MUSHAF & WARISAN ULAMA</span><h2>Mengenal penanda sambil menggunakannya</h2></div><div><span class="badge">{{ $divisionCounts['rubu'] ?? 0 }} Rubu‘</span> <span class="badge">{{ $divisionCounts['hizb'] ?? 0 }} Ḥizb</span></div></div>
<div class="grid two">@foreach($heritageTerms as $term)<div class="item-card static"><div><strong>{{ $term->name }} @if($term->arabic_name) · {{ $term->arabic_name }} @endif</strong><small>{{ $term->short_description }}</small><p>{{ $term->practical_use }}</p></div></div>@endforeach</div>
</section>
@endsection
