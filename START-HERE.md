# MULAI DI SINI — v2.9.0

Rilis aktif adalah **v2.9.0 — Personal Learning System**.

Baca berurutan:
1. `UPGRADE-V2.9.0.md`
2. `DEPLOY-QUICK-V2.9.0.txt`
3. `docs/PHASE-07-PERSONAL-LEARNING-V2.9.0.md`
4. `docs/ROADMAP-10-PHASES-V2.6.0.md`

v2.9.0 mengaktifkan Fase 7: evidence belajar → draf rekomendasi → teacher override. STIFIn tidak menjadi input mesin rekomendasi. Dua gate manual Fase 6 yang ditunda tetap berstatus pending dan tidak dianggap lulus otomatis karena pengembangan sudah bergerak ke Fase 7.

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
