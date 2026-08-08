# Panduan Deployment Sullamul Ḥifẓ ke Coolify

Panduan ini menggunakan alur:

```text
Source Code → GitHub → Coolify → Dockerfile → MySQL → Domain/HTTPS
```

## 1. Persiapan repository

Pastikan root repository berisi:

```text
Dockerfile
unit.json
composer.json
artisan
scripts/container-start.sh
```

Branch deployment yang disarankan: `main`.

## 2. Buat database MySQL

Di project Coolify yang sama:

1. **New Resource**.
2. Pilih **Database → MySQL**.
3. Gunakan image `mysql:8.0-bookworm` agar kompatibel dengan CPU VPS yang digunakan.
4. Tentukan:
   - database name;
   - username;
   - password kuat.
5. Deploy database.
6. Salin internal host, port, database, username, dan password.

Contoh:

```env
DB_CONNECTION=mysql
DB_HOST=mysql-xxxxxxxx
DB_PORT=3306
DB_DATABASE=sullamul_hifz
DB_USERNAME=sullamul_hifz
DB_PASSWORD=PASSWORD_KUAT
```

Gunakan **internal host**, bukan domain publik database.

## 3. Buat Application dari GitHub

1. **New Resource → Application**.
2. Pilih repository GitHub.
3. Branch: `main`.
4. Build Pack: **Dockerfile**.
5. Dockerfile location: `/Dockerfile`.
6. Ports Exposes: `8000`.
7. Health check path: `/up`.

## 4. Buat APP_KEY

Jalankan di komputer atau terminal VPS:

```bash
echo "base64:$(openssl rand -base64 32)"
```

Salin seluruh hasil, termasuk awalan `base64:`.

## 5. Environment variables

Masukkan melalui Coolify Developer View:

```env
APP_NAME="Sullamul Hifz"
APP_ENV=production
APP_KEY=base64:HASIL_KEY_ANDA
APP_DEBUG=false
APP_URL=https://taysriulqurani.id
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=HOST_INTERNAL_MYSQL
DB_PORT=3306
DB_DATABASE=sullamul_hifz
DB_USERNAME=sullamul_hifz
DB_PASSWORD=PASSWORD_DATABASE

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@DOMAIN-ANDA
MAIL_FROM_NAME="Sullamul Hifz"

INITIAL_INSTITUTION_NAME="TPA Al-Insyirah"
INITIAL_INSTITUTION_CODE=ALINSYIRAH
INITIAL_ADMIN_NAME="Administrator TPA Al-Insyirah"
INITIAL_ADMIN_EMAIL=EMAIL_ADMIN_ANDA
INITIAL_ADMIN_PHONE=NOMOR_ADMIN_ANDA
INITIAL_ADMIN_PASSWORD="PASSWORD_ADMIN_SANGAT_KUAT"
INITIAL_TEACHER_PASSWORD="PASSWORD_AWAL_GURU"
INITIAL_GUARDIAN_PASSWORD="PASSWORD_AWAL_WALI"
SEED_INITIAL_TPA_DATA=true
SEED_DEMO_DATA=false

UPLOAD_MAX_KB=25600
MEDIA_RETENTION_DAYS=180
```

### Catatan APP_URL

Gunakan URL final dengan HTTPS, misalnya:

```env
APP_URL=https://taysriulqurani.id
```

## 6. Persistent storage

Tanpa persistent storage, file bukti tugas dapat hilang saat container diganti pada redeploy.

Tambahkan volume di Coolify:

```text
Source/Volume name: sullamul-hifz-private-media
Destination path:  /var/www/html/storage/app/private
```

Disarankan juga volume log bila diperlukan:

```text
Source/Volume name: sullamul-hifz-logs
Destination path:  /var/www/html/storage/logs
```

Database berada pada resource MySQL terpisah dan harus memiliki volume persisten sendiri.

## 7. Post-deployment command

Biarkan bagian **Post-deployment Command kosong**. Mulai v2.6.4, migration,
seeder idempoten, cache, dan startup aplikasi dikelola oleh
`scripts/container-start.sh` ketika `AUTO_MIGRATE=true`.

