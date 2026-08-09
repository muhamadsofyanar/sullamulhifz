# MULAI DI SINI — v4.4.0

Rilis kandidat adalah **v4.4.0 — Universal Product, Identity Core & Multi-tenant Foundation**.

## Baca berurutan

1. `UPGRADE-V4.4.0.md`
2. `DEPLOY-QUICK-V4.4.0.txt`
3. `UPLOAD-TO-GITHUB.md`
4. `docs/releases/v4.4.0.md`
5. `docs/CURRENT-STATE.md`
6. `docs/ROADMAP-10-PHASES-V2.6.0.md`

v4.4.0 melanjutkan baseline v4.1.0 dan menggabungkan tiga fase: reposisi produk publik, identity/relationship core, serta fondasi multi-tenant. Rilis ini additive; komunikasi v4.1.0 tidak dibangun ulang dan seluruh kredensial tetap berada di environment.

Alur paling aman: backup → salin paket ke repository → push sekali → tunggu GitHub Actions hijau → redeploy sekali → verifikasi migration dan switcher workspace → uji pendaftaran Personal/lembaga → tes komunikasi admin.
