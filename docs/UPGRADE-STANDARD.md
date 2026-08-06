# Standar Upgrade Produksi

Panduan versi spesifik tetap menjadi sumber utama. Dokumen ini menjelaskan pola umum.

## A. Sebelum upgrade

1. Baca `UPGRADE-VERSION.md` sampai selesai.
2. Pastikan versi saat ini melalui `/release.txt`.
3. Buat backup database produksi.
4. Pastikan persistent storage aplikasi aktif.
5. Catat nama Environment Variables yang ada; jangan menyalin nilai secret ke issue/chat.
6. Pastikan tidak ada proses input data penting selama deployment.
7. Pastikan rollback commit/tag lama tersedia.

## B. Unggah kode

1. Upload isi paket rilis ke root repository.
2. Jangan upload folder pembungkus.
3. Jangan upload `.env`, key data, daftar akun rahasia, dump database, atau file backup.
4. Commit dengan format rilis.
5. Pastikan GitHub Actions lulus.

## C. Deployment Coolify

1. Tambahkan Environment Variables baru sebelum redeploy jika panduan versi memintanya.
2. Klik Redeploy.
3. Tunggu aplikasi healthy.
4. Jalankan command versi spesifik dari Terminal aplikasi.
5. Untuk upgrade normal, gunakan:

```sh
cd /var/www/html
sh scripts/deploy.sh
```

Panduan versi boleh mengganti command ini jika ada alasan jelas.

## D. Larangan

Pada database produksi berisi data, jangan jalankan:

```sh
php artisan db:wipe --force
php artisan migrate:fresh --force
CONFIRM_DATABASE_WIPE=YES sh scripts/first-install.sh
```

## E. Verifikasi

Jalankan sesuai kebutuhan:

```sh
php artisan migrate:status
php artisan sullam:verify-installation
curl -i http://127.0.0.1:8000/up
```

Kemudian uji route dan peran pada `TEST-CHECKLIST.md`.

## F. Rollback aplikasi

1. Revert ke commit/tag terakhir yang stabil.
2. Redeploy.
3. Jangan rollback database secara manual kecuali panduan versi menyertakan prosedur dan dampaknya.
4. Jika migration baru tidak kompatibel dengan versi lama, gunakan rencana rollback dari release note.
5. Catat insiden dan penyebab di `ISSUE-REGISTER.md` atau issue GitHub.

## G. Setelah stabil

- tambahkan hasil deployment ke `docs/releases/VERSION.md`;
- tandai issue selesai;
- perbarui `CURRENT-STATE.md` jika keadaan produksi berubah;
- jangan menghapus panduan versi lama.
