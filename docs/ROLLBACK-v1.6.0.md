# Rollback v1.6.0

## Rollback aplikasi tanpa menghapus data

Cara paling aman:

1. Pilih deployment stabil v1.5.1 pada Coolify.
2. Rollback container/source.
3. Jangan menjalankan `migrate:rollback` kecuali ada alasan teknis dan backup tersedia.

Tabel v1.6.0 boleh tetap berada di database karena source v1.5.1 tidak menggunakannya.

## Rollback database penuh

Hanya dilakukan pada maintenance window setelah backup teruji:

```bash
php artisan migrate:rollback --step=1 --force
```

Perintah tersebut menghapus tabel Quran Learning dan seluruh sesi/preset/video/timing di dalamnya. Perintah tidak boleh dijalankan sekadar untuk memperbaiki tampilan atau sinkronisasi.
