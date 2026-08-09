# MULAI DI SINI — v3.2.1

Rilis kandidat adalah **v3.2.1 — Official Bank Transfer Configuration**.

Baca berurutan:
1. `UPGRADE-V3.2.1.md`
2. `DEPLOY-QUICK-V3.2.1.txt`
3. `docs/PRODUCT-P1-PUBLIC-PERSONAL-V3.0.0.md`
4. `docs/ROADMAP-10-PHASES-V2.6.0.md`

v3.0.0 membuka jalur Personal. v3.1.x menghubungkannya dengan murattal, Program Online, Academy terpilih, setoran audio/teks, dan review asatidz. v3.2.0 menambahkan fondasi implementasi Fase 8–9 serta readiness Fase 10. v3.2.1 menambahkan konfigurasi rekening transfer resmi dan snapshot tujuan pada payment ledger. Feature flag pembayaran, multi-lembaga, community, payment provider, dan integrasi eksternal tetap tidak diaktifkan otomatis.

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
