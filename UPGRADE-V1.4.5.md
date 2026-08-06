# Upgrade v1.4.5 — Portal Domain Separation

## Tujuan

Memisahkan website publik dan portal autentikasi pada deployment Laravel yang sama:

- `sullamulhifz.or.id` — website publik;
- `www.sullamulhifz.or.id` — redirect permanen ke domain tanpa `www`;
- `app.sullamulhifz.or.id` — login, dashboard, dan seluruh portal operasional;
- `taysriulqurani.id` — tetap berfungsi sebagai domain transisi/cadangan.

## Dampak database

Tidak ada migration, seeder, atau perubahan data.

## Environment Variables di Coolify

Tambahkan atau perbarui:

```env
APP_URL=https://sullamulhifz.or.id
DOMAIN_SEPARATION_ENABLED=true
PUBLIC_SITE_URL=https://sullamulhifz.or.id
PUBLIC_HOSTS=sullamulhifz.or.id,www.sullamulhifz.or.id
PORTAL_BASE_URL=https://app.sullamulhifz.or.id
PORTAL_URL=https://app.sullamulhifz.or.id/login
PORTAL_HOST=app.sullamulhifz.or.id
LEGACY_HOSTS=taysriulqurani.id,www.taysriulqurani.id
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
```

Jangan mengubah `APP_KEY`, `DB_URL`, password database, atau data key.

## Domains di Coolify

Gunakan satu resource aplikasi yang sama dan isi kolom Domains:

```text
https://taysriulqurani.id,https://sullamulhifz.or.id,https://www.sullamulhifz.or.id,https://app.sullamulhifz.or.id
```

Jangan tambahkan `academy`, `api`, atau `staging` ke resource ini pada rilis v1.4.5.

## Langkah upgrade

1. Salin isi patch ke root repository.
2. Commit dan push ke branch `main`.
3. Pastikan record DNS `app` mengarah ke VPS dan berstatus Proxied.
4. Tambahkan `https://app.sullamulhifz.or.id` ke Domains Coolify.
5. Tambahkan environment variables di atas.
6. Klik Save lalu Redeploy.
7. Tidak perlu menjalankan migration atau seeder.

## Perilaku setelah upgrade

| Permintaan | Hasil |
|---|---|
| `sullamulhifz.or.id/` | Website publik |
| `sullamulhifz.or.id/login` | Redirect ke `app.../login` |
| `sullamulhifz.or.id/dashboard` | Redirect ke `app.../dashboard` |
| `app.sullamulhifz.or.id/` | Login untuk tamu, dashboard untuk pengguna login |
| `app.sullamulhifz.or.id/tentang` | Redirect ke website publik |
| `www.sullamulhifz.or.id/...` | Redirect ke domain tanpa `www` |
| `taysriulqurani.id` | Tetap tersedia selama transisi |

## Verifikasi

Buka mode incognito:

```text
https://sullamulhifz.or.id
https://sullamulhifz.or.id/login
https://app.sullamulhifz.or.id
https://app.sullamulhifz.or.id/login
https://www.sullamulhifz.or.id
https://taysriulqurani.id
```

Setelah login, dashboard harus berada pada `app.sullamulhifz.or.id/dashboard`.

Opsional dari Terminal aplikasi:

```sh
cd /var/www/html
sh scripts/smoke-test-v1.4.5.sh
```

## Rollback

1. Set `DOMAIN_SEPARATION_ENABLED=false`.
2. Save dan Restart atau Redeploy.
3. Bila perlu, kembalikan commit v1.4.5 dan redeploy commit sebelumnya.

Database tidak perlu dipulihkan.
