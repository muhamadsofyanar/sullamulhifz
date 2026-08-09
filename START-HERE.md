# MULAI DI SINI — v4.4.2

Rilis kandidat adalah **v4.4.2 — Blade Compilation & Release Docs Hotfix**.

## Baca berurutan

1. `UPGRADE-V4.4.2.md`
2. `DEPLOY-QUICK-V4.4.2.txt`
3. `UPLOAD-TO-GITHUB.md`
4. `docs/releases/v4.4.2.md`
5. `docs/CURRENT-STATE.md`
6. `docs/ROADMAP-10-PHASES-V2.6.0.md`

v4.4.2 meneruskan seluruh isi v4.4.0 dan dua perbaikan kompilasi Blade pada Pusat Komunikasi. Rilis ini tidak mengubah database; komunikasi v4.1.0 tidak dibangun ulang dan seluruh kredensial tetap berada di environment.

Alur paling aman: backup → salin paket ke repository → push sekali → tunggu GitHub Actions hijau → redeploy sekali → verifikasi migration dan switcher workspace → uji pendaftaran Personal/lembaga → tes komunikasi admin.
