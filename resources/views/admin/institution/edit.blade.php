@extends('layouts.app',['pageTitle'=>'Profil Lembaga'])
@section('content')
<div class="page-head"><div><span class="eyebrow">IDENTITAS LEMBAGA</span><h1>Profil TPA Al-Insyirah</h1><p>Lengkapi data yang benar-benar sudah ditetapkan. Informasi yang belum tersedia boleh dibiarkan kosong.</p></div><a class="button secondary" href="{{ route('admin.academic-core.index') }}">Lihat kesiapan akademik</a></div>
<div class="stats-grid four">
<div class="stat-card"><span>Master brand</span><strong>{{ $institution->setting('master_brand','Sullamul Ḥifẓ') }}</strong></div>
<div class="stat-card"><span>Tahun aktif</span><strong>{{ $activeYear?->name ?? 'Belum ada' }}</strong></div>
<div class="stat-card"><span>Status profil</span><strong>{{ $institution->setting('profile_completed',false) ? 'Lengkap' : 'Perlu dilengkapi' }}</strong></div>
<div class="stat-card"><span>Relasi merek</span><strong>Powered by Sullamul Ḥifẓ</strong></div>
</div>
<form class="stack" method="post" action="{{ route('admin.institution.update') }}">@csrf @method('PUT')
<div class="grid two">
<section class="card"><h2>Identitas resmi</h2><div class="stack compact"><label>Nama lembaga<input name="name" value="{{ old('name',$institution->name) }}" required></label><label>Nama legal<input name="legal_name" value="{{ old('legal_name',$institution->legal_name) }}" placeholder="Kosongkan bila belum ditetapkan"></label><div class="form-grid"><label>Telepon/WhatsApp<input name="phone" value="{{ old('phone',$institution->phone) }}"></label><label>Email<input type="email" name="email" value="{{ old('email',$institution->email) }}"></label></div><label>Alamat<textarea name="address" rows="4">{{ old('address',$institution->address) }}</textarea></label><label>Penanggung jawab<input name="leader_name" value="{{ old('leader_name',$institution->setting('leader_name')) }}" placeholder="Kosongkan bila belum dipublikasikan"></label></div></section>
<section class="card"><h2>Identitas Sullamul Ḥifẓ</h2><div class="stack compact"><label>Master brand<input name="master_brand" value="{{ old('master_brand',$institution->setting('master_brand','Sullamul Ḥifẓ')) }}" required></label><label>Tagline<input name="tagline" value="{{ old('tagline',$institution->setting('tagline','Bukan Sekadar Hafal, Tapi KUAT')) }}" required></label><label>Rumusan hubungan merek<input name="brand_relation" value="{{ old('brand_relation',$institution->setting('brand_relation','TPA Al-Insyirah — Powered by Sullamul Ḥifẓ')) }}"></label><label>Catatan bawah rapor<textarea name="report_footer" rows="3">{{ old('report_footer',$institution->setting('report_footer')) }}</textarea></label></div></section>
</div>
<div class="grid two">
<section class="card"><h2>Arah lembaga</h2><div class="stack compact"><label>Visi<textarea name="vision" rows="5" placeholder="Isi setelah disahkan oleh lembaga">{{ old('vision',$institution->setting('vision')) }}</textarea></label><label>Misi<textarea name="mission" rows="8" placeholder="Satu poin per baris">{{ old('mission',$institution->setting('mission')) }}</textarea></label></div></section>
<section class="card"><h2>Informasi layanan</h2><div class="stack compact"><label>Catatan pendaftaran<textarea name="registration_notes" rows="8" placeholder="Jadwal, syarat, atau keterangan pendaftaran yang sudah pasti">{{ old('registration_notes',$institution->setting('registration_notes')) }}</textarea></label><div class="alert">Data pribadi anak, akun, password, dan hasil asesmen individual tidak ditempatkan pada halaman publik.</div></div></section>
</div>
<button class="button primary" type="submit">Simpan Profil Lembaga</button>
</form>
@endsection
