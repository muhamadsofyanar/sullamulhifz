# Upgrade v2.1.0

Upgrade ini dirancang dari source v2.0.4 menuju **v2.1.0 Unified Platform & Secure Media**. Migration bersifat additive dan tidak menjalankan `db:wipe` atau `migrate:fresh`.

## Sebelum deploy

1. Backup database MySQL.
2. Backup persistent volume `storage/app` dan `storage/logs`.
3. Simpan environment variables Coolify.
4. Pastikan branch GitHub yang dipakai Coolify sudah benar.
5. Pertahankan `APP_KEY` lama dan persistent volume lama.

## Environment minimum

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sullamulhifz.or.id
PUBLIC_SITE_URL=https://sullamulhifz.or.id
PUBLIC_HOSTS=sullamulhifz.or.id,www.sullamulhifz.or.id
PORTAL_BASE_URL=https://app.sullamulhifz.or.id
PORTAL_URL=https://app.sullamulhifz.or.id/login
PORTAL_HOST=app.sullamulhifz.or.id
ACADEMY_HOST=academy.sullamulhifz.or.id
API_HOST=api.sullamulhifz.or.id
STAGING_HOST=staging.sullamulhifz.or.id
DOMAIN_SEPARATION_ENABLED=true
AUTO_MIGRATE=true
BOOTSTRAP_PRODUCTION=false
TRUSTED_PROXIES=127.0.0.1,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,100.64.0.0/10
```

Untuk database kosong pertama kali, set sementara:

```env
BOOTSTRAP_PRODUCTION=true
INITIAL_ADMIN_EMAIL=admin@sullamulhifz.or.id
INITIAL_ADMIN_PASSWORD=kata-sandi-kuat-minimal-12-karakter
```

Setelah deployment pertama berhasil, kembalikan `BOOTSTRAP_PRODUCTION=false`.

## Proses deployment

Upload source ke GitHub dan lakukan satu Redeploy dari Coolify. Startup akan:

1. menunggu database;
2. menjalankan migration additive;
3. menjalankan seeder idempoten;
4. mengamankan media legacy;
5. menjalankan verifier sebagai warning/readiness—hasil verifier yang belum lengkap tidak mematikan web;
6. membangun cache;
7. menyalakan NGINX Unit;
8. menyinkronkan Audio Qur’an di latar belakang.

## Perubahan data

Tabel baru:

- `branches`;
- `academic_periods`;
- `media_assets` dan `media_links`;
- `announcement_targets`;
- `friday_session_targets`;
- `student_marhalah_histories`;
- `account_invitations`;
- `feature_flags`.

Kolom baru ditambahkan secara nullable untuk branch, media, dan hubungan riwayat. Kolom legacy tetap dipertahankan agar rollback source tidak langsung memutus data lama.

## Setelah deploy

- buka `/up`, `/api/health`, dan `/release.txt`;
- login sebagai admin;
- buka **Fondasi Platform** dan periksa feature flag;
- uji satu file pada tugas, Buku Penghubung, pengumuman, dan Pembinaan Jumat;
- logout dan uji kembali URL file privat;
- hapus service worker/cache browser lama apabila PWA masih menampilkan v2.0.4;
- aktifkan Laravel scheduler melalui Scheduled Task Coolify: `php artisan schedule:run` setiap menit;
- isi SMTP agar reset kata sandi dan undangan email dapat digunakan.

## Rollback

Rollback source/image ke v2.0.4 dapat dilakukan tanpa menjalankan migration down. Jangan menghapus tabel baru dan jangan menjalankan `php artisan migrate:rollback` secara otomatis di produksi. Source lama tetap menggunakan kolom legacy.
