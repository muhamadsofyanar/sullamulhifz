# Upgrade ke v1.3.0 — Public Website & Route Separation

Rilis ini menambahkan website publik tanpa mengubah tabel database.

## Sebelum upgrade

1. Pastikan aplikasi v1.2.x berjalan normal.
2. Buat backup database melalui Coolify.
3. Pastikan environment lama tetap tersimpan.

## Upgrade produksi

1. Upload seluruh source v1.3.0 ke branch `main`.
2. Redeploy aplikasi di Coolify.
3. Buka Terminal aplikasi dan jalankan:

```bash
cd /var/www/html
php artisan optimize:clear
php artisan optimize
```

Jangan menjalankan `first-install.sh`, `db:wipe`, `migrate:fresh`, atau seeder ulang.

## Environment opsional

```env
PUBLIC_SITE_URL=https://sullamulhifz.or.id
PORTAL_URL=https://app.sullamulhifz.or.id/login
PORTAL_HOST=app.sullamulhifz.or.id
PUBLIC_CONTACT_EMAIL=info@sullamulhifz.or.id
```

Environment ini boleh ditambahkan setelah domain aktif. Tanpa variabel tersebut, website tetap dapat dibuka pada domain aplikasi saat ini.

## Pengujian

- `/` menampilkan website publik tanpa login.
- `/login` tetap menampilkan form login.
- `/dashboard` tetap meminta autentikasi.
- host `app.sullamulhifz.or.id/` mengarah ke login/dashboard.
- data santri, guru, wali, kelas, dan Tahfizh tetap utuh.
