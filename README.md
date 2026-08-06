# Sullamul Ḥifẓ MVP

**Bukan Sekadar Hafal, Tapi KUAT.**

Aplikasi web responsif/PWA untuk pencatatan penting pembelajaran Al-Qur’an, tugas rumah, buku penghubung, pengumuman, dan Pembinaan Jumat. Implementasi pertama disiapkan untuk **TPA Al-Insyirah**.

> Pembinaan berlangsung di dunia nyata. Aplikasi menjaga jejak, komunikasi, dan kesinambungannya.

## Fitur yang tersedia

### Admin Lembaga
- Dashboard ringkas.
- Data santri dan hubungan wali.
- Data dan akun guru.
- Tahun ajaran, jenjang, kelas utama, dan kelompok belajar.
- Penugasan guru dan jadwal.
- Pengumuman lembaga/kelas.
- Dokumentasi Pembinaan Jumat.
- Laporan CSV dasar.

### Kepala TPA
- Dashboard pengawasan.
- Pengumuman dan Pembinaan Jumat.
- Laporan tanpa hak otomatis mengubah master santri, guru, atau struktur akademik.

### Guru
- Daftar kelas dan kelompok yang benar-benar diampu.
- Membuka dan menyelesaikan pertemuan.
- Absensi cepat.
- Catatan tahsīn selektif.
- Setoran hafalan Juz 30.
- Murāja‘ah terpisah dari hafalan baru.
- Tugas kelas/kelompok dan pemeriksaan bukti.
- Buku penghubung privat.

### Orang Tua/Wali
- Satu akun untuk beberapa anak.
- Melihat tahsīn, setoran, dan murāja‘ah anak sendiri.
- Melihat tugas dan mengirim bukti privat.
- Melihat tanggapan guru.
- Buku penghubung.
- Pengumuman dan Pembinaan Jumat.

### Keamanan dasar
- Akun awal wajib mengganti kata sandi pada login pertama.
- Hak akses berbasis peran.
- Guru dibatasi oleh penugasan aktif.
- Wali hanya melihat anak yang terhubung.
- Bukti tugas disimpan di storage privat dan diakses melalui pemeriksaan izin.
- Tidak ada ranking santri.

## Teknologi

- PHP 8.4.1+
- Laravel 13
- Blade + CSS/JavaScript mandiri, tanpa proses build Node wajib
- MySQL 8 / MariaDB kompatibel
- Docker + NGINX Unit
- PWA dasar

## Struktur penting

```text
app/                    Controller, model, middleware
config/                 Konfigurasi aplikasi
database/migrations/    Struktur database
database/seeders/       Data awal idempotent
public/                  CSS, JS, manifest, service worker
resources/views/        Tampilan Blade
routes/web.php           Route aplikasi
scripts/deploy.sh        Migration, seeding, dan optimasi
Dockerfile               Build untuk Coolify
unit.json                Web server NGINX Unit
README-COOLIFY.md        Panduan deployment rinci
```

## Akun admin pertama

Akun admin dibuat dari environment variable:

```env
INITIAL_ADMIN_NAME="Administrator TPA Al-Insyirah"
INITIAL_ADMIN_EMAIL=admin@sullamulhifz.id
INITIAL_ADMIN_PHONE=6281200000000
INITIAL_ADMIN_PASSWORD="Ganti-Segera-2026!"
```

**Wajib ubah email dan kata sandi awal sebelum deployment.** Pada login pertama, aplikasi memaksa pengguna mengganti kata sandi awal. Seeder bersifat idempotent. Password environment hanya dipakai ketika akun admin pertama kali dibuat; perubahan password melalui aplikasi tidak ditimpa saat redeploy.

## Data awal

Seeder produksi membuat:
- TPA Al-Insyirah;
- tahun ajaran 2026/2027;
- Tamhidi, Mustawa Awal, Mustawa Tsani;
- enam kelas awal;
- Tahfizh Sesi A dan B;
- program tahsīn, tahfizh, murāja‘ah, Pembinaan Jumat, dan program bakat;
- enam marhalah resmi;
- daftar surah Juz 30;
- role dan permission;
- akun admin awal.

Data guru, wali, dan santri demo hanya dibuat saat:

```env
SEED_DEMO_DATA=true
```

Untuk produksi gunakan:

```env
SEED_DEMO_DATA=false
```

## Menjalankan secara lokal dengan Docker

1. Buat APP_KEY:

```bash
export APP_KEY="base64:$(openssl rand -base64 32)"
```

2. Build dan jalankan:

```bash
docker compose -f docker-compose.local.yml up -d --build
```

3. Jalankan deployment task:

```bash
docker compose -f docker-compose.local.yml exec app sh scripts/deploy.sh
```

4. Buka:

```text
http://localhost:8000
```

Akun demo lokal:

```text
Admin:  admin@sullamulhifz.local / Local-Sullam-2026!
Guru:   guru.demo@sullamulhifz.id / SullamDemo2026!
Wali:   wali.demo@sullamulhifz.id / SullamDemo2026!
```

## Mengunggah ke GitHub

### Melalui website GitHub

1. Buat repository baru, misalnya `sullamul-hifz`.
2. Ekstrak file ZIP proyek.
3. Upload seluruh isi folder proyek, bukan folder pembungkusnya.
4. Pastikan file berikut terlihat di root repository:
   - `composer.json`
   - `Dockerfile`
   - `unit.json`
   - `artisan`
   - `README-COOLIFY.md`
5. Commit ke branch `main`.

### Melalui Git

```bash
git init
git add .
git commit -m "Initial Sullamul Hifz MVP"
git branch -M main
git remote add origin https://github.com/USERNAME/sullamul-hifz.git
git push -u origin main
```

Jangan upload file `.env`.

## Deployment Coolify

Ikuti [README-COOLIFY.md](README-COOLIFY.md).

Ringkasannya:

1. Buat database MySQL di Coolify.
2. Buat Application dari repository GitHub.
3. Pilih **Dockerfile** sebagai build pack.
4. Exposed port: `8000`.
5. Masukkan environment variables.
6. Tambahkan persistent storage untuk `/var/www/html/storage/app/private`.
7. Set post-deployment command:

```bash
sh scripts/deploy.sh
```

8. Hubungkan domain dan aktifkan HTTPS.
9. Deploy.

## Pengujian

Repository menyertakan GitHub Actions untuk:

```bash
php artisan test
```

Secara manual:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan test
```

## Batas MVP saat ini

Belum termasuk:
- aplikasi Android/iOS native;
- integrasi WhatsApp otomatis;
- penilaian bacaan dengan AI;
- Parent Academy/LMS lengkap;
- sistem pembayaran;
- milestone penjagaan tingkat lanjut;
- rekomendasi otomatis berbasis STIFIn;
- community seperti media sosial;
- seluruh 114 surah di master data (seed awal fokus Juz 30);
- import massal santri dari Excel.

Database dan struktur kode disiapkan agar fitur tersebut dapat ditambahkan tanpa mengubah prinsip dasar.

## Catatan produksi penting

- Gunakan kata sandi database dan admin yang kuat.
- Jangan aktifkan `APP_DEBUG=true` di produksi.
- Pasang persistent storage sebelum menerima unggahan.
- Aktifkan backup database harian di Coolify/VPS.
- Backup folder private media secara berkala.
- Pastikan persetujuan wali tersedia sebelum media anak digunakan di luar bukti tugas privat.
- Setelah logo final tersedia, ganti `public/icon.svg` dan identitas visual tanpa mengubah fungsi aplikasi.
