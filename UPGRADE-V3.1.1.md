# Upgrade v3.1.1 — Guided Quran Learning Recovery

Patch ini digunakan setelah migration `2026_08_08_002200_guided_quran_learning_v310` gagal dan masih berstatus `Pending`.

## Prasyarat recovery

1. Pastikan migration `002200` masih `Pending`.
2. Pastikan lima tabel Guided Quran hasil percobaan sebelumnya kosong.
3. Hapus hanya lima tabel kosong tersebut dari child ke parent sebelum deploy patch ini.
4. Jangan menghapus tabel atau data Personal v3.0.0.

## Setelah deploy

1. Pastikan `/up` mengembalikan HTTP 200.
2. Pastikan `RELEASE` berisi `v3.1.1`.
3. Jalankan `php artisan migrate:status | tail -n 10`; migration `002200` harus menjadi `Ran`.
4. Jalankan `php artisan sullam:verify-guided-quran`.
5. Lanjutkan smoke test Program Juz 30, enrollment Personal, setoran audio, review asatidz, feedback, revisi/verifikasi, lalu isolasi akun Personal kedua.
