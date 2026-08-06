@extends('layouts.public')
@section('title', $profile['name'].' — Implementasi Sullamul Ḥifẓ')
@section('description', 'Profil lengkap '.$profile['name'].' sebagai implementasi pertama dan lembaga percontohan Sullamul Ḥifẓ.')

@section('content')
<section class="page-hero institution-hero">
    <div class="public-container institution-hero-grid">
        <div>
            <span class="public-eyebrow">{{ $profile['eyebrow'] }}</span>
            <h1>{{ $profile['name'] }}</h1>
            <p class="institution-descriptor">{{ $profile['descriptor'] }}</p>
            <p>{{ $profile['summary'] }}</p>
            <div class="hero-actions">
                <a class="public-button primary" href="#profil">Lihat profil lengkap</a>
                <a class="public-button secondary" href="{{ route('public.institution.reference') }}">Panduan untuk lembaga lain</a>
            </div>
        </div>
        <aside class="institution-hero-card">
            <img src="/brand/logo-mark.svg" alt="Logo Sullamul Hifz">
            <span>{{ $profile['brand_relation'] }}</span>
            <blockquote>“{{ $profile['headline'] }}”</blockquote>
            <div class="institution-identity-mini">
                <div><small>Tahun ajaran</small><strong>{{ $profile['identity']['academic_year'] }}</strong></div>
                <div><small>Status</small><strong>{{ $profile['identity']['implementation_status'] }}</strong></div>
            </div>
        </aside>
    </div>
</section>

<section class="public-section institution-profile-section" id="profil">
    <div class="public-container">
        <div class="section-heading narrow-heading">
            <span class="public-eyebrow">IDENTITAS IMPLEMENTASI</span>
            <h2>Contoh lembaga yang dibaca sebagai sistem utuh.</h2>
            <p>{{ $profile['reference_note'] }}</p>
        </div>
        <div class="institution-metric-grid">
            @foreach($profile['stats'] as $stat)
                <article><strong>{{ $stat['value'] }}</strong><span>{{ $stat['label'] }}</span></article>
            @endforeach
        </div>
        <div class="institution-fact-grid">
            <article><span>Kode lembaga</span><strong>{{ $profile['identity']['code'] }}</strong></article>
            <article><span>Slug</span><strong>{{ $profile['identity']['slug'] }}</strong></article>
            <article><span>Zona waktu</span><strong>{{ $profile['identity']['timezone'] }}</strong></article>
            <article><span>Alamat</span><strong>{{ $profile['identity']['address'] ?: 'Belum dipublikasikan' }}</strong></article>
            <article><span>Kontak</span><strong>{{ $profile['identity']['phone'] ?: 'Belum dipublikasikan' }}</strong></article>
            <article><span>Penanggung jawab</span><strong>{{ $profile['identity']['leader_name'] ?: 'Belum ditetapkan pada profil publik' }}</strong></article>
        </div>
    </div>
</section>

<section class="public-section soft-section">
    <div class="public-container institution-vision-grid">
        <div class="institution-vision-card">
            <span class="public-eyebrow">{{ $profile['vision']['label'] }}</span>
            <h2>{{ $profile['vision']['text'] }}</h2>
            <p class="institution-draft-note">{{ $profile['vision']['status'] }}</p>
        </div>
        <div class="institution-outcome-grid">
            @foreach($profile['outcomes'] as $outcome)
                <article><h3>{{ $outcome['title'] }}</h3><p>{{ $outcome['description'] }}</p></article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section">
    <div class="public-container section-heading">
        <span class="public-eyebrow">STRUKTUR TAHUN AJARAN {{ $profile['identity']['academic_year'] }}</span>
        <h2>Enam kelas utama dan dua kelompok Tahfizh.</h2>
        <p>Kelas utama menyimpan perjalanan akademik santri. Kelompok Tahfizh berjalan lintas kelas tanpa menghapus identitas kelas asal.</p>
    </div>
    <div class="public-container institution-table-wrap">
        <table class="institution-table">
            <thead><tr><th>Kelas</th><th>Santri</th><th>Jadwal</th><th>Pengampu</th><th>Fokus</th></tr></thead>
            <tbody>
            @foreach($profile['classes'] as $class)
                <tr><td><strong>{{ $class['name'] }}</strong></td><td>{{ $class['students'] }}</td><td>{{ $class['schedule'] }}</td><td>{{ $class['teacher'] }}</td><td>{{ $class['focus'] }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="public-container institution-group-grid">
        @foreach($profile['learning_groups'] as $group)
            <article>
                <span>{{ $group['session'] }}</span>
                <h3>{{ $group['name'] }}</h3>
                <strong>{{ $group['students'] }} santri · {{ $group['teacher'] }}</strong>
                <p>{{ implode(' + ', $group['source_classes']) }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="public-section soft-section">
    <div class="public-container two-column institution-teacher-section">
        <div>
            <span class="public-eyebrow">TIM PENGAMPU AWAL</span>
            <h2>Peran jelas, riwayat penugasan tetap dapat berubah.</h2>
            <p>Nama dan penugasan berikut menggambarkan kondisi awal implementasi. Sistem tidak melakukan hard-code agar kelas, guru, dan program dapat bertambah atau berubah.</p>
        </div>
        <div class="institution-teacher-list">
            @foreach($profile['teachers'] as $teacher)
                <article><div class="teacher-initial">{{ mb_substr($teacher['name'], 0, 1) }}</div><div><h3>{{ $teacher['name'] }}</h3><strong>{{ $teacher['role'] }}</strong><p>{{ $teacher['assignment'] }}</p></div></article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section">
    <div class="public-container section-heading">
        <span class="public-eyebrow">PROGRAM PEMBINAAN</span>
        <h2>Inti yang berjalan, pengayaan yang dikembangkan bertahap.</h2>
    </div>
    <div class="public-container institution-program-grid">
        @foreach($profile['programs']['core'] as $index => $program)
            <article><span>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $program['title'] }}</h3><p>{{ $program['description'] }}</p></article>
        @endforeach
    </div>
    <div class="public-container institution-enrichment">
        <div><span class="public-eyebrow">PROGRAM PENGAYAAN</span><h3>Masuk sebagai referensi pengembangan, bukan klaim layanan aktif.</h3></div>
        <div class="institution-enrichment-tags">
            @foreach($profile['programs']['enrichment'] as $program)
                <span>{{ $program['title'] }} <small>{{ $program['status'] }}</small></span>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section institution-path-section">
    <div class="public-container section-heading">
        <span class="public-eyebrow">ALUR PEMBINAAN</span>
        <h2>Teknologi hadir setelah kebutuhan manusia terbaca.</h2>
    </div>
    <div class="public-container institution-path-grid">
        @foreach($profile['learning_path'] as $step)
            <article><span>{{ $step['step'] }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['description'] }}</p></article>
        @endforeach
    </div>
