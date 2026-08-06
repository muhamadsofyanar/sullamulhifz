# Upgrade ke Sullamul Ḥifẓ v1.2.0

## Untuk instalasi yang sudah berhasil dan sudah berisi 88 santri

1. Ganti isi repository GitHub dengan isi ZIP v1.2.0.
2. Commit ke branch yang dipakai Coolify.
3. Klik **Redeploy** pada aplikasi `sullamul-hifz`.
4. Jangan menjalankan `db:wipe` atau `scripts/first-install.sh` lagi.
5. Buka `/release.txt` dan pastikan tertulis `Sullamul Hifz v1.2.0`.
6. Tekan `Ctrl + F5` agar cache CSS dan service worker diperbarui.

Environment Variables lama tetap dipakai. `INITIAL_TPA_DATA_KEY`, password admin, password guru, dan password wali tidak perlu dimasukkan ulang selama resource aplikasi Coolify tidak dibuat ulang.

## Untuk instalasi baru/kosong

Setelah redeploy, jalankan:

```sh
cd /var/www/html
CONFIRM_DATABASE_WIPE=YES sh scripts/first-install.sh
```

Perintah ini hanya untuk database baru atau database yang memang boleh dihapus seluruhnya.

## Verifikasi

```sh
php artisan migrate:status
php artisan sullam:verify-installation
```

Lalu buka:

- `/dashboard`
- `/admin/content`
- `/buku-penghubung`
- `/pengumuman`
- `/pembinaan-jumat`
