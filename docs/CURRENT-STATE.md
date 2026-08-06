# Current State — Sullamul Ḥifẓ

Tanggal sinkronisasi dokumentasi: 6 Agustus 2026.

## Produksi aktif

- Versi aplikasi yang telah berjalan lancar: **v1.3.0 Public Website**.
- Domain sementara: `taysriulqurani.id`.
- Infrastruktur: Coolify, Dockerfile, NGINX Unit, PHP 8.4, Laravel 13, MySQL 8.
- Website publik dan portal internal masih berada pada domain yang sama.
- Data awal produksi: 88 santri, 88 wali, 4 guru, 6 kelas utama, Tahfizh A dan Tahfizh B.

## Kandidat pengembangan

Repository paket ini ditandai **v1.4.5 Portal Domain Separation**. Kode fungsionalnya berbasis kandidat **v1.4.0 TPA Operational Complete** dan dokumentasi v1.4.1. Rilis v1.4.4 menambahkan Ikrar Santri pada website publik dan portal, editor admin, lima budaya bersama, tiga ruang pembiasaan, serta mode cetak.

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

## Fitur kandidat terbaru

- `/ikrar-santri` — halaman publik;
- `/nilai/ikrar-santri` — portal admin, guru, dan wali;
- `/admin/ikrar-santri` — editor admin;
- data default aman di `config/student_pledge.php`;
- perubahan admin disimpan sebagai JSON pada `system_settings`;
- tidak ada migration baru.

## Batasan saat ini

- Academy penuh belum dibuat.
- Multi-lembaga belum dibuat.
- Pembayaran dan WhatsApp API otomatis belum dibuat.
- Aplikasi native Android/iOS belum dibuat.
- Domain utama `sullamulhifz.or.id` telah aktif; subdomain portal `app.sullamulhifz.or.id` disiapkan melalui v1.4.5.


## Institution Reference v1.4.4

TPA Al-Insyirah kini memiliki profil publik lengkap sebagai implementasi pertama, disertai panduan adaptasi untuk lembaga lain. Data yang belum ditetapkan tetap ditandai sebagai placeholder.


## Portal Domain Separation v1.4.5

Website publik tetap pada `sullamulhifz.or.id`, sedangkan login, dashboard, dan operasi privat diarahkan ke `app.sullamulhifz.or.id`. Domain lama dipertahankan sementara sebagai jalur transisi. Tidak ada perubahan database.

## Update v1.5.0 — Academic Core Complete

Rilis ini menggabungkan profil lembaga, semester aktif, delapan rubu’ Juz 30, target hafalan personal, observasi metode belajar, integrasi portal wali, serta migration otomatis additive dalam satu upload dan satu redeploy. Lihat `UPGRADE-V1.5.0.md` dan `docs/PHASES-v1.5.0.md`.
