# Upgrade v1.4.4 — Institution Reference

## Dampak

- Menambah dua halaman publik.
- Menambah konfigurasi profil referensi lembaga.
- Menambah satu gambar referensi yang diberikan pengguna.
- Tidak mengubah database.

## Langkah

1. Backup repository lokal.
2. Salin isi upgrade patch ke root repository.
3. Commit dan push.
4. Redeploy aplikasi di Coolify.
5. Periksa:
   - `/lembaga/tpa-al-insyirah`
   - `/referensi-lembaga`
   - `/tpa`
   - `/ikrar-santri`

## Tidak perlu dijalankan

- `php artisan migrate`
- `php artisan db:seed`
- `php artisan migrate:fresh`
- `php artisan db:wipe`

## Rollback

Kembalikan commit v1.4.4 lalu redeploy commit sebelumnya. Database tidak perlu dipulihkan karena rilis ini tidak mengubah skema atau data.
