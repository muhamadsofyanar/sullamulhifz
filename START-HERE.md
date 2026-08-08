# Mulai di Sini — Sullamul Ḥifẓ v2.5.2

Rilis kandidat: **v2.5.2 Tahfizh Unified Workflow**.

Fokus rilis ini adalah penutupan UX Fase 3. Pencatatan individual Setoran dan Murāja‘ah sekarang berada langsung di halaman **Perjalanan Tahfizh** santri. Operasional Hari Ini tetap digunakan untuk pencatatan massal satu kelas.

## Sebelum deploy
1. Backup database dan persistent volume `storage` di Coolify.
2. Baca `DEPLOY-QUICK-V2.5.2.txt` dan `UPGRADE-V2.5.2.md`.
3. Salin isi folder proyek ke root repository GitHub.
4. Pertahankan environment produksi yang sudah berjalan.
5. Push `main`, lalu Redeploy Coolify sekali.

## Setelah deploy
Validasi Fase 3: buat setoran individual, pilih fokus koreksi, buat jadwal Murāja‘ah, catat Murāja‘ah, lalu cek histori dan tampilan mobile.

Launch penuh tetap menunggu seluruh 10 fase dan production gate mencapai 100%.
