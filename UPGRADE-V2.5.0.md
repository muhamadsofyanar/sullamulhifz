# Upgrade v2.5.0 — Tahfizh Learning Engine

## Sebelum deploy
1. Backup database.
2. Backup persistent volume `storage`.
3. Pastikan source v2.4.1 sudah berjalan.
4. Jangan mengubah `APP_KEY`.

## Perubahan database
Migration additive baru:
`2026_08_07_001300_tahfizh_learning_engine_v250.php`

Migration membuat siklus belajar Tahfizh, jadwal penjagaan dan fokus koreksi serta menambah penghubung pada catatan setoran/Murāja‘ah. Data lama tidak dihapus.

## Setelah deploy
Startup otomatis menjalankan:
- migration;
- `TahfizhLearningEngineV250Seeder`;
- `sullam:verify-tahfizh`;
- roadmap status.

## Validasi awal
1. Login sebagai guru.
2. Buka **Perjalanan Tahfizh**.
3. Pilih santri.
4. Buat/lihat siklus belajar.
5. Buat jadwal Murāja‘ah.
6. Mulai pertemuan lalu catat setoran dan Murāja‘ah.
7. Login sebagai wali dan pastikan hanya arahan anak terkait yang terlihat.

Jangan menandai Fase 3 100% sebelum checklist produksi Fase 3 selesai.
