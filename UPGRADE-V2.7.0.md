# Upgrade v2.7.0 — Academy LMS 2.0

## Sebelum deploy

1. Backup database dan persistent volume `storage`.
2. Pastikan source yang di-upload memiliki `RELEASE` = `v2.7.0`.
3. Tunggu GitHub Actions `Tests` dan `Release Documentation Check` hijau.
4. Biarkan Post-deployment Command Coolify kosong dan pertahankan `AUTO_MIGRATE=true`.

## Migration

Migration baru: `2026_08_08_001800_academy_lms_v270`.

Migration hanya menambah tabel Academy LMS 2.0. Tidak ada kolom existing yang dihapus dan tidak ada environment variable baru.

## Setelah deploy

1. Pastikan `/up` HTTP 200.
2. Jalankan `php artisan migrate:status` dan pastikan migration v2.7.0 `Ran`.
3. Buka Academy Studio dan buat satu prerequisite, satu quiz dan satu worksheet uji.
4. Sebagai peserta, verifikasi lock prerequisite, kelulusan quiz, submission worksheet dan completion lesson.
5. Tuntaskan satu program uji dan verifikasi sertifikat + URL verifikasinya.
6. Setelah bukti produksi memadai, tandai launch check Fase 5 yang relevan sebagai `done`.

`composer.lock` tetap disarankan untuk reproducible build. Jangan membuat lock file palsu.
