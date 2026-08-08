# Upgrade v2.9.0 — Personal Learning System

## Sebelum deploy

1. Backup database dan persistent volume `storage`.
2. Pastikan source memiliki `RELEASE` = `v2.9.0`.
3. Tunggu GitHub Actions `Tests` dan `Release Documentation Check` hijau.
4. Pertahankan `AUTO_MIGRATE=true`; Post-deployment Command Coolify tetap kosong.

## Migration

Migration baru: `2026_08_08_002000_personal_learning_system_v290`.

Migration hanya menambah tabel `learning_recommendation_reviews`. Tabel observasi dan insight yang sudah ada dipakai kembali. Tidak ada tabel/kolom lama yang dihapus dan tidak ada environment variable baru.

## Setelah deploy

1. Pastikan `/up` HTTP 200 dan `cat RELEASE` menampilkan `v2.9.0`.
2. Pastikan migration v2.9.0 berstatus `Ran`.
3. Jalankan `php artisan sullam:verify-personal-learning`.
4. Login sebagai guru dan buka `Personalisasi Belajar`.
5. Pilih santri ampuan, simpan minimal satu observasi nyata, lalu susun rekomendasi.
6. Uji `Terima`, `Ubah`, dan `Tolak` pada draf terpisah; pastikan keputusan tersimpan.
7. Pastikan guru yang tidak mengampu santri tidak dapat membuka/mereview rekomendasinya.
8. Pastikan STIFIn tidak muncul sebagai alasan/evidence rekomendasi.

Jangan menjalankan `migrate:fresh`, `db:wipe`, atau rollback produksi tanpa backup dan rencana pemulihan. Gate manual Fase 6 yang sebelumnya ditunda tetap pending.
