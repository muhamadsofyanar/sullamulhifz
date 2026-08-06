# Changelog

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

## 0.1.1 - 2026-08-06

- Align runtime and Composer dependency resolution to PHP 8.4.1+.
- Upgrade NGINX Unit runtime image from `1.34.1-php8.3` to `1.34.2-php8.4`.
- Pin Composer platform PHP to 8.4.1 to prevent build/runtime dependency mismatch.
