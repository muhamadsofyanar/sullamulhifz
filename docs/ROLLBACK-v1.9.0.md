# Rollback v1.9.0

## Rollback aplikasi

1. Simpan log dan catat commit yang bermasalah.
2. Rollback deployment Coolify ke image v1.6.1 terakhir yang stabil.
3. Jangan langsung menjalankan `migrate:rollback` bila data v1.9.0 sudah dipakai.

Aplikasi v1.6.1 akan mengabaikan kolom tambahan. Ini lebih aman daripada menghapus data operasional baru.

## Rollback database

Rollback migration hanya dilakukan setelah backup dan keputusan eksplisit karena akan menghapus:

- checklist launch;
- detail penilaian tambahan;
- hubungan setoran dengan target;
- ringkasan wali pada pertemuan;
- konfigurasi audio pada tugas.

Perintah darurat, setelah backup:

```bash
php artisan migrate:rollback --step=1 --force
```

Jangan menggunakan `migrate:fresh` atau `db:wipe`.
