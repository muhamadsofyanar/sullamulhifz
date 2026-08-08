# Upgrade v3.0.0 — Public Self-Registration + Personal Mode

## Dari versi

Panduan ini untuk upgrade produksi dari **v2.9.0** yang telah lulus smoke test Fase 7.

## Sebelum deploy

1. Backup database dan persistent volume `storage`.
2. Pastikan source memiliki `RELEASE` = `v3.0.0`.
3. Tunggu GitHub Actions `Tests` dan `Release Documentation Check` hijau.
4. Pertahankan `AUTO_MIGRATE=true`; Post-deployment Command Coolify tetap kosong.

## Dampak database

Migration baru: `2026_08_08_002100_public_personal_mode_v300`.

Migration bersifat additive:

- menambah `workspace_type`, `owner_user_id`, dan `privacy_mode` pada `institutions`;
- menambah `personal_profiles`, `personal_goals`, dan `personal_practice_entries`;
- menambah role `personal` dan permission `personal.use` secara idempoten;
- workspace lembaga lama otomatis tetap bertipe `institution`.

Tidak ada environment variable baru dan tidak ada tabel lama yang dihapus.

## Setelah deploy

1. Pastikan `/up` HTTP 200 dan `cat RELEASE` menampilkan `v3.0.0`.
2. Pastikan migration `public_personal_mode_v300` berstatus `Ran`.
3. Jalankan `php artisan sullam:verify-personal-mode`.
4. Buka `/pendaftaran`; pastikan jalur Personal dan TPA sama-sama tampil.
5. Daftarkan satu akun Personal uji melalui `/daftar-personal`.
6. Selesaikan onboarding, catat satu hafalan/Murāja‘ah, dan buat satu target.
7. Logout, daftar akun Personal uji kedua, dan pastikan jurnal/target akun pertama tidak terlihat.
8. Pastikan akun Guru/Admin/Wali yang sudah ada tetap masuk ke dashboard masing-masing.

## Rollback

Rollback database produksi tidak dianjurkan setelah pengguna Personal mulai membuat data. Bila deployment aplikasi bermasalah, utamakan rollback image/source ke v2.9.0 sambil mempertahankan database hasil migration sampai penyebab diketahui.

Jangan menjalankan `migrate:fresh`, `db:wipe`, atau rollback migration Personal setelah menerima pendaftaran nyata tanpa backup dan rencana pemulihan.
