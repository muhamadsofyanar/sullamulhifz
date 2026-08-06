# Current State — Baseline v1.2.1

Dokumen ini merekam keadaan proyek pada 6 Agustus 2026 agar pengembangan dapat dilanjutkan tanpa bergantung pada riwayat chat.

## 1. Identitas produk

- **Brand induk:** Sullamul Ḥifẓ
- **Tagline:** Bukan Sekadar Hafal, Tapi KUAT
- **Implementasi pertama:** TPA Al-Insyirah
- **Domain sementara produksi:** `https://taysriulqurani.id`
- **Repository:** `muhamadsofyanar/sullamulhifz`, branch produksi `main`

## 2. Teknologi produksi

- Laravel 13
- PHP 8.4
- Blade, CSS, dan JavaScript tanpa build Node wajib
- MySQL 8 (`mysql:8.0-bookworm`)
- Dockerfile + NGINX Unit
- Coolify sebagai deployment platform
- Logging Laravel: `stderr`, dibaca dari Logs aplikasi Coolify
- Exposed port aplikasi: 8000

## 3. Data awal yang sudah tersedia

### Struktur akademik

- Tahun ajaran: 2026/2027
- 6 kelas utama:
  - Tamhidi A — 18 santri
  - Tamhidi B — 13 santri
  - Mustawa Awal A — 16 santri
  - Mustawa Awal B — 14 santri
  - Mustawa Tsani A — 14 santri
  - Mustawa Tsani B — 13 santri
- Total: **88 santri**

### Kelompok Tahfizh

- Tahfizh A — gabungan Mustawa Awal A + Mustawa Tsani A = **30 santri**
- Tahfizh B — gabungan Mustawa Awal B + Mustawa Tsani B = **27 santri**
- Santri Tamhidi belum masuk kelompok Tahfizh pada data awal.

### Guru dan penugasan awal

- Nurul — Tamhidi A dan Tamhidi B, program TPA
- Jundi — Mustawa Awal A dan B, program Tahsin
- Yanti — Mustawa Tsani A dan B, program Tahsin
- Sofyan — Tahfizh A dan Tahfizh B, program Tahfizh

### Wali

- 88 akun wali awal, satu akun per santri.
- Akun awal masih menggunakan alamat dan nomor placeholder berurutan.
- Penggabungan saudara kandung ke satu akun wali belum dikerjakan.

## 4. Fitur yang sudah tersedia

- autentikasi dan kewajiban mengganti password awal;
- dashboard berdasarkan peran;
- data santri, wali, guru, kelas, kelompok, penugasan, dan jadwal;
- pertemuan, absensi, Tahsin, hafalan, Murajaah, dan tugas;
- buku penghubung;
- pengumuman;
- Pembinaan Jumat;
- laporan CSV dasar;
- PWA dasar;
- branding resmi emerald–gold–ivory.

## 5. Perilaku aplikasi sekarang

- Route `/` mengarah ke halaman login.
- Belum ada website publik.
- Login tersedia di `/login`.
- Setelah login, pengguna diarahkan ke `/dashboard`.
- Aplikasi operasional dan halaman publik belum dipisahkan.

Perilaku root yang langsung login adalah keterbatasan versi sekarang, bukan kerusakan database. Perbaikannya dijadwalkan pada v1.3.0.

## 6. Kondisi database dan seeding

- Migration MySQL telah diperbaiki agar nama indeks tidak melebihi 64 karakter.
- Data awal produksi disimpan dalam payload terenkripsi.
- `INITIAL_TPA_DATA_KEY` hanya berada di Environment Variables, bukan GitHub.
- `scripts/first-install.sh` hanya untuk instalasi pertama pada database kosong.
- Seeder bersifat idempotent dan tidak boleh menimpa password yang sudah diganti.

## 7. Environment Variables penting

Nama variabel yang digunakan antara lain:

- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`
- `DB_URL`
- `INITIAL_ADMIN_NAME`, `INITIAL_ADMIN_EMAIL`, `INITIAL_ADMIN_PHONE`, `INITIAL_ADMIN_PASSWORD`
- `SEED_INITIAL_TPA_DATA`
- `INITIAL_TPA_DATA_KEY`
- `INITIAL_TEACHER_PASSWORD`
- `INITIAL_GUARDIAN_PASSWORD`
- `SEED_DEMO_DATA`

Nilai rahasia tidak boleh dicatat dalam dokumentasi atau issue GitHub.

## 8. Prioritas aktif

**v1.3.0 — Public Website & Route Separation**

Tujuan utamanya adalah menjadikan `/` sebagai halaman publik dan mempertahankan `/login` serta `/dashboard` sebagai pintu aplikasi internal. Lihat `NEXT-RELEASE-v1.3.0.md`.

## Status v1.3.0

Website publik telah tersedia pada route `/`, sedangkan portal internal tetap berada di balik `/login` dan middleware autentikasi. Host `app.sullamulhifz.or.id` disiapkan agar root langsung menuju portal.
