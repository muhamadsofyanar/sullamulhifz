@extends('layouts.app',['pageTitle'=>'Qur’an Journey Santri'])
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">FASE 4 · QUR’AN JOURNEY</span>
        <h1>Marhalah mengikuti perjalanan Juz</h1>
        <p>Marhalah bukan ranking atau label kemampuan. Ia adalah standar porsi hafalan baru/setoran pada bagian perjalanan Al-Qur’an tertentu.</p>
    </div>
    <div class="actions"><a class="button secondary" href="{{ route('teacher.tahfizh.index') }}">Perjalanan Tahfizh</a><a class="button ghost" href="{{ route('quran-journey.index') }}">Program Qur’an Saya</a></div>
</div>

<section class="card">
    <div class="section-head"><div><h2>Peta Marhalah Sullamul Ḥifẓ</h2><p class="hint">Porsi berlaku per sesi hafalan/setoran. Frekuensi dapat harian, mingguan, atau fleksibel.</p></div></div>
    <div class="cards-list">
        @foreach([30,29,28,27,26,1] as $juz)
            @php
                $rule = $juz === 1 ? $rules[1] : $rules[$juz];
                $scope = $juz === 1 ? 'Juz 1–25' : 'Juz '.$juz;
            @endphp
            <div class="item-card static"><div><strong>{{ $scope }} · {{ $rule['name'] }}</strong><small>Porsi minimal: {{ $rule['portion'] }}</small></div><span>{{ $rule['name'] }}</span></div>
        @endforeach
    </div>
</section>

<section class="card">
    <div class="section-head"><div><h2>Santri dalam penugasan</h2><p class="hint">Inisialisasi sekali sesuai posisi nyata santri. Setelah itu perpindahan Juz dilakukan melalui milestone Juz selesai.</p></div><span>{{ $students->count() }} santri</span></div>
    @forelse($students as $student)
        @php
            $profile = $student->quranJourneyProfile;
        @endphp
        <div class="list-row">
            <div>
                <strong>{{ $student->full_name }}</strong>
                <small>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Kelompok belajar' }}</small>
                @if($profile)
                    <p>Juz {{ $profile->current_juz_number }} · {{ $profile->marhalah?->name }} · {{ $profile->marhalah?->portion_label }}</p>
                @else
                    <p>Qur’an Journey belum diinisialisasi.</p>
                @endif
            </div>
            <a class="button small secondary" href="{{ route('teacher.quran-journey.student',$student) }}">Buka Journey</a>
        </div>
    @empty
        <p class="empty">Belum ada santri dalam penugasan guru.</p>
    @endforelse
</section>
@endsection
