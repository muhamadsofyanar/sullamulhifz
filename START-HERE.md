# MULAI DI SINI — v2.8.0

Rilis aktif adalah **v2.8.0 — Family & Teacher Ecosystem**.

Baca berurutan:
1. `UPGRADE-V2.8.0.md`
2. `DEPLOY-QUICK-V2.8.0.txt`
3. `docs/PHASE-06-FAMILY-TEACHER-V2.8.0.md`
4. `docs/ROADMAP-10-PHASES-V2.6.0.md`

v2.8.0 menutup gap implementasi utama Fase 6: aktivitas keluarga terstruktur dan kompetensi/pelatihan guru. Keduanya menggunakan refleksi dan status proses, bukan skor/ranking. Fase 6 baru 100% setelah alur Parent↔Teacher dan guardrail STIFIn benar-benar divalidasi di produksi.

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
