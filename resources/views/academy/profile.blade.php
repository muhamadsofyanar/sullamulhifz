@extends($academyLayout ?? 'layouts.app',['pageTitle'=>'Profil Academy'])
@section('content')
<div class="academy-profile-grid">
    <section class="academy-profile-card"><div class="academy-profile-avatar">{{ strtoupper(mb_substr($user->name,0,1)) }}</div><h1>{{ $user->name }}</h1><p>{{ $user->institution?->name }} · {{ $user->roles->pluck('display_name')->filter()->implode(', ') ?: $user->roles->pluck('name')->implode(', ') }}</p><div class="academy-profile-meta"><span><b>Program tersedia</b><strong>{{ $programs->count() }}</strong></span><span><b>Materi selesai</b><strong>{{ $completed }}/{{ $total }}</strong></span><span><b>Progres keseluruhan</b><strong>{{ $percent }}%</strong></span></div></section>
    <section class="academy-profile-settings"><span class="eyebrow">AKUN</span><h2>Keamanan akun</h2><p class="hint">Akun Academy menggunakan identitas pengguna yang sama dengan aplikasi Sullamul Ḥifẓ.</p>
        <form method="post" action="{{ route('academy.portal.profile.password') }}" class="stack">@csrf @method('PUT')<label>Kata sandi saat ini<input type="password" name="current_password" required autocomplete="current-password"></label><label>Kata sandi baru<input type="password" name="password" required autocomplete="new-password"></label><label>Ulangi kata sandi baru<input type="password" name="password_confirmation" required autocomplete="new-password"></label><button class="button primary" type="submit">Perbarui kata sandi</button></form>
        <hr style="margin:25px 0;border:0;border-top:1px solid var(--academy-line)"><p><a class="text-link" href="{{ config('sullam.portal_base_url') }}">Buka aplikasi operasional TPA →</a></p>
    </section>
</div>
@endsection
