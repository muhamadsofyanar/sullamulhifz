# MULAI DI SINI — v4.1.0

Rilis kandidat adalah **v4.1.0 — WhatsApp & Email Completion**.

## Baca berurutan

1. `UPGRADE-V4.1.0.md`
2. `DEPLOY-QUICK-V4.1.0.txt`
3. `UPLOAD-TO-GITHUB.md`
4. `docs/releases/v4.1.0.md`
5. `docs/CURRENT-STATE.md`
6. `docs/ROADMAP-10-PHASES-V2.6.0.md`

v4.1.0 melanjutkan baseline v4.0.0 dan menutup scaffold integrasi WhatsApp/email. Rilis ini additive: koneksi tetap nonaktif sampai environment provider dan toggle admin disiapkan. Isi delivery disimpan terenkripsi memakai `APP_KEY`; API key tidak masuk database.

Alur paling aman: backup → salin paket ke repository → push sekali → tunggu GitHub Actions hijau → lengkapi environment Coolify → redeploy sekali → tes ke kontak admin → baru aktifkan notifikasi operasional.
