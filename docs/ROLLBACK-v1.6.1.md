# Rollback v1.6.1

Rollback source dapat dilakukan ke tag v1.6.0 atau v1.5.1.

Migration `2026_08_06_000310_qari_tahfizh_v161` hanya mengubah konfigurasi sumber audio. Timing Al-Husary dan Al-Minshawi boleh tetap berada di database karena tidak mengganggu versi sebelumnya.

Untuk rollback penuh migration:

```bash
php artisan migrate:rollback --step=1 --force
```

Perintah tersebut akan mengaktifkan kembali Ahmad Al-Ajmi sebagai default dan menonaktifkan dua sumber v1.6.1. Backup database tetap menjadi jalur pemulihan utama.
