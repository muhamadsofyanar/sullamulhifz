@extends('layouts.app',['pageTitle'=>'WhatsApp & Email'])
@section('content')
<div class="page-head communication-page-head">
    <div>
        <span class="eyebrow">PUSAT KOMUNIKASI · V4.1</span>
        <h1>WhatsApp & Email</h1>
        <p>Hubungkan kanal transaksional, uji pengiriman, atur notifikasi, dan pantau kegagalan tanpa menyimpan API key di database.</p>
    </div>
    <a class="button secondary" href="#riwayat">Lihat riwayat</a>
</div>

<section class="communication-stats" aria-label="Ringkasan komunikasi bulan ini">
    <article><span>Terkirim</span><strong>{{ number_format((int) ($stats->successful ?? 0)) }}</strong><small>Bulan ini</small></article>
    <article><span>Gagal</span><strong>{{ number_format((int) ($stats->failed ?? 0)) }}</strong><small>Perlu diperiksa</small></article>
    <article><span>Pesan masuk</span><strong>{{ number_format((int) ($stats->inbound ?? 0)) }}</strong><small>Webhook WhatsApp</small></article>
    <article><span>Mode proses</span><strong class="communication-mode">{{ strtoupper((string) config('communications.dispatch_mode')) }}</strong><small>{{ config('communications.dispatch_mode') === 'sync' ? 'Tanpa worker tambahan' : 'Memerlukan queue worker' }}</small></article>
</section>

<div class="communication-grid">
@foreach(['whatsapp' => 'WhatsApp', 'email' => 'Email'] as $channel => $label)
    @php
        $connection = $connections->get($channel);
        $state = $connectionState->get($channel, ['ready'=>false,'message'=>'Koneksi belum tersedia.','driver'=>'log']);
        $events = data_get($connection?->configuration, 'events', []);
    @endphp
    <section class="card communication-connection {{ $channel }}">
        <div class="communication-connection-head">
            <span class="communication-channel-icon">{{ $channel === 'whatsapp' ? 'WA' : '@' }}</span>
            <div><span class="eyebrow">KANAL TRANSAKSIONAL</span><h2>{{ $label }}</h2></div>
            @if($connection?->status === 'active' && $state['ready'])
                <span class="badge status-sent">Aktif & siap</span>
            @elseif($connection?->status === 'active')
                <span class="badge status-failed">Belum siap</span>
            @else
                <span class="badge">Nonaktif</span>
            @endif
        </div>

        <div class="communication-health {{ $state['ready'] ? 'ready' : 'warning' }}">
            <strong>{{ $state['ready'] ? '✓ Konfigurasi terdeteksi' : '○ Perlu dilengkapi' }}</strong>
            <span>{{ $state['message'] }}</span>
            @if($connection?->last_checked_at)<small>Terakhir diperiksa {{ $connection->last_checked_at->diffForHumans() }}</small>@endif
            @if($connection?->last_error)<small class="error-text">{{ $connection->last_error }}</small>@endif
        </div>

        @if($connection)
        <form method="post" action="{{ route('admin.communications.connections.update',$connection) }}" class="stack compact communication-settings">
            @csrf @method('put')
            <label>Provider / driver
                <select name="driver" required>
                    @foreach($driverCatalog[$channel] as $driver => $driverLabel)
                        <option value="{{ $driver }}" @selected($state['driver']===$driver)>{{ $driverLabel }}</option>
                    @endforeach
                </select>
            </label>
            <div class="communication-checks">
                <label><input type="checkbox" name="enabled" value="1" @checked($connection->status==='active')> Aktifkan kanal</label>
                <label><input type="checkbox" name="event_liaison" value="1" @checked(data_get($events,'liaison',true))> Notifikasi Buku Penghubung</label>
                <label><input type="checkbox" name="event_account_invitation" value="1" @checked(data_get($events,'account_invitation',true))> Undangan aktivasi akun</label>
                @if($channel === 'email')<label><input type="checkbox" name="event_password_reset" value="1" @checked(data_get($events,'password_reset',true))> Tautan lupa kata sandi</label>@endif
            </div>
            <button class="button primary" type="submit">Simpan konfigurasi</button>
        </form>

        <details class="communication-test">
            <summary>Uji pengiriman langsung</summary>
            <form method="post" action="{{ route('admin.communications.connections.test',$connection) }}" class="stack compact">
                @csrf
                <label>{{ $channel === 'email' ? 'Email tujuan' : 'Nomor WhatsApp tujuan' }}
                    <input name="recipient" type="{{ $channel === 'email' ? 'email' : 'text' }}" required placeholder="{{ $channel === 'email' ? 'admin@domain.or.id' : '62812xxxxxxxx' }}">
                </label>
                @if($channel === 'email')<label>Subjek<input name="subject" value="Tes integrasi Sullamul Ḥifẓ" maxlength="190"></label>@endif
                <label>Pesan<textarea name="message" rows="4" maxlength="2000" required>Assalamu’alaikum. Ini adalah tes integrasi {{ $label }} dari Sullamul Ḥifẓ.</textarea></label>
                <p class="hint">Gunakan alamat milik Anda sendiri. Tombol ini melakukan pengiriman nyata kecuali driver “Log saja” dipilih.</p>
                <button class="button secondary" type="submit">Kirim tes</button>
            </form>
        </details>
        @endif
    </section>
