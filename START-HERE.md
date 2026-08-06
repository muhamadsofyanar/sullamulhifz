# START HERE — Sullamul Ḥifẓ

Dokumen ini adalah pintu masuk resmi proyek ketika riwayat chat hilang atau pengembang berganti.

## Status rilis

- **Produksi sebelum upgrade ini:** v1.6.0, Quran Learning Complete.
- **Paket repository ini:** v1.6.1 — Qari Tahfizh.
- **Website publik:** `https://sullamulhifz.or.id`.
- **Portal aplikasi:** `https://app.sullamulhifz.or.id`.
- **Domain lama:** `https://taysriulqurani.id`, masih dipertahankan sebagai cadangan transisi.

## Data yang harus dipertahankan

- 88 santri;
- 88 wali;
- 4 guru: Nurul, Jundi, Yanti, dan Sofyan;
- 6 kelas utama;
- Tahfizh A: 30 santri;
- Tahfizh B: 27 santri.

## Fitur inti aktif

- website publik dan referensi TPA Al-Insyirah;
- portal admin, guru, dan wali;
- Academic Core, target hafalan, observasi metode belajar;
- Ikrar Santri;
- pemisahan domain publik dan portal;
- Quran Learning: audio Juz 30, pengulangan ayat/rentang/surah/halaman/rubu’, target santri, sesi latihan, dan video terkurasi.

## Urutan membaca

1. `docs/CURRENT-STATE.md`
2. `docs/QARI-TAHFIZH-v1.6.1.md`
3. `UPGRADE-V1.6.1.md`
4. `docs/QURAN-LEARNING-v1.6.0.md`
5. `docs/TEST-v1.6.1.md`
6. `docs/DATABASE-v1.6.1.md`
7. `docs/QURAN-LEARNING-v1.6.0.md`
8. `docs/ROLLBACK-v1.6.1.md`
9. `docs/ROADMAP.md`
10. `docs/ARCHITECTURE.md`
11. `docs/HANDOVER-NEXT-CHAT.md`
12. `CHANGELOG.md`

## Aturan keselamatan

- Jangan menjalankan `db:wipe`, `migrate:fresh`, `scripts/first-install.sh`, atau `ProductionSeeder` pada produksi.
- Migration yang sudah pernah berjalan tidak boleh diedit; tambahkan migration baru.
- Backup database sebelum upgrade.
- Jangan mengunggah `.env`, `APP_KEY`, `DB_URL`, password, dump database, atau `INITIAL_TPA_DATA_KEY`.
- Jangan mengisi progres santri secara fiktif. Istilah 100% pada Pustaka Qur’an berarti 1.128 timing tersedia—564 untuk setiap qari—bukan progres santri.
- Video hanya diterbitkan setelah sumber dan izin tayangnya diperiksa.

## Sumber kebenaran

1. kode pada commit yang benar-benar dideploy;
2. deployment Coolify dan status migration;
3. `RELEASE` dan `public/release.txt`;
4. `docs/CURRENT-STATE.md`;
5. dokumen rilis;
6. `CHANGELOG.md`;
7. riwayat chat.
