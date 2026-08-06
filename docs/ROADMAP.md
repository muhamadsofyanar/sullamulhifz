# Roadmap Pengembangan Sullamul Ḥifẓ

Roadmap ini mengatur urutan pengembangan agar aplikasi tidak tumbuh menjadi satu sistem besar yang sulit dirawat. Nomor versi adalah target kerja; ruang lingkup dapat diperkecil, tetapi urutannya tidak boleh dilompati tanpa keputusan yang dicatat di `DECISIONS.md`.

## Prinsip urutan

1. Stabilkan fondasi dan data terlebih dahulu.
2. Selesaikan satu alur pengguna sampai tuntas sebelum menambah produk baru.
3. Website, aplikasi TPA, Academy, dan Community tetap satu ekosistem, tetapi memiliki batas modul yang jelas.
4. Selama skala masih kecil, gunakan modular monolith Laravel; jangan memecah menjadi microservices.
5. Setiap versi wajib memiliki panduan upgrade, verifikasi, dan rollback.

---

## Baseline — v1.2.1 Documentation Governance

**Status:** selesai setelah dokumen ini masuk repository.

Tujuan:

- menyimpan konteks proyek di GitHub;
- menetapkan prosedur rilis dan upgrade;
- mencegah ketergantungan pada riwayat chat;
- menambahkan pemeriksaan otomatis agar setiap versi memiliki dokumen rilis.

Tidak ada perubahan database atau fitur pengguna.

---

## Fase 1 — Website Publik dan Pemisahan Pintu

### v1.3.0 — Public Website & Route Separation

**Prioritas berikutnya.**

Ruang lingkup:

- `/` menjadi landing page publik;
- halaman Tentang, Program, TPA, Academy, Artikel, dan Kontak;
- tombol Masuk mengarah ke `/login`;
- aplikasi internal tetap di `/dashboard` dan route ber-auth;
- header, footer, SEO dasar, Open Graph, sitemap, robots, dan tampilan mobile;
- konten publik tidak boleh menampilkan data santri, wali, atau guru tanpa izin.

Tidak termasuk:

- pendaftaran online yang menyimpan data;
- pembayaran;
- LMS Academy;
- blog admin lengkap.

Kriteria selesai:

- pengunjung tanpa login dapat membuka `/`;
- `/login` tetap berfungsi;
- pengguna yang sudah login dapat masuk ke `/dashboard`;
- seluruh halaman publik responsif dan konsisten dengan brand guide;
- tidak ada perubahan atau penghapusan data produksi.

### v1.3.1 — Public Website Stabilization

Ruang lingkup:

- perbaikan hasil uji pengguna;
- aksesibilitas keyboard dan kontras;
- optimasi gambar dan performa;
- halaman kebijakan privasi dan syarat penggunaan;
- analytics hanya setelah kebijakan privasi disetujui.

---

## Fase 2 — Penguatan Operasional TPA

### v1.4.0 — Data Operations & Administration

Ruang lingkup:

- impor CSV/Excel untuk santri, wali, dan guru;
- validasi duplikasi dan pratinjau sebelum impor;
- penggabungan beberapa anak ke satu akun wali;
- manajemen akun dan reset password oleh admin;
- audit log perubahan data penting;
- status aktif/nonaktif dan arsip tahun ajaran;
- backup dan restore runbook yang diuji.

Kriteria selesai:

- impor tidak membuat duplikasi diam-diam;
- setiap perubahan kritis tercatat;
- admin dapat memperbaiki data tanpa akses database langsung;
- data tahun ajaran lama tetap dapat dibaca.

### v1.4.1 — Hardening & Bug Fixes

- perbaikan hasil penggunaan nyata;
- penguatan validasi;
- pagination, pencarian, dan filter;
- perbaikan mobile untuk halaman admin.

---

## Fase 3 — Ruang Kerja Guru

### v1.5.0 — Teacher Workspace

Ruang lingkup:

- dashboard guru yang benar-benar berdasarkan penugasan;
- buka/tutup pertemuan;
- absensi cepat di ponsel;
- input Tahsin, hafalan baru, dan Murajaah;
- target per anak dan catatan tindak lanjut;
- dua skenario jam belajar: Tahfizh dahulu atau Tahsin dahulu;
- kelompok Tahfizh A/B dan kelas utama tetap dipisahkan secara data.

Kriteria selesai:

- guru hanya melihat kelas/kelompok yang diampu;
- input satu pertemuan dapat selesai dari ponsel;
- data hafalan tidak tertukar dengan Murajaah;
- riwayat perubahan dapat ditelusuri.

### v1.5.1 — Teacher UX Stabilization

- mode cepat;
- autosave aman;
- pencegahan input ganda;
- ekspor rekap guru;
- perbaikan berdasarkan simulasi pertemuan nyata.

---

## Fase 4 — Portal Orang Tua dan Komunikasi

### v1.6.0 — Parent Portal

