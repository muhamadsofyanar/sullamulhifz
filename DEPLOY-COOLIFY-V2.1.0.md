# Deploy GitHub → Coolify v2.1.0

Panduan ini mengikuti pola kerja: source diekstrak, diunggah manual ke GitHub, lalu **Redeploy** satu kali di Coolify.

## Pemetaan domain

| Domain | Fungsi | Resource |
|---|---|---|
| `sullamulhifz.or.id` | Website publik | Produksi |
| `www.sullamulhifz.or.id` | Alias website publik | Produksi |
| `app.sullamulhifz.or.id` | Portal login dan aplikasi | Produksi |
| `academy.sullamulhifz.or.id` | Pintu masuk Academy | Produksi |
| `api.sullamulhifz.or.id` | API starter | Produksi |
| `staging.sullamulhifz.or.id` | Pengujian | Resource staging terpisah |

Record Cloudflare yang sudah **Proxied** dapat dipertahankan. Tambahkan domain produksi pada resource aplikasi produksi di Coolify. Staging sebaiknya memakai branch, database, dan persistent volume terpisah. Pada resource produksi biarkan `STAGING_ENABLED=false`; pada resource staging set `STAGING_ENABLED=true`.

## Sebelum menekan Redeploy

1. Backup database MySQL.
2. Backup persistent volume `storage/app` dan `storage/logs`.
3. Pastikan mount volume lama tidak dilepas ketika image diganti.
4. Simpan salinan environment variables Coolify.
5. Untuk database produksi yang sudah berisi, pastikan:

```env
AUTO_MIGRATE=true
BOOTSTRAP_PRODUCTION=false
APP_ENV=production
APP_DEBUG=false
```

6. Pastikan `APP_KEY` lama tetap dipakai. Jangan membuat APP key baru pada database yang sudah berjalan.

## Upload ke GitHub

1. Ekstrak ZIP rilis.
2. Buka folder `sullamulhifz-v2.1.0`.
3. Salin **isi foldernya** ke root repository GitHub. Jangan membuat lapisan folder tambahan.
4. Jangan unggah `.env`, dump database, atau file dari persistent storage produksi.
5. Commit dan push ke branch yang dipakai resource Coolify.

## Redeploy satu kali

Startup container akan:

1. membersihkan cache lama;
2. menunggu database dan menjalankan migration additive;
3. menjalankan seeder launch, Academy, dan platform yang idempoten;
4. memindahkan lampiran legacy sensitif dari storage publik ke storage privat;
5. menjalankan verifier sebagai peringatan kesiapan, bukan penghenti aplikasi;
6. membuat storage link;
7. membangun config cache dan view cache;
8. menyalakan NGINX Unit;
9. menyinkronkan pustaka Audio Qur’an di latar belakang.

Pantau log sampai muncul:

```text
Menjalankan NGINX Unit...
```

## Scheduled task

Pilihan yang disarankan adalah menjalankan Laravel scheduler setiap menit:

```bash
php artisan schedule:run
```

Dengan scheduler tersebut, pembersihan media kedaluwarsa yang didefinisikan aplikasi akan berjalan otomatis. Apabila scheduler per menit belum digunakan, buat task harian alternatif:

```bash
php artisan sullam:purge-expired-media
```

## Pemeriksaan setelah deploy

```bash
sh scripts/smoke-test-v2.1.0.sh https://sullamulhifz.or.id https://app.sullamulhifz.or.id https://api.sullamulhifz.or.id
```

Kemudian uji manual:

- login admin, guru, dan wali;
- Admin → Fondasi Platform;
- pembuatan santri/guru/wali dan tautan aktivasi;
- pertemuan, absensi, tahsīn, tahfizh, dan murāja‘ah;
- tugas serta bukti file;
- lampiran Buku Penghubung;
- target pengumuman dan Pembinaan Jumat;
- Audio Qur’an dan Academy/LMS;
- logout, lalu pastikan URL media privat tidak dapat dibuka tanpa autentikasi.

## Email

Undangan akun tetap menghasilkan tautan yang dapat disalin oleh admin. Agar undangan dan reset kata sandi terkirim otomatis, isi konfigurasi SMTP pada environment Coolify dan jangan menggunakan `MAIL_MAILER=log` di produksi.
