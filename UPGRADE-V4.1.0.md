# Upgrade v4.1.0 — WhatsApp & Email Completion

## Sifat upgrade

- Migration additive: menambah `communication_templates`, `communication_deliveries`, dan kolom health pada `integration_connections`.
- Tidak menghapus atau mengubah data santri, wali, guru, pembelajaran, media, pembayaran, maupun Personal Mode.
- WhatsApp dan email tetap nonaktif sesudah deploy sampai konfigurasi admin diaktifkan.

## Sebelum deploy

1. Backup MySQL dan persistent volume `storage`.
2. Pertahankan `APP_KEY` produksi. Isi delivery dienkripsi dengan kunci ini; menggantinya akan membuat data terenkripsi lama tidak terbaca.
3. Tambahkan environment provider yang akan dipakai. Jangan isi token di source/GitHub.
4. Pertahankan `COMMUNICATION_DISPATCH_MODE=sync` untuk deployment satu-container saat ini.

## Pilihan environment

### StarSender WhatsApp

```env
STARSENDER_API_KEY=ISI_DI_COOLIFY
COMMUNICATION_WEBHOOK_SECRET=TOKEN_ACAK_PANJANG
```

### WhatsApp provider generik

```env
WHATSAPP_WEBHOOK_ENDPOINT=https://provider.example/api/send
WHATSAPP_WEBHOOK_TOKEN=ISI_DI_COOLIFY
WHATSAPP_WEBHOOK_FORMAT=json
WHATSAPP_WEBHOOK_RECIPIENT_FIELD=to
WHATSAPP_WEBHOOK_MESSAGE_FIELD=message
WHATSAPP_WEBHOOK_REFERENCE_FIELD=reference_id
# Bila provider meminta token di body, isi nama field berikut.
WHATSAPP_WEBHOOK_TOKEN_FIELD=
COMMUNICATION_WEBHOOK_SECRET=TOKEN_ACAK_PANJANG
```

### Email SMTP (termasuk KIRIM.EMAIL/Mailketing SMTP)

```env
MAIL_MAILER=smtp
MAIL_HOST=HOST_PROVIDER
MAIL_PORT=587
MAIL_SCHEME=null
MAIL_USERNAME=ISI_DI_COOLIFY
MAIL_PASSWORD=ISI_DI_COOLIFY
MAIL_FROM_ADDRESS=noreply@sullamulhifz.or.id
MAIL_FROM_NAME="Sullamul Hifz"
```

### Mailketing API

```env
MAILKETING_API_TOKEN=ISI_DI_COOLIFY
MAIL_FROM_ADDRESS=noreply@sullamulhifz.or.id
MAIL_FROM_NAME="Sullamul Hifz"
```

## Sesudah deploy

1. Masuk sebagai admin → **WhatsApp & Email**.
2. Pilih driver, simpan tanpa mengaktifkan kanal, dan pastikan indikator konfigurasi terdeteksi.
3. Aktifkan satu kanal, lalu kirim tes hanya ke nomor/email admin.
4. Untuk WhatsApp masuk, salin URL webhook dari halaman admin ke dashboard provider.
5. Uji undangan akun, lupa kata sandi, dan satu alur Buku Penghubung admin → wali.
6. Periksa riwayat delivery dan pastikan tidak ada status `failed`.

## Rollback

Nonaktifkan kanal dari UI untuk menghentikan pengiriman tanpa rollback aplikasi. Rollback schema hanya dilakukan setelah backup karena akan menghapus riwayat komunikasi v4.1.0.