Ruang lingkup:

- satu akun untuk beberapa anak;
- ringkasan perkembangan anak;
- absensi, Tahsin, hafalan, Murajaah, dan tugas;
- buku penghubung privat;
- pengumuman lembaga dan kelas;
- kalender dan jadwal;
- tampilan mobile-first.

Kriteria selesai:

- wali hanya dapat melihat anak yang terhubung;
- satu wali dapat berpindah antar anak tanpa login ulang;
- bukti tugas tetap privat;
- catatan guru tidak bocor ke wali lain.

### v1.6.1 — Notification Foundation

- notifikasi dalam aplikasi;
- preferensi notifikasi;
- antrean notifikasi;
- email opsional;
- WhatsApp belum otomatis kecuali ada keputusan dan kepatuhan yang jelas.

---

## Fase 5 — Laporan, Rubu, dan Evaluasi

### v1.7.0 — Progress & Reports

Ruang lingkup:

- rekap progres per Rubu Juz 30;
- laporan individu dan kelas;
- rapor perkembangan periodik;
- filter tahun ajaran, kelas, program, dan guru;
- ekspor PDF/Excel;
- indikator tanpa ranking santri.

Kriteria selesai:

- rekap Rubu berubah berdasarkan data aktual;
- laporan dapat ditelusuri ke catatan pertemuan;
- istilah dan urutan surat mengikuti pedoman TPA yang disetujui.

### v1.7.1 — Quality Review

- validasi laporan dengan guru;
- perbaikan formula dan label;
- penguncian periode laporan;
- tanda tangan digital sederhana atau ruang tanda tangan cetak.

---

## Fase 6 — Pendaftaran dan Website Dinamis

### v1.8.0 — Admissions & Public Content

Ruang lingkup:

- pendaftaran santri baru;
- status proses pendaftaran;
- persetujuan privasi;
- pengelolaan artikel dan halaman publik;
- formulir minat Academy;
- proteksi spam dan rate limiting.

Kriteria selesai:

- data calon santri terpisah dari santri aktif;
- tidak ada akun wali otomatis sebelum diterima;
- admin dapat mengubah konten tanpa mengubah kode.

---

## Fase 7 — Sullamul Ḥifẓ Academy

### v2.0.0 — Academy MVP

Ruang lingkup:

- katalog program;
- kursus, modul, video/audio, dan materi unduhan;
- enrollment peserta;
- progres belajar;
- kuis dasar;
- instruktur Academy;
- satu akun dapat memiliki peran wali/guru sekaligus peserta Academy.

Target program awal:

- Parenting Qur’ani;
- Mendampingi Hafalan Anak;
- Pelatihan Guru Tahsin;
- Pelatihan Guru Tahfizh;
- Manajemen TPA;
- STIFIn Tahfiz, setelah materi dan dasar hukumnya siap.

Kriteria selesai:

- Academy merupakan modul terpisah dari data operasional TPA;
- hak akses kursus tidak membuka data santri;
- progres peserta tersimpan dan dapat dilanjutkan.

### v2.1.0 — Assessment & Certificates

- bank soal;
- penilaian tugas;
- kelulusan;
- sertifikat yang dapat diverifikasi;
- cohort dan jadwal kelas.

### v2.2.0 — Commerce

- produk gratis/berbayar;
- invoice dan status pembayaran;
- integrasi payment gateway setelah legalitas, kebijakan refund, dan pencatatan keuangan siap;
- kupon atau beasiswa.

---

## Fase 8 — Community

### v2.5.0 — Guided Community

Ruang lingkup:

- agenda pembinaan;
- kelompok belajar;
- diskusi terarah dan termoderasi;
- perpustakaan konten;
- bukan media sosial terbuka.

Kriteria selesai:

- moderasi, pelaporan, dan privasi tersedia;
- ruang anak dan orang dewasa tidak dicampur tanpa kebijakan perlindungan anak.

---

## Fase 9 — Multi-Lembaga

### v3.0.0 — Multi-Institution Platform

Ruang lingkup:

- onboarding lembaga baru;
- isolasi data per lembaga;
- admin pusat dan admin lembaga;
- konfigurasi program, kelas, dan branding lembaga;
- paket penggunaan dan batas kapasitas;
- migrasi data TPA Al-Insyirah tanpa kehilangan riwayat.

Kriteria selesai:

- tidak ada query lintas lembaga tanpa otorisasi;
- backup dan restore dapat dilakukan per lembaga;
- pengujian isolasi tenant wajib lulus.

### v3.1.0 — Unified Identity & Domain Separation

Target arsitektur domain:

- `sullamulhifz.id` — website utama;
- `app.sullamulhifz.id` — aplikasi lembaga/TPA;
- `academy.sullamulhifz.id` — Academy;
- satu akun dan satu profil dengan banyak peran.

Pemecahan domain dilakukan setelah modul dan identitas stabil, bukan di awal.
