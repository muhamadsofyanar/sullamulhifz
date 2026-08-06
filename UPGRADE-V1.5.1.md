# Upgrade v1.5.1 — NGINX Unit Startup Hotfix

## Masalah

Startup v1.5.0 menjalankan `unitd --no-daemon` secara langsung setelah migrasi. Pada image resmi NGINX Unit, konfigurasi `/docker-entrypoint.d/unit.json` diterapkan oleh `/usr/local/bin/docker-entrypoint.sh`. Karena entrypoint tersebut terlewati, Unit berjalan tanpa listener dan aplikasi Laravel, sehingga proxy menerima 502 Bad Gateway.

## Perbaikan

Baris terakhir `scripts/container-start-v1.5.0.sh` diubah menjadi:

```sh
exec /usr/local/bin/docker-entrypoint.sh unitd --no-daemon
```

Migration, data, route, dan fitur Academic Core tidak diubah.

## Instalasi

1. Salin isi patch ke root repository dan pilih Replace/Timpa.
2. Commit dan push.
3. Redeploy Coolify satu kali.
4. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau seeder instalasi awal.

## Log sukses

Log harus menampilkan:

- `Applying configuration /docker-entrypoint.d/unit.json`
- `"laravel" prototype started`
- `"laravel" application started`
- respons HTTP status `200`
