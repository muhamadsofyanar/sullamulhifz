# Mulai di Sini — Sullamul Ḥifẓ v2.4.0

Rilis kandidat: **v2.4.0 Full Qur’an & Mushaf Engine**.

## Sebelum deploy
1. Backup database dan persistent volume `storage` di Coolify.
2. Baca `DEPLOY-QUICK-V2.4.0.txt` dan `UPGRADE-V2.4.0.md`.
3. Salin **isi folder proyek** ke root repository GitHub; jangan upload folder pembungkus.
4. Jangan upload `.env`, dump database, atau isi storage produksi.
5. Pertahankan `APP_KEY` lama dan gunakan `AUTO_MIGRATE=true`, `BOOTSTRAP_PRODUCTION=false`, `APP_DEBUG=false`.
6. Push branch `main`, lalu Redeploy Coolify sekali.

Web tidak menunggu seluruh audio selesai. Full Qur’an corpus dan dua qari dilengkapi di background dan dapat dilanjutkan pada redeploy berikutnya tanpa menghapus bagian yang sudah lengkap.

## Setelah deploy
- cek **Admin → Pustaka Qur’an**;
- cek **Academy → Audio Qur’an**;
- cek **Admin → Fondasi Platform** untuk status resmi 10 fase;
- gunakan **Admin → Kesiapan Peluncuran** untuk menyimpan bukti validasi produksi.

Launch penuh belum direkomendasikan sampai seluruh 10 fase mencapai 100%.
