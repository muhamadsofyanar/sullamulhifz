# Upgrade v1.9.0 — TPA Launch Complete

## Posisi rilis

v1.9.0 menggabungkan tiga fase:

1. Operasional Pembelajaran Harian.
2. Portal Wali, Rapor, dan Laporan.
3. Launch Readiness.

Paket dibangun untuk satu upload repository dan satu redeploy Coolify. Status rilis tetap **kandidat peluncuran** sampai seluruh checklist pada menu **Siap Launch** diuji menggunakan akun nyata.

## Sebelum upgrade

1. Buat backup database: `pre-v1.9.0-tpa-launch-complete`.
2. Pastikan v1.6.1 berjalan dan dua qari dapat digunakan.
3. Pastikan domain publik dan portal aktif.
4. Salin isi patch ke root repository dan pilih **Replace files in the destination**.
5. Pastikan file `RELEASE` berisi `v1.9.0`.

## Deployment

Commit yang disarankan:

```text
Release v1.9.0 — TPA Launch Complete
```

Redeploy Coolify satu kali. Startup otomatis menjalankan:

- migration additive;
- seeder template yang idempoten;
- verifikasi Academic Core;
- verifikasi Quran Learning;
- verifikasi Launch Complete;
- cache konfigurasi dan Blade;
- startup NGINX Unit yang benar.

## Log yang diharapkan

```text
=== Sullamul Hifz v1.9.0 TPA Launch Complete startup ===
Academic Core siap
Struktur Quran Learning v1.6.1 siap
TPA Launch Complete v1.9.0 siap
Menjalankan NGINX Unit...
```

## URL pemeriksaan

```text
/dashboard
/teacher/daily
/admin/launch-readiness
/admin/reports
/latihan-quran
```

## Larangan

Jangan menjalankan:

```text
php artisan migrate:fresh
php artisan db:wipe
scripts/first-install.sh
InitialTpaDataSeeder
ProductionSeeder
```