Jangan menjalankan `scripts/deploy.sh` lagi sebagai post-deployment command,
karena akan menduplikasi pekerjaan startup container.

## 8. Domain dan HTTPS

1. Tambahkan domain aplikasi di Coolify.
2. Arahkan DNS domain ke IP VPS.
3. Tunggu propagasi DNS.
4. Aktifkan sertifikat HTTPS melalui proxy Coolify.
5. Pastikan `APP_URL` memakai domain HTTPS yang sama.

## 9. Deploy

Tekan **Deploy** dan periksa tahap berikut:

1. Composer dependency installation berhasil.
2. Docker image selesai dibuat.
3. Container membuka port `8000`.
4. Startup container menyelesaikan migration dan seeder tanpa error fatal.
5. Health check `/up` berstatus sehat.

## 10. Instalasi database baru

Setelah redeploy berhasil, buka **Terminal aplikasi**, lalu jalankan satu perintah:

```bash
cd /var/www/html
CONFIRM_DATABASE_WIPE=YES sh scripts/first-install.sh
```

Script akan menghapus tabel lama, menjalankan keempat migration, membuat data awal lengkap, dan memverifikasi jumlah data.

Target verifikasi:

```text
Santri aktif       88
Wali aktif         88
Guru aktif          4
Kelas Tahfizh A    30
Kelas Tahfizh B    27
```

## 11. Login pertama

Admin masuk memakai nilai `INITIAL_ADMIN_EMAIL` dan `INITIAL_ADMIN_PASSWORD`.

Guru:

```text
nurul@taysriulqurani.id
jundi@taysriulqurani.id
yanti@taysriulqurani.id
sofyan@taysriulqurani.id
```

Wali:

```text
wali001@taysriulqurani.id sampai wali088@taysriulqurani.id
```

Semua akun awal wajib mengganti password saat login pertama. Daftar akun wali dibuat berurutan `wali001` sampai `wali088`; pemetaan lengkap mengikuti data kelas yang diberikan.

Jika password admin lupa, ubah `INITIAL_ADMIN_PASSWORD` di Coolify lalu jalankan:

```bash
php artisan sullam:reset-admin
```

## 12. Pemeriksaan setelah deployment

- `/up` mengembalikan status sehat.
- Admin dapat login.
- Guru hanya melihat kelas yang diampu.
- Wali hanya melihat anak yang terhubung.
- Absensi dapat disimpan.
- Setoran dan murāja‘ah dapat disimpan terpisah.
- Tugas dapat diterbitkan.
- Wali dapat mengunggah bukti.
- Guru dapat membuka bukti dan memberi tanggapan.
- Pengumuman dan Pembinaan Jumat tampil kepada penerima.

## 13. Backup

Minimal:

- backup database MySQL setiap hari;
- backup persistent volume media secara berkala;
- simpan salinan di lokasi berbeda dari VPS utama;
- uji pemulihan, bukan hanya pembuatan backup.

## 14. Update aplikasi

Setelah memperbarui file di GitHub:

1. Commit/push ke branch `main`.
2. Coolify dapat melakukan auto-deploy melalui webhook, atau tekan Redeploy.
3. `scripts/container-start.sh` menjalankan migration baru secara otomatis ketika `AUTO_MIGRATE=true`.
4. Periksa log dan fungsi utama.

## 15. Troubleshooting ringkas

### Halaman 500 setelah deployment

Periksa:

```text
APP_KEY
DB_HOST
DB_DATABASE
DB_USERNAME
DB_PASSWORD
permission storage
post-deployment logs
```

### Database connection refused

Gunakan internal hostname resource MySQL Coolify. Jangan gunakan `127.0.0.1`, karena aplikasi dan database berada di container berbeda.

### File bukti hilang setelah redeploy

Persistent storage belum dipasang atau destination path salah. Gunakan:

```text
/var/www/html/storage/app/private
```

### Mixed content atau redirect HTTP

Pastikan:

```env
APP_URL=https://taysriulqurani.id
SESSION_SECURE_COOKIE=true
```

Aplikasi sudah dikonfigurasi untuk mempercayai header proxy Coolify dan memaksa skema HTTPS pada environment produksi.
