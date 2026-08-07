# Mulai di Sini — Sullamul Ḥifẓ v2.1.0

Rilis aktif: **v2.1.0 Unified Platform & Secure Media**.

## Jalur tercepat

1. Backup database dan persistent volume `storage` di Coolify.
2. Baca `DEPLOY-QUICK-V2.1.0.txt` dan `UPGRADE-V2.1.0.md`.
3. Ekstrak paket dan salin **isi folder proyek** ke root repository GitHub.
4. Jangan mengunggah `.env`, dump database, atau isi storage produksi.
5. Pastikan `APP_KEY` lama tetap dipakai, `AUTO_MIGRATE=true`, `BOOTSTRAP_PRODUCTION=false`, dan `APP_DEBUG=false`.
6. Push ke branch yang dipakai Coolify, lalu redeploy satu kali.
7. Tunggu log `Menjalankan NGINX Unit...`.
8. Jalankan `scripts/smoke-test-v2.1.0.sh` dan uji login admin/guru/wali.
9. Tambahkan Scheduled Task Coolify `php artisan schedule:run` setiap menit.

Rilis ini tidak menjalankan `db:wipe` atau `migrate:fresh`. `ProductionSeeder` hanya dijalankan ketika `BOOTSTRAP_PRODUCTION=true`.
