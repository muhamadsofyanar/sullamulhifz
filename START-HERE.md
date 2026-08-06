# START HERE — Sullamul Hifz v1.4.0

Current production target: **v1.4.0 TPA Operational Complete**.

Read in this order:

1. `docs/CURRENT-STATE.md`
2. `docs/UPGRADE-v1.4.0.md`
3. `docs/TEST-v1.4.0.md`
4. `docs/ROLLBACK-v1.4.0.md`
5. `docs/DATABASE-v1.4.0.md`
6. `docs/ROADMAP.md`

Critical rule: never run `db:wipe`, `migrate:fresh`, or `first-install.sh` on production.

# START HERE — Sullamul Ḥifẓ

Dokumen ini adalah pintu masuk resmi proyek. Gunakan ini ketika riwayat chat hilang, pengembang berganti, atau pekerjaan dilanjutkan dari akun/perangkat lain.

## Status terkini

- **Versi repository:** v1.2.1 — Documentation Governance
- **Versi fungsional aplikasi:** basis v1.2.0 Official Branding
- **Lingkungan produksi:** Coolify, Dockerfile, NGINX Unit, PHP 8.4, Laravel 13, MySQL 8
- **Domain sementara:** `taysriulqurani.id`
- **Data awal:** 88 santri, 88 wali, 4 guru, 6 kelas utama, 2 kelompok Tahfizh
- **Prioritas berikutnya:** v1.3.0 — Public Website & Route Separation

## Baca dalam urutan ini

1. [`docs/CURRENT-STATE.md`](docs/CURRENT-STATE.md) — kondisi nyata aplikasi sekarang.
2. [`docs/ROADMAP.md`](docs/ROADMAP.md) — urutan pengembangan versi demi versi.
3. [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — batas modul dan arah teknis.
4. [`docs/NEXT-RELEASE-v1.3.0.md`](docs/NEXT-RELEASE-v1.3.0.md) — pekerjaan terdekat.
5. [`docs/RELEASE-STANDARD.md`](docs/RELEASE-STANDARD.md) — aturan membuat rilis.
6. [`docs/UPGRADE-STANDARD.md`](docs/UPGRADE-STANDARD.md) — prosedur upgrade produksi.
7. [`docs/HANDOVER-NEXT-CHAT.md`](docs/HANDOVER-NEXT-CHAT.md) — prompt untuk melanjutkan di chat baru.

## Aturan keselamatan paling penting

- **Jangan menjalankan** `php artisan db:wipe`, `migrate:fresh`, atau `scripts/first-install.sh` pada database produksi yang sudah berisi data.
- Upgrade normal menggunakan `sh scripts/deploy.sh` atau perintah khusus yang tertulis pada panduan versi.
- Jangan unggah `.env`, password, `APP_KEY`, `DB_URL`, atau `INITIAL_TPA_DATA_KEY` ke GitHub.
- Sebelum migration produksi, buat backup database dan pastikan backup dapat dipulihkan.
- Migration yang sudah pernah dijalankan di produksi tidak boleh diedit. Tambahkan migration baru.

## Sumber kebenaran

Jika isi chat bertentangan dengan repository, gunakan urutan sumber berikut:

1. kode pada branch produksi;
2. `RELEASE` dan `public/release.txt`;
3. dokumen rilis di `docs/releases/`;
4. `CHANGELOG.md`;
5. riwayat chat.
