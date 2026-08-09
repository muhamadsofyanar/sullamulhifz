# MULAI DI SINI — v4.4.3

Rilis kandidat adalah **v4.4.3 — Blade Directive Structure Hotfix**.

## Baca berurutan

1. `UPGRADE-V4.4.3.md`
2. `DEPLOY-QUICK-V4.4.3.txt`
3. `UPLOAD-TO-GITHUB.md`
4. `docs/releases/v4.4.3.md`
5. `docs/CURRENT-STATE.md`
6. `docs/ROADMAP-10-PHASES-V2.6.0.md`

v4.4.3 meneruskan seluruh isi v4.4.2 dan menata ulang directive Blade pada Pusat Komunikasi agar setiap blok kontrol berdiri pada baris yang jelas dan seimbang. Rilis ini tidak mengubah database; komunikasi v4.1.0 tidak dibangun ulang dan seluruh kredensial tetap berada di environment.

Alur paling aman: backup → salin paket ke repository → push sekali → tunggu GitHub Actions hijau → redeploy sekali → verifikasi migration dan switcher workspace → uji pendaftaran Personal/lembaga → tes komunikasi admin.
