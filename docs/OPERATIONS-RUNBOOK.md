# Operations Runbook — Coolify Production

## Komponen

- Application: `sullamul-hifz`
- Database: MySQL internal Coolify
- Runtime: PHP 8.4 + NGINX Unit
- Port aplikasi: 8000
- Health endpoint: `/up`
- Logging: stderr pada menu Logs aplikasi Coolify

Nama resource dapat berubah; verifikasi di Coolify sebelum melakukan tindakan.

## Pemeriksaan kesehatan

Dari Terminal aplikasi:

```sh
cd /var/www/html
curl -i http://127.0.0.1:8000/up
php artisan about
php artisan migrate:status
```

Target healthcheck adalah HTTP 200.

## Melihat error

1. Buka Logs aplikasi Coolify.
2. Refresh halaman yang error.
3. Cari `production.ERROR`, `SQLSTATE`, `ParseError`, atau `Exception`.
4. Salin hanya bagian error utama; jangan menyalin secret.

Laravel logging default pada produksi adalah `stderr`, sehingga `storage/logs/laravel.log` mungkin tidak ada.

## Membersihkan cache aman

```sh
cd /var/www/html
php artisan optimize:clear
php artisan optimize
```

Jangan menggabungkan dua command tanpa newline karena dapat terbaca sebagai command yang salah.

## Deployment normal

```sh
cd /var/www/html
sh scripts/deploy.sh
```

Gunakan hanya setelah membaca panduan versi.

## Instalasi pertama

Hanya untuk database baru/kosong:

```sh
cd /var/www/html
CONFIRM_DATABASE_WIPE=YES sh scripts/first-install.sh
```

Perintah ini menghapus tabel dan tidak boleh digunakan sebagai cara memperbaiki bug produksi.

## Reset admin

Gunakan command aplikasi yang tersedia dan dokumentasikan tindakan. Jangan mengubah hash password langsung di database kecuali keadaan darurat dan ada prosedur audit.

## Backup

Sebelum upgrade yang menyentuh database:

- buat backup melalui fasilitas database/Coolify;
- beri nama yang memuat tanggal dan versi;
- pastikan file/backup tercatat berhasil;
- untuk rilis berisiko, lakukan uji restore pada lingkungan terpisah.

## Insiden

Untuk setiap insiden produksi, catat:

- tanggal dan waktu;
- versi;
- route/fitur terdampak;
- pesan error utama;
- tindakan sementara;
- akar penyebab;
- perbaikan permanen;
- kebutuhan test regresi.
