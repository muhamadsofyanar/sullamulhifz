# Mulai di Sini — Sullamul Ḥifẓ v2.5.0

Rilis kandidat: **v2.5.0 Tahfizh Learning Engine**.

## Sebelum deploy
1. Backup database dan persistent volume `storage` di Coolify.
2. Baca `DEPLOY-QUICK-V2.5.0.txt` dan `UPGRADE-V2.5.0.md`.
3. Salin **isi folder proyek** ke root repository GitHub; jangan upload folder pembungkus.
4. Jangan upload `.env`, dump database, atau isi storage produksi.
5. Pertahankan `APP_KEY` lama dan gunakan `AUTO_MIGRATE=true`, `BOOTSTRAP_PRODUCTION=false`, `APP_DEBUG=false`.
6. Push branch `main`, lalu Redeploy Coolify sekali.

Full Qur’an tetap berjalan seperti v2.4.x. v2.5.0 menambahkan Fase 3 secara additive: siklus Tahfizh, jadwal penjagaan, fokus koreksi, dan alur guru–wali.

## Setelah deploy
- cek **Guru → Perjalanan Tahfizh**;
- cek **Admin → Pustaka Qur’an**;
- cek **Academy → Audio Qur’an**;
- cek **Admin → Fondasi Platform** untuk status resmi 10 fase;
- gunakan **Admin → Kesiapan Peluncuran** untuk menyimpan bukti validasi produksi.

Launch penuh belum direkomendasikan sampai seluruh 10 fase mencapai 100%.
