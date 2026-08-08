# Upgrade v2.6.4 — Qur’an Journey Stabilization

## Tujuan

Rilis ini adalah hotfix/stabilisasi Fase 4. Tidak menambah tabel atau kolom baru.

## Sebelum deploy

1. Backup database MySQL dan persistent storage aplikasi.
2. Pastikan source GitHub benar-benar berisi `RELEASE` = `v2.6.4`.
3. Pastikan GitHub Actions `Tests` dan `Release Docs` berstatus hijau.
4. Di Coolify, pertahankan `AUTO_MIGRATE=true`.
5. Kosongkan **Post-deployment Command**; jangan jalankan `scripts/deploy.sh`.

## Deploy

Upload seluruh isi paket ke root repository `main`, commit/push, tunggu GitHub
Actions hijau, lalu lakukan **satu kali Redeploy** di Coolify.

Startup `scripts/container-start.sh` menjalankan migration yang belum pernah
berjalan, seeder idempoten, cache config/view, kemudian NGINX Unit.

## Setelah deploy

1. Pastikan `/up` mengembalikan HTTP 200.
2. Buka Guru → Qur’an Journey → salah satu santri yang berada di Juz 29.
3. Pastikan detail santri tidak lagi HTTP 500.
4. Jalankan `php artisan migrate:status`; seluruh migration sampai v2.6.3 harus `Ran`.
5. Periksa Application Logs; tidak boleh ada `ParseError` dari compiled Blade.

## Rollback

Tidak ada migration v2.6.4. Bila diperlukan, rollback aplikasi cukup dengan
mengembalikan source/image versi sebelumnya; jangan menjalankan `migrate:rollback`
hanya untuk membatalkan v2.6.4.

## Catatan dependency

Snapshot sumber yang diterima untuk v2.6.4 belum membawa `composer.lock`.
Jangan membuat lock file secara manual/fiktif. Tambahkan lock file dari Composer
2.8.x pada workstation atau CI terkontrol sebagai pekerjaan hardening berikutnya.