</section>

<section class="public-section soft-section">
    <div class="public-container institution-values-layout">
        <div>
            <span class="public-eyebrow">BUDAYA SANTRI</span>
            <h2>Ikrar diterjemahkan menjadi kebiasaan.</h2>
            <p>Nilai tidak berhenti pada poster. Ia dihidupkan di kelas, rumah, dan masjid tanpa menjadi alat ranking atau mempermalukan anak.</p>
            <a class="public-button primary" href="{{ route('public.pledge') }}">Baca Ikrar Santri</a>
        </div>
        <div class="institution-values-grid">
            @foreach($profile['values'] as $index => $value)
                <article><span>{{ $index + 1 }}</span><h3>{{ $value['title'] }}</h3><p>{{ $value['description'] }}</p></article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section institution-poster-section">
    <div class="public-container institution-poster-grid">
        <figure>
            <img src="{{ $profile['poster_image'] }}" alt="Poster referensi Ikrar Santri TPA Al-Insyirah">
            <figcaption>Visual awal Ikrar Santri yang dieksplorasi menjadi halaman digital dan budaya pembiasaan.</figcaption>
        </figure>
        <div>
            <span class="public-eyebrow">DARI VISUAL KE SISTEM</span>
            <h2>Satu poster dapat berkembang menjadi kebijakan, pembiasaan, dan dokumentasi.</h2>
            <p>Eksplorasi tidak berhenti pada desain. Isi ikrar diturunkan menjadi lima budaya bersama, panduan pembiasaan, materi Pembinaan Jumat, komunikasi wali, dan halaman yang dapat dicetak.</p>
            <ul class="institution-check-list">
                <li>Ikrar yang dapat dikelola admin</li>
                <li>Halaman publik dan portal pengguna</li>
                <li>Pembiasaan kelas, rumah, dan masjid</li>
                <li>Tanpa ranking dan label negatif</li>
                <li>Siap diadaptasi lembaga lain</li>
            </ul>
        </div>
    </div>
</section>

<section class="public-section soft-section">
    <div class="public-container section-heading">
        <span class="public-eyebrow">KEMITRAAN KELUARGA</span>
        <h2>Orang tua bukan penonton perkembangan anak.</h2>
    </div>
    <div class="public-container institution-family-grid">
        @foreach($profile['family_partnership'] as $item)
            <article><h3>{{ $item['title'] }}</h3><p>{{ $item['description'] }}</p></article>
        @endforeach
    </div>
</section>

<section class="public-section">
    <div class="public-container institution-principle-grid">
        <div>
            <span class="public-eyebrow">PRINSIP TIDAK BOLEH HILANG</span>
            <h2>Yang ditiru bukan hanya menunya, tetapi cara pandangnya.</h2>
        </div>
        <div class="institution-principle-list">
            @foreach($profile['principles'] as $principle)
                <article><h3>{{ $principle['title'] }}</h3><p>{{ $principle['description'] }}</p></article>
            @endforeach
        </div>
    </div>
</section>

<section class="public-section soft-section">
    <div class="public-container institution-placeholder-card">
        <div>
            <span class="public-eyebrow">DATA YANG BELUM BOLEH DIKARANG</span>
            <h2>Lengkapi setelah ditetapkan oleh TPA Al-Insyirah.</h2>
            <p>Bagian berikut sengaja diberi status belum ditetapkan agar profil publik tidak menampilkan informasi fiktif.</p>
        </div>
        <ul>
            @foreach($profile['placeholders'] as $placeholder)<li>{{ $placeholder }}</li>@endforeach
        </ul>
    </div>
</section>

<section class="public-cta">
    <div class="public-container cta-inner">
        <div><span class="public-eyebrow light">REFERENSI UNTUK LEMBAGA LAIN</span><h2>Gunakan strukturnya, bukan menyalin identitasnya.</h2></div>
        <a class="public-button gold" href="{{ route('public.institution.reference') }}">Buka panduan adaptasi</a>
    </div>
</section>
@endsection
