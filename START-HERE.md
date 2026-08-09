# MULAI DI SINI — v4.8.0

Rilis kandidat adalah **v4.8.0 — Pendampingan Terhubung**.

## Baca berurutan

1. `UPGRADE-V4.8.0.md`
2. `DEPLOY-QUICK-V4.8.0.txt`
3. `UPLOAD-TO-GITHUB.md`
4. `docs/releases/v4.8.0.md`
5. `docs/CURRENT-STATE.md`
6. `docs/ROADMAP-10-PHASES-V2.6.0.md`

v4.8.0 meneruskan v4.5.0 yang telah berjalan dan menggabungkan tiga fase pada aplikasi yang sama: v4.6 Ustadz Privat, v4.7 Suite Lembaga, dan v4.8 Portal Keluarga. Seluruh hubungan memakai persetujuan, batas akses eksplisit, histori non-destruktif, dan isolasi workspace.

Alur paling aman: backup → salin paket ke repository → push sekali → tunggu semua GitHub Actions hijau → redeploy sekali → jalankan verifier v4.5 dan v4.8 → uji satu relasi Ustadz, satu undangan lembaga, dan satu relasi keluarga.
