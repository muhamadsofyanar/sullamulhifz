@extends('layouts.app',['pageTitle'=>'Profil'])
@section('content')
@if($user->must_change_password)<div class="alert danger"><strong>Kata sandi awal harus diganti.</strong> Gunakan kata sandi yang hanya diketahui oleh Anda.</div>@endif
<div class="page-head"><div><h1>Profil akun</h1><p>{{ $user->email ?: $user->phone }}</p></div></div>
<div class="grid two"><section class="card"><h2>Informasi akun</h2><dl class="details"><dt>Nama</dt><dd>{{ $user->name }}</dd><dt>Email</dt><dd>{{ $user->email ?: '—' }}</dd><dt>Nomor telepon</dt><dd>{{ $user->phone ?: '—' }}</dd><dt>Peran</dt><dd>{{ $user->roles->pluck('display_name')->join(', ') }}</dd></dl></section>
<section class="card"><h2>Ubah kata sandi</h2><form class="stack" method="post" action="{{ route('profile.password') }}">@csrf @method('PUT')<label>Kata sandi saat ini<input type="password" name="current_password" required></label><label>Kata sandi baru<input type="password" name="password" required></label><label>Ulangi kata sandi baru<input type="password" name="password_confirmation" required></label><button class="button primary" type="submit">Simpan kata sandi</button></form></section></div>
@endsection
