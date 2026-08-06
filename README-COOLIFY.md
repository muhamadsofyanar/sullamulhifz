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
scripts/deploy.sh
```

Branch deployment yang disarankan: `main`.

## 2. Buat database MySQL

Di project Coolify yang sama:

1. **New Resource**.
2. Pilih **Database → MySQL**.
3. Gunakan MySQL 8.4 atau versi LTS yang tersedia.
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
APP_URL=https://DOMAIN-ANDA
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
SEED_DEMO_DATA=false

UPLOAD_MAX_KB=25600
MEDIA_RETENTION_DAYS=180
```

### Catatan APP_URL

Gunakan URL final dengan HTTPS, misalnya:

```env
APP_URL=https://app.sullamulhifz.id
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

Isi bagian **Post-deployment Command**:

```bash
sh scripts/deploy.sh
```

Script menjalankan:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --class="Database\Seeders\ProductionSeeder" --force
php artisan storage:link
php artisan optimize
```

Seeder produksi idempotent dan tidak membuat santri demo ketika `SEED_DEMO_DATA=false`.

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
4. Post-deployment command berhasil.
5. Health check `/up` berstatus sehat.

## 10. Login pertama

Buka domain aplikasi dan masuk memakai:

```text
Email:    nilai INITIAL_ADMIN_EMAIL
Password: nilai INITIAL_ADMIN_PASSWORD
```

Setelah masuk, aplikasi otomatis mengarahkan admin ke halaman **Profil** untuk mengganti kata sandi awal. Setelah kata sandi berhasil diganti:

1. Tambahkan guru.
2. Atur penugasan guru.
3. Masukkan santri dan wali.
4. Masukkan santri ke Tahfizh Sesi A/B jika diperlukan.

## 11. Urutan input awal

Urutan aman:

```text
Guru
→ Penugasan Guru
→ Jadwal
→ Santri + Wali + Kelas Utama
→ Anggota Kelompok Tahfizh
→ Uji Login Guru
→ Uji Login Wali
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
3. `scripts/deploy.sh` menjalankan migration baru secara otomatis.
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
APP_URL=https://DOMAIN-ANDA
SESSION_SECURE_COOKIE=true
```

Aplikasi sudah dikonfigurasi untuk mempercayai header proxy Coolify dan memaksa skema HTTPS pada environment produksi.
