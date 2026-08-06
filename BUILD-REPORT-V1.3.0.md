# Build Report — v1.3.0

## Pemeriksaan statis

- source disalin dari v1.2.1;
- tidak ada migration baru;
- route publik ditambahkan;
- route internal tidak dipindahkan;
- logo resmi digunakan dari `public/brand/`;
- private seed data tidak ditampilkan pada halaman publik;
- robots dan sitemap tersedia;
- paket DNS dibuat terpisah.

## Pengujian yang harus dijalankan pada CI/container

```bash
php artisan test
php artisan route:list
php artisan view:cache
php artisan optimize
```

## Smoke test produksi

- `/` = 200 tanpa login;
- `/login` = 200;
- `/dashboard` = redirect login untuk tamu;
- `app.sullamulhifz.or.id/` = redirect login/dashboard;
- seluruh halaman publik tampil baik pada desktop dan ponsel.
