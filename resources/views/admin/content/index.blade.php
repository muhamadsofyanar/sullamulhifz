@extends('layouts.app',['pageTitle'=>'Konten & Pembinaan'])
@section('content')
<div class="page-head"><div><h1>Konten dan Pembinaan</h1><p>Pengumuman terarah, lampiran aman, serta Pembinaan Jumat untuk kelas, kelompok, atau jenjang.</p></div></div>
<div class="grid two">
<section class="card"><h2>Buat pengumuman</h2>
<form class="stack" method="post" enctype="multipart/form-data" action="{{ route('admin.content.announcements.store') }}">@csrf
<label>Judul<input name="title" required maxlength="190"></label>
<label>Penerima<select name="audience_type" data-target-switch><option value="all">Semua pengguna</option><option value="guardians">Semua wali</option><option value="teachers">Semua guru</option><option value="admins">Admin dan kepala</option><option value="class">Kelas tertentu</option><option value="group">Kelompok tertentu</option><option value="level">Jenjang tertentu</option></select></label>
<div class="target-picker-grid">
<label data-target-class hidden>Kelas<select name="class_id" data-target-id-class><option value="">Pilih kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
<label data-target-group hidden>Kelompok<select name="learning_group_id" data-target-id-group><option value="">Pilih kelompok</option>@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select></label>
<label data-target-level hidden>Jenjang<select name="level_id" data-target-id-level><option value="">Pilih jenjang</option>@foreach($levels as $level)<option value="{{ $level->id }}">{{ $level->name }}</option>@endforeach</select></label>
</div>
<label>Isi<textarea name="content" required maxlength="20000"></textarea></label>
<label>Lampiran aman <small>PDF, JPG, PNG, WEBP, atau DOCX.</small><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,.docx"></label>
<div class="form-grid"><label>Terbit<input type="datetime-local" name="publish_at"></label><label>Kedaluwarsa<input type="datetime-local" name="expires_at"></label></div>
<div class="checks-grid"><label class="check"><input type="checkbox" name="is_pinned" value="1"> Tandai penting</label><label class="check"><input type="checkbox" name="require_acknowledgement" value="1"> Wajib konfirmasi baca</label></div>
<label>Status<select name="status"><option value="published">Terbitkan</option><option value="draft">Simpan draft</option></select></label><button class="button primary">Simpan pengumuman</button>
</form></section>

<section class="card"><h2>Catat Pembinaan Jumat</h2>
<form class="stack" method="post" enctype="multipart/form-data" action="{{ route('admin.content.friday.store') }}">@csrf
<label>Tanggal<input type="date" name="session_date" value="{{ now()->format('Y-m-d') }}" required></label>
<label>Sasaran<select name="target_type" data-target-switch><option value="all">Semua santri</option><option value="class">Kelas</option><option value="group">Kelompok belajar</option><option value="level">Jenjang</option></select></label>
<div class="target-picker-grid">
<label data-target-class hidden>Kelas<select name="class_id" data-target-id-class><option value="">Pilih kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
<label data-target-group hidden>Kelompok<select name="learning_group_id" data-target-id-group><option value="">Pilih kelompok</option>@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select></label>
<label data-target-level hidden>Jenjang<select name="level_id" data-target-id-level><option value="">Pilih jenjang</option>@foreach($levels as $level)<option value="{{ $level->id }}">{{ $level->name }}</option>@endforeach</select></label>
</div>
<label>Kategori<input name="category" value="adab" required maxlength="100"></label><label>Tema<input name="title" required maxlength="190"></label><label>Tujuan<textarea name="objectives" maxlength="5000"></textarea></label><label>Ringkasan<textarea name="summary" required maxlength="20000"></textarea></label>
<label>Ayat terkait<select name="quran_surah_id"><option value="">Tidak dipilih</option>@foreach($surahs as $surah)<option value="{{ $surah->id }}">{{ $surah->id }}. {{ $surah->name_latin }}</option>@endforeach</select></label>
<div class="form-grid"><label>Ayat awal<input type="number" name="quran_start_verse" min="1"></label><label>Ayat akhir<input type="number" name="quran_end_verse" min="1"></label></div>
<label>Tindak lanjut di rumah<textarea name="home_follow_up" maxlength="5000"></textarea></label><label>Link audio/video<input type="url" name="media_url" maxlength="500"></label>
<label>Lembar aktivitas <small>PDF, JPG, PNG, WEBP, atau DOCX.</small><input type="file" name="worksheet" accept=".pdf,.jpg,.jpeg,.png,.webp,.docx"></label>
<label class="check"><input type="checkbox" name="family_response_enabled" value="1"> Izinkan respons keluarga</label><label>Status<select name="status"><option value="published">Terbitkan</option><option value="draft">Simpan draft</option></select></label><button class="button primary">Simpan pembinaan</button>
</form></section></div>

<div class="grid two"><section class="card"><h2>Riwayat pengumuman</h2>@forelse($announcements as $item)<div class="list-row"><div><strong>{{ $item->title }}</strong><small>{{ $item->schoolClass?->name ?? $item->learningGroup?->name ?? $item->audience_type }} · {{ $item->status }}</small></div></div>@empty<p class="empty">Belum ada.</p>@endforelse{{ $announcements->links() }}</section><section class="card"><h2>Riwayat Pembinaan Jumat</h2>@forelse($fridaySessions as $item)<div class="list-row"><div><strong>{{ $item->title }}</strong><small>{{ $item->session_date->format('d M Y') }} · {{ $item->targets->first()?->schoolClass?->name ?? $item->targets->first()?->learningGroup?->name ?? $item->targets->first()?->level?->name ?? 'Seluruh santri' }}</small></div></div>@empty<p class="empty">Belum ada.</p>@endforelse{{ $fridaySessions->links() }}</section></div>
@endsection
