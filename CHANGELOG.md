# Changelog

## v1.3.0 — Public Website & Domain Preparation

- menambahkan website publik pada route `/`;
- menambahkan halaman Tentang, Program, TPA, Academy, Artikel, Kontak, dan Privasi;
- menambahkan metadata SEO, Open Graph, robots, dan sitemap;
- memisahkan perilaku root pada host portal `app.sullamulhifz.or.id`;
- menambahkan paket DNS Cloudflare dan panduan domain/subdomain;
- memperbarui service worker agar navigasi publik tidak memakai cache halaman login lama;
- tidak mengubah database atau data awal.

## v1.2.1 — Documentation Governance

- Menambahkan `START-HERE.md` sebagai pintu masuk ketika riwayat chat hilang.
- Menambahkan current state, roadmap, arsitektur, decision log, issue register, data governance, dan handover.
- Menetapkan standar release, upgrade, testing, rollback, dan operasi Coolify.
- Menambahkan template issue/PR dan GitHub Action untuk memastikan setiap rilis memiliki panduan upgrade.
- Tidak mengubah fitur, database, atau data produksi.

## v1.1.1 — Blade hotfix
- Memperbaiki parse error `unexpected token endif` pada halaman Pembinaan Jumat.
- Memisahkan directive Blade bersarang agar kompilasi view stabil di Laravel/PHP 8.4.
- Mempertahankan seluruh data awal v1.1.0.

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

## v1.2.0 — Official Branding

- Mengintegrasikan logo resmi Sullamul Ḥifẓ pada login, sidebar, favicon, dan PWA.
- Menambahkan icon system konsisten pada seluruh navigasi utama.
- Memperbarui palet antarmuka ke Deep Emerald, Warm Gold, dan Ivory.
- Memperbarui dashboard admin dengan kartu statistik dan akses cepat berikon.
- Menyertakan `docs/BRAND-GUIDE.md` sebagai pedoman pengembangan berikutnya.
- Mempertahankan hotfix MySQL, hotfix Blade Pembinaan Jumat, serta data awal terenkripsi.
