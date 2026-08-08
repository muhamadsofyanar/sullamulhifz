# Sullamul Ḥifẓ

**Bukan Sekadar Hafal, Tapi KUAT.**

Rilis kandidat: **v2.6.0 — Qur’an Journey**.

Sullamul Ḥifẓ adalah platform web responsif/PWA untuk pencatatan pembelajaran Al-Qur’an, operasional lembaga, tugas rumah, komunikasi guru–wali, dan perjalanan perkembangan santri. TPA Al-Insyirah menjadi implementasi pertama, sementara fondasinya disiapkan untuk cabang dan lembaga lain.

## Modul utama

- admin, kepala TPA, guru, dan orang tua/wali;
- tahun ajaran, periode, jenjang, kelas, kelompok, jadwal, dan penugasan guru;
- pertemuan, absensi, tahsīn, tahfizh, murāja‘ah, target hafalan, dan observasi belajar;
- tugas, bukti tugas privat, buku penghubung, pengumuman, dan Pembinaan Jumat;
- Full Qur’an 30 juz: mushaf 114 surah/6.236 ayat, 604 halaman, 240 Rubu‘ al-Hizb, dua qari, bookmark, target latihan, dan riwayat baca;
- Qur’an Journey: Marhalah berbasis Juz, milestone/penjagaan, Khatam 30 Hari, Fami Bisyauqin, dan Peta Mushaf & Warisan Ulama;
- Parent Academy/LMS, rekomendasi materi keluarga, website publik, pendaftaran, dan rapor;
- media terpusat, permission granular, feature flag, aktivasi akun, reset kata sandi, dan audit.

## Stack

- PHP 8.4 dan Laravel 13;
- MySQL;
- Blade, CSS, dan JavaScript mandiri;
- Docker dan NGINX Unit;
- deployment GitHub → Coolify.

## Domain produksi

- `sullamulhifz.or.id` dan `www.sullamulhifz.or.id`: website publik;
- `app.sullamulhifz.or.id`: portal aplikasi;
- `academy.sullamulhifz.or.id`: pintu masuk Academy;
- `api.sullamulhifz.or.id`: API starter;
- `staging.sullamulhifz.or.id`: staging, disarankan memakai resource/database terpisah.

## Mulai dari sini

1. [`START-HERE.md`](START-HERE.md)
2. [`UPGRADE-V2.6.0.md`](UPGRADE-V2.6.0.md)
3. [`DEPLOY-QUICK-V2.6.0.txt`](DEPLOY-QUICK-V2.6.0.txt)
4. [`docs/CURRENT-STATE.md`](docs/CURRENT-STATE.md)
5. [`docs/ROADMAP-10-PHASES-V2.6.0.md`](docs/ROADMAP-10-PHASES-V2.6.0.md)
6. [`docs/PHASE-04-QURAN-JOURNEY-V2.6.0.md`](docs/PHASE-04-QURAN-JOURNEY-V2.6.0.md)

## Aturan produksi

- backup database dan volume `storage` sebelum upgrade;
- jangan menjalankan `db:wipe` atau `migrate:fresh` pada produksi;
- untuk database yang sudah berisi, gunakan `BOOTSTRAP_PRODUCTION=false`;
- jangan commit `.env`, `APP_KEY`, kredensial database, token, atau data pribadi;
- pertahankan persistent volume `storage/app` saat mengganti image/container;
- aktifkan SMTP agar reset kata sandi dapat dikirim melalui email.

Migration v2.6.0 bersifat additive. Data Tahsīn, Tahfizh, Murāja‘ah, target, Quran Learning dan Academy lama tetap dipertahankan; v2.6 menambahkan Qur’an Journey tanpa menghapus histori Fase 3.
