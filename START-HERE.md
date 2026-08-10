# MULAI DI SINI — v5.3.0

Rilis kandidat adalah **v5.3.0 — Empat Fase, Satu Deploy**.

Paket ini meneruskan v4.9.0 dan menggabungkan empat fase berikut dalam satu upload GitHub dan satu redeploy Coolify:

- **Fase 9 / v5.0.0 — Business, Payment & Integrations**
- **Fase 10 / v5.1.0 — SaaS Production Readiness**
- **Fase 11 / v5.2.0 — Pendamping Cerdas + human review**
- **Fase 12 / v5.3.0 — Mobile/PWA, offline-safe & global preferences**

## Baca berurutan

1. `UPGRADE-V5.3.0.md`
2. `DEPLOY-QUICK-V5.3.0.txt`
3. `UPLOAD-TO-GITHUB.md`
4. `docs/releases/v5.3.0.md`
5. `docs/CURRENT-STATE.md`
6. `docs/ROADMAP.md`
7. `docs/PHASE-REGISTRY.md`

Alur paling aman: **backup → upload/push sekali → tunggu semua GitHub Actions hijau → redeploy sekali → migration additive berjalan → jalankan `sullam:verify-release-v530` → smoke test akun nyata**.

Jangan menjalankan `migrate:fresh`, `db:wipe`, atau seeder demo pada database produksi.


Catatan Fase 9: preset berbayar tidak diaktifkan dan tidak diberi harga produksi secara otomatis. Hanya **Personal Gratis** yang aktif; superadmin harus menetapkan harga nyata sebelum mengaktifkan paket berbayar.
