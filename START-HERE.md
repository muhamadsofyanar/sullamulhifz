# MULAI DI SINI — v4.0.0

Rilis kandidat adalah **v4.0.0 — Satu Ruang Qur’an**.

Baca berurutan:
1. `UPGRADE-V4.0.0.md`
2. `DEPLOY-QUICK-V4.0.0.txt`
3. `docs/releases/v4.0.0.md`
4. `docs/PRODUCT-P1-PUBLIC-PERSONAL-V3.0.0.md`
5. `docs/ROADMAP-10-PHASES-V2.6.0.md`

v4.0.0 melanjutkan baseline v3.4.0 dan menggabungkan sepuluh workstream menjadi satu deploy. Jurnal/target selalu privat; modul hanya dapat dibuka saat enrollment aktif. Community dan pembayaran mempunyai alur nyata, tetapi feature flag-nya tetap tidak diaktifkan otomatis.

---

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
