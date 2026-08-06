@extends('layouts.app', ['pageTitle' => 'Kelola Ikrar Santri'])

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">KONTEN NILAI BERSAMA</span>
        <h1>Kelola Ikrar Santri</h1>
        <p>Perubahan langsung digunakan pada halaman publik dan portal pengguna.</p>
    </div>
    <div class="form-actions">
        <a class="button secondary" target="_blank" href="{{ route('public.pledge') }}">Lihat publik</a>
        <a class="button ghost" target="_blank" href="{{ route('feed.pledge') }}">Lihat portal</a>
    </div>
</div>

<form method="post" action="{{ route('admin.student-pledge.update') }}" class="stack">
    @csrf
    @method('put')

    <section class="card stack">
        <div class="section-head"><h2>Identitas dan pembuka</h2></div>
        <div class="form-grid">
            <label class="span-2">Label lembaga
                <input name="eyebrow" value="{{ old('eyebrow', $pledge['eyebrow']) }}" required>
            </label>
            <label>Judul
                <input name="title" value="{{ old('title', $pledge['title']) }}" required>
            </label>
            <label>Keterangan lembaga
                <input name="institution_descriptor" value="{{ old('institution_descriptor', $pledge['institution_descriptor']) }}" required>
            </label>
            <label class="span-2">Motto lembaga
                <input name="institution_motto" value="{{ old('institution_motto', $pledge['institution_motto']) }}" required>
            </label>
            <label>Kalimat pembuka
                <input name="intro" value="{{ old('intro', $pledge['intro']) }}" required>
            </label>
            <label class="span-2">Cita-cita bersama
                <textarea name="aspiration" required>{{ old('aspiration', $pledge['aspiration']) }}</textarea>
            </label>
            <label class="span-2">Kalimat penutup
                <input name="closing" value="{{ old('closing', $pledge['closing']) }}" required>
            </label>
        </div>
    </section>

    <section class="card stack">
        <div class="section-head"><div><h2>Tujuh ikrar</h2><p class="hint">Jumlah ikrar dikunci tujuh agar struktur tetap konsisten.</p></div></div>
        <div class="pledge-admin-list">
            @foreach($pledge['items'] as $index => $item)
                <fieldset>
                    <legend>Ikrar {{ $index + 1 }}</legend>
                    <input type="hidden" name="items[{{ $index }}][number]" value="{{ $index + 1 }}">
                    <input type="hidden" name="items[{{ $index }}][theme]" value="{{ old("items.$index.theme", $item['theme'] ?? '') }}">
                    <div class="form-grid">
                        <label>Label singkat
                            <input name="items[{{ $index }}][short_title]" value="{{ old("items.$index.short_title", $item['short_title']) }}" required>
                        </label>
                        <label>Isi utama
                            <textarea name="items[{{ $index }}][title]" required>{{ old("items.$index.title", $item['title']) }}</textarea>
                        </label>
                        <label class="span-2">Lanjutan kalimat, opsional
                            <input name="items[{{ $index }}][description]" value="{{ old("items.$index.description", $item['description']) }}">
                        </label>
                    </div>
                </fieldset>
            @endforeach
        </div>
    </section>

    <section class="card stack">
        <div class="section-head"><h2>Lima budaya bersama</h2></div>
        <div class="grid two">
            @foreach($pledge['values'] as $index => $value)
                <fieldset>
                    <legend>Nilai {{ $index + 1 }}</legend>
                    <label>Judul
                        <input name="values[{{ $index }}][title]" value="{{ old("values.$index.title", $value['title']) }}" required>
                    </label>
                    <label>Penjelasan
                        <textarea name="values[{{ $index }}][description]" required>{{ old("values.$index.description", $value['description']) }}</textarea>
                    </label>
                </fieldset>
            @endforeach
        </div>
    </section>

    <section class="card stack">
        <div class="section-head"><h2>Pembiasaan</h2></div>
        <div class="grid three">
            @foreach($pledge['practice'] as $index => $practice)
                <fieldset>
                    <legend>Ruang {{ $index + 1 }}</legend>
                    <label>Tempat
                        <input name="practice[{{ $index }}][place]" value="{{ old("practice.$index.place", $practice['place']) }}" required>
                    </label>
                    <label>Cara menghidupkan
                        <textarea name="practice[{{ $index }}][description]" required>{{ old("practice.$index.description", $practice['description']) }}</textarea>
                    </label>
                </fieldset>
            @endforeach
        </div>
    </section>

    <div class="sticky-actions">
        <button class="button primary" type="submit">Simpan Ikrar Santri</button>
    </div>
</form>

<form method="post" action="{{ route('admin.student-pledge.reset') }}" onsubmit="return confirm('Kembalikan seluruh isi Ikrar Santri ke data bawaan?')">
    @csrf
    @method('delete')
    <button class="button danger" type="submit">Reset ke data bawaan</button>
</form>
@endsection
