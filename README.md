# Sullamul Ḥifẓ v1.4.4 — Institution Reference

**Bukan Sekadar Hafal, Tapi KUAT.**

Rilis kandidat ini melengkapi TPA Al-Insyirah sebagai profil implementasi pertama Sullamul Ḥifẓ dan menyediakan panduan adaptasi bagi lembaga lain. Fitur Ikrar Santri v1.4.3 tetap tersedia. Tidak ada migration baru.

> Produksi aktif yang telah berjalan adalah v1.3.0 pada `taysriulqurani.id`. Paket v1.4.x tetap merupakan kandidat yang harus diuji sebelum dipindahkan ke domain baru.

## Mulai dari sini

- [`START-HERE.md`](START-HERE.md)
- [`docs/CURRENT-STATE.md`](docs/CURRENT-STATE.md)
- [`docs/ROADMAP.md`](docs/ROADMAP.md)
- [`docs/HANDOVER-NEXT-CHAT.md`](docs/HANDOVER-NEXT-CHAT.md)
- [`docs/NEXT-RELEASE-v2.0.0.md`](docs/NEXT-RELEASE-v2.0.0.md)

## Produk

Sullamul Ḥifẓ mencakup:

- website publik;
- portal operasional TPA untuk admin, guru, dan wali;
- pencatatan kehadiran, Tahsin, Tahfizh, Murajaah, tugas, buku penghubung, pengumuman, dan Pembinaan Jumat;
- pengelolaan data awal TPA Al-Insyirah;
- arah pengembangan Academy pada fase v2.


## Fitur v1.4.4

- profil lengkap publik `/lembaga/tpa-al-insyirah`;
- panduan adaptasi `/referensi-lembaga`;
- identitas, statistik, kelas, kelompok Tahfizh, guru, program, alur pembinaan, nilai, dan kemitraan keluarga;
- pemisahan tegas antara data yang telah ditetapkan dan data placeholder;
- daftar bagian yang dapat ditiru dan yang wajib disesuaikan lembaga lain;
- poster Ikrar Santri sebagai referensi visual;
- konfigurasi profil yang dapat dioverride melalui `institutions.settings.reference_profile`;
- sitemap, navigasi, dokumentasi, dan feature test baru.

## Fitur v1.4.3

- halaman publik `/ikrar-santri`;
- halaman portal `/nilai/ikrar-santri`;
- editor admin `/admin/ikrar-santri`;
- tujuh ikrar santri;
- lima budaya bersama;
- contoh pembiasaan di kelas, rumah, dan masjid;
- mode cetak;
- fallback konfigurasi aman ketika tabel pengaturan belum tersedia.

## Data awal TPA Al-Insyirah

- 88 santri;
- 88 wali;
- guru Nurul, Jundi, Yanti, dan Sofyan;
- 6 kelas utama;
- Tahfizh A: 30 santri;
- Tahfizh B: 27 santri.

## Infrastruktur

- PHP 8.4+;
- Laravel 13;
- MySQL 8;
- Blade + CSS/JavaScript mandiri;
- Docker + NGINX Unit;
- Coolify;
- PWA dasar.

## Domain target

- `sullamulhifz.or.id` — website publik;
- `app.sullamulhifz.or.id` — portal TPA;
- `academy.sullamulhifz.or.id` — Academy mendatang.

## Larangan produksi

Jangan menjalankan perintah berikut pada database produksi yang sudah berisi data:

```text
php artisan db:wipe
php artisan migrate:fresh
scripts/first-install.sh
ProductionSeeder
```

Upgrade kandidat v1.4.x harus mengikuti `docs/UPGRADE-v1.4.0.md`, setelah backup dan pengujian pada database terpisah.

## Rahasia

Jangan commit:

- `.env`;
- APP key;
- DB URL;
- password;
- data key;
- daftar akun rahasia;
- dump database.

Panduan deployment rinci tetap tersedia pada `README-COOLIFY.md`.
