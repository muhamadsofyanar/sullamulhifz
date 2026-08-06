# Current State — Sullamul Ḥifẓ

Tanggal sinkronisasi: 6 Agustus 2026.

## Infrastruktur

- Laravel 13, PHP 8.4, NGINX Unit, MySQL 8.0-bookworm.
- Deployment: GitHub `main` melalui Coolify.
- Website: `sullamulhifz.or.id`.
- Portal: `app.sullamulhifz.or.id`.
- Legacy/cadangan: `taysriulqurani.id`.

## Data produksi

- 88 santri;
- 88 wali;
- 4 guru;
- 6 kelas utama;
- 2 kelompok Tahfizh.

## Baseline

v1.5.1 adalah baseline stabil sebelum paket ini: Academic Core v1.5.0 dengan hotfix startup NGINX Unit.

## Paket saat ini

**v1.6.1 — Qari Tahfizh** melanjutkan Quran Learning dengan:

- tabel pustaka audio, timing ayat, preset, sesi latihan, dan video;
- qari utama Mahmoud Khalil Al-Husary untuk ketelitian tahfizh;
- qari pilihan Muhammad Siddiq Al-Minshawi untuk murajaah dan tadabbur;
- sinkronisasi 37 surah / 564 ayat Juz 30 untuk masing-masing qari (1.128 timing total);
- latihan ayat, rentang, surah, halaman, rubu’, dan target santri;
- pengulangan per ayat atau seluruh pilihan;
- jumlah ulang, jeda, dan kecepatan yang dapat dipilih;
- contoh An-Nās ayat 1 sebanyak 10 kali;
- contoh Al-Qāri‘ah ayat 1–5 sebanyak 10 kali per ayat;
- Pustaka Qur’an admin;
- video terkurasi;
- sinkronisasi latar belakang agar startup web tetap cepat.

## Status kelengkapan

Migration dan source tersedia dalam paket. Kelengkapan audio runtime harus diverifikasi setelah deployment melalui:

```bash
php artisan sullam:verify-quran-learning
```

Target master referensi adalah 564 timing per qari atau 1.128 timing untuk dua qari. Data progres santri tetap berasal dari input nyata guru dan wali.

## Batasan yang disengaja

- video tidak diisi otomatis;
- Academy penuh belum dibangun;
- aplikasi native belum dibangun;
- WhatsApp API otomatis belum dibangun;
- multi-lembaga penuh belum diaktifkan untuk publik.
