# Sullamul Ḥifẓ

**Bukan Sekadar Hafal, Tapi KUAT.**

Rilis kandidat: **v2.9.0 — Personal Learning System**.

Sullamul Ḥifẓ adalah platform web responsif/PWA untuk pencatatan pembelajaran Al-Qur’an, operasional lembaga, tugas rumah, komunikasi guru–wali, dan perjalanan perkembangan santri. TPA Al-Insyirah menjadi implementasi pertama, sementara fondasinya disiapkan untuk cabang dan lembaga lain.

## Modul utama

- admin, kepala TPA, guru, dan orang tua/wali;
- tahun ajaran, periode, jenjang, kelas, kelompok, jadwal, dan penugasan guru;
- pertemuan, absensi, tahsīn, tahfizh, murāja‘ah, target hafalan, dan observasi belajar;
- tugas, bukti tugas privat, buku penghubung, pengumuman, dan Pembinaan Jumat;
- Full Qur’an 30 juz: mushaf 114 surah/6.236 ayat, 604 halaman, 240 Rubu‘ al-Hizb, dua qari, bookmark, target latihan, dan riwayat baca;
- Qur’an Journey: Marhalah berbasis Juz, milestone/penjagaan, Khatam 30 Hari, Fami Bisyauqin, dan Peta Mushaf & Warisan Ulama;
- Academy LMS 2.0: program/modul/materi, learning path, prerequisite, kuis, worksheet, progress, bookmark, refleksi, dan sertifikat;
- Family & Teacher Ecosystem: aktivitas keluarga guru→wali→guru, refleksi pendampingan, kompetensi/pelatihan guru berbasis bukti naratif tanpa ranking;
- Personal Learning System: rekomendasi berbasis observasi/progres nyata dengan teacher override tercatat; STIFIn bukan evidence rekomendasi;
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
2. [`UPGRADE-V2.9.0.md`](UPGRADE-V2.9.0.md)
3. [`DEPLOY-QUICK-V2.9.0.txt`](DEPLOY-QUICK-V2.9.0.txt)
4. [`docs/CURRENT-STATE.md`](docs/CURRENT-STATE.md)
5. [`docs/ROADMAP-10-PHASES-V2.6.0.md`](docs/ROADMAP-10-PHASES-V2.6.0.md)
6. [`docs/PHASE-07-PERSONAL-LEARNING-V2.9.0.md`](docs/PHASE-07-PERSONAL-LEARNING-V2.9.0.md)

## Aturan produksi

- backup database dan volume `storage` sebelum upgrade;
- jangan menjalankan `db:wipe` atau `migrate:fresh` pada produksi;
- untuk database yang sudah berisi, gunakan `BOOTSTRAP_PRODUCTION=false`;
- jangan commit `.env`, `APP_KEY`, kredensial database, token, atau data pribadi;
- pertahankan persistent volume `storage/app` saat mengganti image/container;
- aktifkan SMTP agar reset kata sandi dapat dikirim melalui email.

Migration v2.9.0 bersifat additive. Data Fase 1–6 tetap dipertahankan; tabel baru hanya menambah audit review/override rekomendasi personal.