@endforeach
</div>

@php($waConnection = $connections->get('whatsapp'))
@if($waConnection)
<section class="card communication-webhook">
    <div>
        <span class="eyebrow">PESAN MASUK & STATUS</span>
        <h2>Webhook WhatsApp</h2>
        <p>Masukkan URL berikut pada dashboard provider. Ganti placeholder token dengan nilai <code>COMMUNICATION_WEBHOOK_SECRET</code> yang sama seperti di Coolify.</p>
    </div>
    <div class="communication-url">
        <code>{{ url('/api/v1/webhooks/communications/whatsapp/'.$waConnection->id) }}?token=YOUR_COMMUNICATION_WEBHOOK_SECRET</code>
    </div>
    <p class="hint">Endpoint menerima format StarSender (<code>from</code>, <code>message</code>, <code>timestamp</code>) dan format generik. Pesan masuk disimpan terenkripsi; payload mentah dan API key tidak disimpan.</p>
</section>
@endif

<section class="communication-readiness-grid">
    <article class="card">
        <span class="eyebrow">DELIVERABILITY EMAIL</span>
        <h2>Sebelum mengaktifkan email</h2>
        <ul class="communication-checklist">
            <li>Verifikasi <code>MAIL_FROM_ADDRESS</code> pada provider.</li>
            <li>Publikasikan SPF, DKIM, dan DMARC untuk domain pengirim.</li>
            <li>Uji Gmail dan Outlook, termasuk folder spam.</li>
            <li>Pastikan alamat reply-to dipantau oleh pengelola.</li>
        </ul>
    </article>
    <article class="card">
        <span class="eyebrow">KEPATUHAN WHATSAPP</span>
        <h2>Sebelum mengaktifkan WhatsApp</h2>
        <ul class="communication-checklist">
            <li>Kirim hanya kepada wali yang mengizinkan notifikasi.</li>
            <li>Mulai dari pesan transaksional, bukan blast promosi.</li>
            <li>Gunakan jeda provider dan pantau risiko device terputus.</li>
            <li>Jangan menyalin isi percakapan atau data sensitif anak.</li>
        </ul>
    </article>
</section>

<section class="card communication-templates">
    <div class="section-head">
        <div><span class="eyebrow">OTOMASI AMAN</span><h2>Template notifikasi</h2><p class="muted">Notifikasi Buku Penghubung sengaja tidak menyalin isi percakapan ke layanan eksternal.</p></div>
        <span class="badge">{{ $templates->count() }} template</span>
    </div>
    <div class="communication-template-grid">
        @foreach($templates as $template)
        <details>
            <summary><span><b>{{ strtoupper($template->channel) }}</b> · {{ $template->name }}</span><span class="badge {{ $template->is_active ? 'status-sent' : '' }}">{{ $template->is_active ? 'Aktif' : 'Nonaktif' }}</span></summary>
            <form method="post" action="{{ route('admin.communications.templates.update',$template) }}" class="stack compact">
                @csrf @method('put')
                @if($template->channel === 'email')<label>Subjek<input name="subject" value="{{ $template->subject }}" maxlength="190"></label>@endif
                <label>Isi template<textarea name="content" rows="9" maxlength="10000" required>{{ $template->content }}</textarea></label>
                <small>Variabel: @foreach($template->available_variables ?: [] as $variable)<code>{{ '{{'.$variable.'}}' }}</code>{{ !$loop->last ? ', ' : '' }}@endforeach</small>
                <label><input type="checkbox" name="is_active" value="1" @checked($template->is_active)> Template aktif</label>
                <button class="button secondary" type="submit">Simpan template</button>
            </form>
        </details>
        @endforeach
    </div>
