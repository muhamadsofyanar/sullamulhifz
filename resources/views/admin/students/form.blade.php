@extends('layouts.app',['pageTitle'=>$student->exists?'Edit Santri':'Tambah Santri'])
@section('content')
<div class="page-head"><div><h1>{{ $student->exists?'Edit data santri':'Tambah santri' }}</h1><p>Data minimum dahulu. Catatan tambahan dapat dilengkapi kemudian.</p></div></div>
<form class="stack" method="post" enctype="multipart/form-data" action="{{ $student->exists?route('admin.students.update',$student):route('admin.students.store') }}">@csrf @if($student->exists)@method('PUT')@endif
<div class="grid two"><section class="card"><h2>Identitas</h2><div class="form-grid">
<label>Nama lengkap*<input name="full_name" value="{{ old('full_name',$student->full_name) }}" required></label>
<label>Nama panggilan<input name="nickname" value="{{ old('nickname',$student->nickname) }}"></label>
<label>Kode santri<input name="student_code" value="{{ old('student_code',$student->student_code) }}" placeholder="Otomatis jika kosong"></label>
<label>Jenis kelamin<select name="gender"><option value="">Pilih</option><option value="male" @selected(old('gender',$student->gender)==='male')>Laki-laki</option><option value="female" @selected(old('gender',$student->gender)==='female')>Perempuan</option></select></label>
<label>Tempat lahir<input name="birth_place" value="{{ old('birth_place',$student->birth_place) }}"></label>
<label>Tanggal lahir<input type="date" name="birth_date" value="{{ old('birth_date',optional($student->birth_date)->format('Y-m-d')) }}"></label>
<label>Tanggal masuk<input type="date" name="joined_at" value="{{ old('joined_at',optional($student->joined_at)->format('Y-m-d')) }}"></label>
<label>Status<select name="status"><option value="active" @selected(old('status',$student->status)==='active')>Aktif</option><option value="leave" @selected(old('status',$student->status)==='leave')>Cuti</option><option value="moved" @selected(old('status',$student->status)==='moved')>Pindah</option><option value="graduated" @selected(old('status',$student->status)==='graduated')>Lulus</option><option value="stopped" @selected(old('status',$student->status)==='stopped')>Berhenti</option></select></label>
<label>Kelas aktif*<select name="class_id" required><option value="">Pilih kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((int)old('class_id',$currentClassId)===$class->id)>{{ $class->name }}</option>@endforeach</select></label>
<label class="span-2">Foto santri<input type="file" name="photo" accept="image/*"></label>
<label class="span-2">Alamat<textarea name="address">{{ old('address',$student->address) }}</textarea></label>
<label class="span-2">Catatan kebutuhan khusus<textarea name="special_needs_notes">{{ old('special_needs_notes',$student->special_needs_notes) }}</textarea></label>
</div></section>
<section class="card"><h2>Personalisasi</h2><div class="form-grid"><label>Status STIFIn<select name="stifin_status"><option value="untested" @selected(old('stifin_status',$student->stifin_status)==='untested')>Belum Dites</option><option value="tested" @selected(old('stifin_status',$student->stifin_status)==='tested')>Sudah Dites</option></select></label><label>Hasil STIFIn, opsional<input name="stifin_result" value="{{ old('stifin_result',$student->stifin_result) }}"></label></div><p class="hint">STIFIn hanya informasi tambahan dan tidak menentukan kemampuan, kelas, atau marhalah.</p>
@if(!$student->exists)<hr><h2>Wali utama</h2><p class="hint">Bagian ini opsional. Kata sandi awal harus segera diganti oleh wali.</p><div class="form-grid"><label>Nama wali<input name="guardian_name" value="{{ old('guardian_name') }}"></label><label>Hubungan<select name="guardian_relationship"><option value="mother">Ibu</option><option value="father">Ayah</option><option value="guardian">Wali</option><option value="grandparent">Kakek/Nenek</option></select></label><label>Nomor telepon<input name="guardian_phone" value="{{ old('guardian_phone') }}"></label><label>Email<input type="email" name="guardian_email" value="{{ old('guardian_email') }}"></label><label class="span-2">Kata sandi awal<input type="password" name="guardian_password" minlength="12" placeholder="Opsional. Kosongkan untuk membuat akun undangan yang diaktifkan melalui tautan aman"></label></div>@endif
</section></div>
<div class="form-actions"><a class="button ghost" href="{{ route('admin.students.index') }}">Batal</a><button class="button primary">Simpan Santri</button></div></form>
@endsection
