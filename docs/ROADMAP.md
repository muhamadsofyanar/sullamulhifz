# Roadmap Resmi

## v1.4.1 — Documentation Sync

Menyelaraskan status produksi, paket kandidat, domain, handover, dan urutan pengembangan. Tidak mengubah database atau fitur aplikasi.

## v1.4.3 — Ikrar Santri

- halaman publik dan portal;
- editor admin;
- tujuh ikrar;
- lima budaya bersama;
- pembiasaan di kelas, rumah, dan masjid;
- tampilan cetak;
- tanpa migration baru.

## v1.4.x — Stabilisasi TPA

Fokus hanya pada:

- pengujian upgrade dari v1.3.0;
- bug fix;
- keamanan;
- performa;
- aksesibilitas;
- konsistensi hak akses;
- backup/restore;
- kesiapan domain baru;
- penyempurnaan operasional kecil.

Tidak menambahkan modul TPA besar baru sebelum kandidat v1.4.0 dinyatakan stabil.

## Cutover domain baru

Setelah domain aktif dan kandidat lolos pengujian:

- `sullamulhifz.or.id` untuk website publik;
- `app.sullamulhifz.or.id` untuk portal TPA;
- domain lama dipertahankan sementara sebagai cadangan/redirect sampai masa transisi selesai.

## v2.0.0 — Academy MVP

- katalog kursus;
- kelas dan peserta;
- instruktur;
- lesson/modul;
- video atau media eksternal;
- progress belajar;
- peran learner dan instructor;
- halaman Academy pada subdomain khusus.

## v2.1.0

Kuis, penilaian, tugas Academy, dan sertifikat.

## v2.2.0

Pendaftaran berbayar dan otomasi enrollment setelah desain pembayaran, refund, invoice, dan keamanan selesai.

## v2.5.0

Community termoderasi dan agenda keluarga Qur'ani.

## v3.0.0

Multi-lembaga, isolasi tenant, admin lembaga, dan pelaporan lintas lembaga.

## v3.1.0

Identitas terpadu/SSO untuk Website, TPA, Academy, dan Community.


## v1.4.4 — Institution Reference

- profil lengkap TPA Al-Insyirah;
- panduan adaptasi lembaga lain;
- pemisahan fakta dan placeholder;
- referensi tata kelola, privasi, dan prinsip tanpa ranking.


## v1.4.5 — Portal Domain Separation

- website publik pada domain utama;
- portal autentikasi pada subdomain `app`;
- canonical redirect `www`;
- domain lama dipertahankan sementara;
- tanpa perubahan database.
