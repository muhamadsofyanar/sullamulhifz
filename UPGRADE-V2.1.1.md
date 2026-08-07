# Upgrade Sullamul Hifz v2.1.1

## Hotfix deployment Coolify

v2.1.1 memperbaiki kegagalan build v2.1.0 pada tahap Composer `package:discover`.

### Root cause
`bootstrap/app.php` menggunakan helper `config()` saat callback `withMiddleware()` sedang diregistrasikan. Pada proses Composer, callback tersebut dapat dieksekusi sebelum Laravel selesai mendaftarkan config repository sehingga muncul:

`Target class [config] does not exist.`

### Fix
- `TRUSTED_PROXIES` dibaca langsung dari process environment pada bootstrap awal.
- Tidak ada lagi pemanggilan `config()` di callback middleware awal.
- Docker build sekarang melakukan smoke-check `artisan package:discover` dan `route:list` sehingga regression bootstrap gagal sebelum container ditukar.

### Coolify
Sebelum redeploy, jadikan variabel berikut **Runtime only / jangan Build Variable** bila opsi tersedia:
- APP_ENV
- APP_KEY
- DB_PASSWORD
- INITIAL_ADMIN_PASSWORD
- INITIAL_TEACHER_PASSWORD
- INITIAL_GUARDIAN_PASSWORD
- INITIAL_TPA_DATA_KEY

Peringatan `SecretsUsedInArgOrEnv` dari BuildKit bukan penyebab kegagalan v2.1.0, tetapi runtime-only mengurangi paparan secret di build metadata.

`composer.lock` belum ada pada repository. Deployment tetap dapat berjalan, namun commit lock file direkomendasikan agar dependency build reproducible.
