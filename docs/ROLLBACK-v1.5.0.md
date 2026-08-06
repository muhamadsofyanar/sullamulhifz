# Rollback v1.5.0

## Rollback aplikasi

1. Gunakan fitur Rollback Coolify ke image/commit v1.4.5.
2. Pastikan domain publik dan portal kembali normal.
3. Jangan langsung menjalankan `migrate:rollback` pada production.

Tabel baru v1.5.0 tidak mengganggu source v1.4.5 karena source lama tidak membacanya.

## Rollback database penuh

Hanya dilakukan bila ada kerusakan data yang terverifikasi:

1. hentikan input baru;
2. simpan bukti dan log;
3. restore backup `pre-v1.5.0-academic-core`;
4. deploy kembali v1.4.5;
5. verifikasi 88 santri, 88 wali, 4 guru, 6 kelas, Tahfizh A dan B.

## Jangan lakukan

- Jangan menghapus tabel secara manual ketika aplikasi sedang dipakai.
- Jangan menjalankan `migrate:fresh`.
- Jangan menjalankan initial seeder pada database production.
