# Next Major Release — v2.0.0 Academy MVP

Dokumen ini adalah arah pengembangan besar setelah v1.4.x stabil dan cutover domain selesai.

## Prasyarat

Academy tidak dimulai sebelum:

- portal TPA v1.4.x stabil;
- backup dan restore teruji;
- domain publik dan portal aktif;
- hak akses admin, guru, dan wali lolos pengujian;
- tidak ada error kritis yang belum diselesaikan.

## Tujuan

Menyediakan LMS dasar untuk orang tua, guru Al-Qur'an, pengelola TPA, dan peserta umum tanpa mencampurkan data operasional santri TPA secara sembarangan.

## Scope MVP

- katalog kursus;
- kategori dan level;
- instruktur;
- enrollment;
- modul dan lesson;
- video eksternal atau file materi;
- progress belajar;
- penandaan lesson selesai;
- dashboard peserta;
- dashboard instruktur;
- kursus gratis dan status publik/draft;
- halaman Academy di `academy.sullamulhifz.or.id`.

## Tidak termasuk v2.0.0

- pembayaran;
- marketplace instruktur;
- streaming video internal;
- sertifikat otomatis;
- ujian berpengawasan;
- aplikasi mobile native;
- multi-lembaga penuh.

## Keputusan arsitektur yang harus dibuat sebelum coding

1. Academy tetap satu Laravel monolith atau resource terpisah.
2. Satu database atau database terpisah.
3. Strategi satu akun lintas TPA dan Academy.
4. Penyimpanan video dan file.
5. Model izin instruktur dan reviewer konten.
6. Kebijakan privasi peserta anak dan dewasa.

## Kriteria penerimaan awal

- pengguna dapat melihat katalog;
- pengguna login dapat mendaftar kursus gratis;
- peserta dapat membuka lesson dan melihat progress;
- instruktur hanya mengelola kursus yang ditugaskan;
- admin dapat menerbitkan dan menarik kursus;
- data TPA tidak bocor ke Academy;
- seluruh migration additive dan terdokumentasi.
