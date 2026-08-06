# Changelog

## v1.1.0 — Complete encrypted initial data
- Menambahkan 88 santri dan 88 akun wali.
- Menambahkan guru Nurul, Jundi, Yanti, dan Sofyan.
- Menambahkan Kelas Tahfizh A (30 santri) dan B (27 santri).
- Menambahkan penugasan guru sesuai kelas/program.
- Menambahkan data awal terenkripsi AES-256-GCM.
- Menambahkan perintah `sullam:reset-admin` dan `sullam:verify-installation`.
- Mempertahankan perbaikan nama indeks MySQL dan PHP 8.4.


## 1.0.0 — 2026-08-06

Stable deployment release for Coolify + MySQL.

- Uses PHP 8.4 runtime and Composer platform PHP 8.4.1.
- Uses `PDO::MYSQL_ATTR_SSL_CA` in the MySQL configuration.
- Shortens the `assignment_submissions` composite unique-index name to `asgn_submission_recipient_attempt_uq`, compatible with MySQL's identifier limit.
- Adds `scripts/first-install.sh` for a clean one-time database installation.
- Adds `scripts/deploy.sh` for subsequent deployments.
- Adds `RELEASE` and `public/release.txt` so the deployed version can be verified.

## 0.1.0 — 2026-08-06

- Fondasi Laravel 13.
- Autentikasi dan role admin/kepala TPA/guru/wali.
- Kewajiban mengganti kata sandi awal, pembatasan percobaan login, dan reset sandi oleh admin.
- Data santri, wali, guru, kelas, dan kelompok belajar.
- Penugasan guru dan jadwal.
- Pertemuan, absensi, tahsīn, setoran, dan murāja‘ah.
- Tugas serta bukti privat dengan validasi jenis file dan kontrol kirim ulang.
- Buku penghubung.
- Pengumuman dan Pembinaan Jumat.
- Laporan CSV dasar dan riwayat kehadiran wali.
- PWA dasar.
- Dockerfile, NGINX Unit, seeder produksi, dan panduan Coolify.

## 0.1.3 - 2026-08-06
- Shortened the `assignment_submissions` composite unique-index name for MySQL's 64-character identifier limit.
