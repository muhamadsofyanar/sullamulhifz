# Sullamul Ḥifẓ v1.4.1 — Documentation Sync

**Bukan Sekadar Hafal, Tapi KUAT.**

Rilis ini menyelaraskan dokumentasi proyek. Kode fungsionalnya berbasis kandidat v1.4.0 TPA Operational Complete; tidak ada perubahan database pada v1.4.1.

> Produksi aktif yang telah berjalan adalah v1.3.0 pada `taysriulqurani.id`. Paket v1.4.x adalah kandidat yang harus diuji sebelum dipindahkan ke domain baru.

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