</section>

<section class="card table-card" id="riwayat">
    <div class="section-head communication-log-head">
        <div><span class="eyebrow">AUDIT PENGIRIMAN</span><h2>Riwayat WhatsApp & Email</h2></div>
        <form method="get" class="communication-filters">
            <select name="channel"><option value="">Semua kanal</option><option value="whatsapp" @selected(($filters['channel']??'')==='whatsapp')>WhatsApp</option><option value="email" @selected(($filters['channel']??'')==='email')>Email</option></select>
            <select name="direction"><option value="">Semua arah</option><option value="outbound" @selected(($filters['direction']??'')==='outbound')>Keluar</option><option value="inbound" @selected(($filters['direction']??'')==='inbound')>Masuk</option></select>
            <select name="status"><option value="">Semua status</option>@foreach(['queued','sending','sent','delivered','received','failed'] as $status)<option value="{{ $status }}" @selected(($filters['status']??'')===$status)>{{ ucfirst($status) }}</option>@endforeach</select>
            <button class="button secondary small">Filter</button>
        </form>
    </div>
    <div class="table-scroll"><table>
        <thead><tr><th>Waktu</th><th>Kanal</th><th>Arah / event</th><th>Penerima/pengirim</th><th>Provider</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($deliveries as $delivery)
            @php
                $address = $delivery->recipient_address;
                $masked = str_contains($address,'@')
                    ? \Illuminate\Support\Str::mask($address,'•',2,max(1,strpos($address,'@')-2))
                    : \Illuminate\Support\Str::mask($address,'•',4,max(1,strlen($address)-6));
            @endphp
            <tr>
                <td><strong>{{ $delivery->created_at->format('d M Y') }}</strong><small>{{ $delivery->created_at->format('H:i:s') }}</small></td>
                <td><span class="communication-channel-pill {{ $delivery->channel }}">{{ strtoupper($delivery->channel) }}</span></td>
                <td><strong>{{ $delivery->direction === 'inbound' ? 'Masuk' : 'Keluar' }}</strong><small>{{ str($delivery->event_key)->replace('_',' ')->headline() }}</small></td>
                <td><strong>{{ $delivery->recipient?->name ?: ($delivery->recipient_name ?: 'Kontak eksternal') }}</strong><small>{{ $masked }}</small></td>
                <td>{{ str($delivery->provider)->headline() }}<small>Percobaan {{ $delivery->attempts }}</small></td>
                <td><span class="badge status-{{ $delivery->status }}">{{ $delivery->status }}</span>@if($delivery->last_error)<small class="error-text" title="{{ $delivery->last_error }}">{{ str($delivery->last_error)->limit(70) }}</small>@endif</td>
                <td>@if($delivery->isRetryable())<form method="post" action="{{ route('admin.communications.deliveries.retry',$delivery) }}">@csrf<button class="button ghost small">Kirim ulang</button></form>@else<span class="muted">—</span>@endif</td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">Belum ada riwayat komunikasi.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="pagination-wrap">{{ $deliveries->links() }}</div>
</section>

<section class="communication-security-note">
    <strong>Pengamanan yang diterapkan</strong>
    <span>API key hanya dibaca dari Environment Variables Coolify.</span>
    <span>Isi pesan tersimpan terenkripsi memakai APP_KEY.</span>
    <span>Alamat penerima disamarkan pada dashboard dan tidak ditulis utuh ke log aplikasi.</span>
    <span>Retry memakai idempotensi untuk notifikasi otomatis agar tidak terkirim ganda.</span>
</section>
@endsection
