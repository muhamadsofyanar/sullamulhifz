# Arsitektur Produk dan Aplikasi

## Strategi utama: modular monolith

Sullamul Ḥifẓ tetap satu aplikasi Laravel dan satu deployment selama skala serta tim masih kecil. Modul dipisahkan secara konseptual dan dalam struktur kode, tetapi belum menjadi microservices.

Alasan:

- deployment dan backup lebih sederhana;
- satu akun dapat memiliki banyak peran;
- transaksi lintas modul tetap konsisten;
- biaya operasional rendah;
- debugging lebih mudah.

Microservices hanya dipertimbangkan jika ada kebutuhan skala, tim terpisah, atau beban yang benar-benar berbeda dan terukur.

## Batas modul

### 1. Identity & Access

- user, role, permission, profil;
- autentikasi;
- kewajiban ganti password;
- satu pengguna dengan banyak peran;
- isolasi lembaga pada fase multi-tenant.

### 2. TPA Core

- lembaga;
- tahun ajaran;
- jenjang, kelas, kelompok;
- santri, wali, guru;
- enrollment dan penugasan;
- jadwal.

### 3. Learning Records

- pertemuan;
- absensi;
- Tahsin;
- hafalan baru;
- Murajaah;
- tugas dan bukti;
- target dan laporan perkembangan.

### 4. Communication

- buku penghubung;
- pengumuman;
- Pembinaan Jumat;
- notifikasi.

### 5. Public Website

- halaman publik;
- brand dan program;
- artikel;
- kontak;
- pendaftaran pada fase berikutnya.

Modul publik tidak boleh mengakses data santri secara langsung.

### 6. Academy

- kursus;
- lesson;
- enrollment Academy;
- progres;
- kuis;
- sertifikat;
- pembayaran pada fase lanjut.

Data Academy terhubung ke user, tetapi tidak bergantung pada enrollment santri TPA.

### 7. Community

- agenda;
- kelompok;
- diskusi termoderasi;
- perpustakaan konten.

## Strategi route dan domain

### Sekarang

- satu domain sementara;
- `/` belum publik;
- `/login` untuk autentikasi;
- route internal setelah login.

### v1.3.x

- `/` dan halaman informasi menjadi publik;
- `/login` tetap terpisah;
- `/dashboard` dan route operasional tetap internal.

### Fase matang

- website utama pada domain induk;
- aplikasi operasional pada subdomain `app`;
- Academy pada subdomain `academy`;
- identitas tetap terpadu.

## Aturan database

- migration yang sudah diterapkan tidak diedit;
- perubahan schema dibuat melalui migration baru;
- foreign key dan index diberi nama eksplisit jika berisiko melebihi batas MySQL;
- seeder produksi idempotent;
- seeder tidak menimpa password pengguna yang sudah berubah;
- data antar modul dipisahkan melalui tabel dan relasi yang jelas;
- pada multi-tenant, seluruh tabel data lembaga wajib memiliki `institution_id` atau isolasi setara.

## Aturan penyimpanan file

- file publik hanya untuk aset brand dan konten yang memang publik;
- bukti tugas dan dokumen anak disimpan privat;
- akses file privat selalu melalui controller yang memeriksa izin;
- persistent volume wajib dipertahankan ketika redeploy.
