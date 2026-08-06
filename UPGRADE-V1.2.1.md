# Upgrade ke Sullamul Ḥifẓ v1.2.1

Rilis ini hanya menambahkan dokumentasi, governance, template GitHub, dan pemeriksaan kelengkapan release notes.

## Versi asal

- v1.2.0 Official Branding

## Dampak database

Tidak ada.

## Environment Variables

Tidak ada perubahan.

## Langkah upgrade

1. Upload isi paket v1.2.1 ke root repository.
2. Commit dengan pesan:

```text
Release Sullamul Hifz v1.2.1 — documentation governance
```

3. Pastikan GitHub Actions lulus.
4. Jika Coolify melakukan auto-deploy, biarkan redeploy selesai.
5. Tidak perlu menjalankan migration, seeder, atau first-install.
6. Periksa `/release.txt` bila source versi ikut dideploy.

## Larangan

Jangan menjalankan:

```sh
php artisan db:wipe --force
php artisan migrate:fresh --force
CONFIRM_DATABASE_WIPE=YES sh scripts/first-install.sh
```

## Verifikasi

- `START-HERE.md` tampil di root GitHub;
- folder `docs/` lengkap;
- GitHub Action `Release Documentation Check` lulus;
- aplikasi lama tetap dapat login dan membuka dashboard.

## Rollback

Revert commit v1.2.1. Tidak ada rollback database.
