# Current State — Sullamul Ḥifẓ

Tanggal sinkronisasi dokumentasi: 6 Agustus 2026.

## Produksi aktif

- Versi aplikasi yang telah berjalan lancar: **v1.3.0 Public Website**.
- Domain sementara: `taysriulqurani.id`.
- Infrastruktur: Coolify, Dockerfile, NGINX Unit, PHP 8.4, Laravel 13, MySQL 8.
- Website publik dan portal internal masih berada pada domain yang sama.
- Data awal produksi: 88 santri, 88 wali, 4 guru, 6 kelas utama, Tahfizh A dan Tahfizh B.

## Kandidat pengembangan

Repository paket ini ditandai **v1.4.1 Documentation Sync**. Kode fungsionalnya berbasis kandidat **v1.4.0 TPA Operational Complete**, yang menambahkan antara lain administrasi wali, impor CSV, CMS publik, pendaftaran, pengumuman tertarget, lampiran buku penghubung, Pembinaan Jumat yang diperluas, rapor, ekspor, riwayat login, dan pengaturan operasional.

Kandidat v1.4.x belum boleh dinyatakan stabil hanya karena source tersedia. Sebelum produksi, wajib dilakukan:

1. deployment pada aplikasi dan database uji terpisah;
2. instalasi database kosong;
3. pengujian upgrade dari salinan database v1.3.0;
4. pengujian admin, guru, dan wali;
5. backup produksi;
6. satu kali cutover terkontrol.

## Domain target

- `sullamulhifz.or.id` — website utama publik.
- `www.sullamulhifz.or.id` — redirect ke domain utama.
- `app.sullamulhifz.or.id` — portal TPA.
- `academy.sullamulhifz.or.id` — Academy, belum LMS pada fase v1.

## Batasan saat ini

- Academy penuh belum dibuat.
- Multi-lembaga belum dibuat.
- Pembayaran dan WhatsApp API otomatis belum dibuat.
- Aplikasi native Android/iOS belum dibuat.
- Domain baru masih menunggu aktivasi/verifikasi DNS.
