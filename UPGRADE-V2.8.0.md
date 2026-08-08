# Upgrade v2.8.0 — Family & Teacher Ecosystem

## Sebelum deploy

1. Backup database dan persistent volume `storage`.
2. Pastikan source yang di-upload memiliki `RELEASE` = `v2.8.0`.
3. Tunggu GitHub Actions `Tests` dan `Release Documentation Check` hijau.
4. Pertahankan `AUTO_MIGRATE=true` dan biarkan Post-deployment Command Coolify kosong.

## Migration

Migration baru: `2026_08_08_001900_family_teacher_ecosystem_v280`.

Migration hanya menambah `family_learning_activities`, `teacher_competencies`, dan `teacher_competency_progress`. Tidak ada tabel/kolom lama yang dihapus dan tidak ada environment variable baru.

## Setelah deploy

1. Pastikan `/up` HTTP 200.
2. Pastikan migration v2.8.0 berstatus `Ran`.
3. Jalankan `php artisan sullam:verify-family-teacher`.
4. Smoke test Guru → Aktivitas Keluarga → Wali → Refleksi → Review Guru.
5. Smoke test Admin → Kompetensi Guru → Refleksi Guru → Review Admin.
6. Verifikasi pengguna lintas santri/lembaga tidak dapat mengakses data yang bukan miliknya.
7. Setelah bukti cukup, tandai launch check Fase 6 yang relevan melalui Kesiapan Peluncuran.

Fase 5 v2.7.0 tetap dipertahankan. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau rollback produksi tanpa backup dan rencana pemulihan.
