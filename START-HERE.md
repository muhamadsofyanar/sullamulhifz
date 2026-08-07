# Mulai di Sini — Sullamul Ḥifẓ v2.3.0

Rilis aktif: **v2.3.0 Integrated Learning Ecosystem**.

## Jalur tercepat

1. Backup database dan persistent volume `storage` di Coolify.
2. Baca `DEPLOY-QUICK-V2.3.0.txt` dan `UPGRADE-V2.3.0.md`.
3. Ekstrak paket dan salin **isi folder proyek** ke root repository GitHub.
4. Jangan mengunggah `.env`, dump database, atau isi storage produksi.
5. Pertahankan `APP_KEY` lama; gunakan `AUTO_MIGRATE=true`, `BOOTSTRAP_PRODUCTION=false`, dan `APP_DEBUG=false`.
6. Pastikan `ACADEMY_PORTAL_URL=https://academy.sullamulhifz.or.id` dan `SESSION_DOMAIN=.sullamulhifz.or.id`.
7. Push ke branch yang dipakai Coolify lalu redeploy satu kali.
8. Tunggu log `Ekosistem v2.3.0 siap` dan `Menjalankan NGINX Unit...`.
9. Uji App dan Academy, terutama `/audio`, `/jalur-belajar`, bookmark, refleksi, dan Admin → Fondasi Platform.
10. Tambahkan Scheduled Task Coolify `php artisan schedule:run` setiap menit bila belum ada.

Rilis ini tidak menjalankan `db:wipe` atau `migrate:fresh`. Seeder startup dibuat idempoten dan tidak boleh menimpa perubahan konten/feature flag yang telah dilakukan admin.
