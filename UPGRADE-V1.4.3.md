# Upgrade v1.4.3 — Ikrar Santri

## Karakter upgrade

- Tidak ada migration baru.
- Tidak ada seeder.
- Tidak menghapus atau mengubah 88 data santri.
- Tidak mengubah akun guru dan wali.

## Prosedur

1. Backup source dan database sebelum deployment produksi.
2. Upload isi patch ke root repository.
3. Commit dan push.
4. Tunggu Coolify menyelesaikan rolling update.
5. Jangan menjalankan `db:wipe`, `migrate:fresh`, atau initial seeder.
6. Buka `/ikrar-santri`.
7. Login dan buka `/nilai/ikrar-santri`.
8. Login sebagai admin dan buka `/admin/ikrar-santri`.
9. Simpan satu perubahan kecil untuk memastikan `system_settings` tersedia.
10. Uji tombol cetak pada desktop dan ponsel.

## Bila editor admin menampilkan 503

Artinya tabel `system_settings` belum tersedia. Halaman publik tetap bekerja menggunakan data default. Periksa status migration v1.4.0 sebelum menjalankan migration apa pun pada produksi.

```bash
php artisan migrate:status
```

Jangan menjalankan migration sebelum backup tersedia.

## Rollback

Rollback source ke commit sebelumnya. Tidak ada tabel atau kolom v1.4.2 yang perlu dihapus. Data `student_pledge` pada `system_settings` boleh dibiarkan karena tidak memengaruhi versi lama.
