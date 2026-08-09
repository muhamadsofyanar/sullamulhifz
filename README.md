# Sullamul Ḥifẓ

**Bukan Sekadar Hafal, Tapi KUAT.**

Rilis kandidat: **v4.1.0 — WhatsApp & Email Completion**.

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
- Character, Talent & Portfolio: progres naratif non-ranking dan evidence portofolio lintas waktu;
- Insight & Automation: reminder Murāja‘ah terjadwal dan AI Assist yang wajib melewati human review serta audit;
- Public Personal Mode: masyarakat dapat daftar mandiri, memiliki workspace privat, mengatur ritme, mencatat hafalan/Murāja‘ah/tilawah/refleksi, membuat target, melihat streak dan arahan harian;
- media terpusat, permission granular, feature flag, aktivasi akun, reset kata sandi, dan audit.
- Pusat Komunikasi WhatsApp/email: StarSender, webhook generik, SMTP, Mailketing API, template, retry, webhook masuk, audit, dan notifikasi Buku Penghubung tanpa menyalin isi privat.

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
2. [`UPGRADE-V4.1.0.md`](UPGRADE-V4.1.0.md)
3. [`DEPLOY-QUICK-V4.1.0.txt`](DEPLOY-QUICK-V4.1.0.txt)
4. [`docs/CURRENT-STATE.md`](docs/CURRENT-STATE.md)
5. [`docs/ROADMAP-10-PHASES-V2.6.0.md`](docs/ROADMAP-10-PHASES-V2.6.0.md)
6. [`docs/PRODUCT-P1-PUBLIC-PERSONAL-V3.0.0.md`](docs/PRODUCT-P1-PUBLIC-PERSONAL-V3.0.0.md)

## Aturan produksi

- backup database dan volume `storage` sebelum upgrade;
- jangan menjalankan `db:wipe` atau `migrate:fresh` pada produksi;
- untuk database yang sudah berisi, gunakan `BOOTSTRAP_PRODUCTION=false`;
- jangan commit `.env`, `APP_KEY`, kredensial database, token, atau data pribadi;
- pertahankan persistent volume `storage/app` saat mengganti image/container;
- aktifkan SMTP atau Mailketing API dari Pusat Komunikasi agar reset kata sandi dapat dikirim melalui email.

Upgrade v4.1.0 bersifat additive terhadap v4.0.0. Data lembaga, pembelajaran, media, dan workspace Personal tetap dipertahankan; kanal komunikasi baru tetap OFF sampai environment provider tersedia dan tes admin berhasil.
