# Sullamul Ḥifẓ

**Bukan Sekadar Hafal, Tapi KUAT.**

Rilis aktif: **v2.3.0 — Integrated Learning Ecosystem**.

Sullamul Ḥifẓ adalah platform web responsif/PWA untuk pencatatan pembelajaran Al-Qur’an, operasional lembaga, tugas rumah, komunikasi guru–wali, dan perjalanan perkembangan santri. TPA Al-Insyirah menjadi implementasi pertama, sementara fondasinya disiapkan untuk cabang dan lembaga lain.

## Modul utama

- admin, kepala TPA, guru, dan orang tua/wali;
- tahun ajaran, periode, jenjang, kelas, kelompok, jadwal, dan penugasan guru;
- pertemuan, absensi, tahsīn, tahfizh, murāja‘ah, target hafalan, dan observasi belajar;
- tugas, bukti tugas privat, buku penghubung, pengumuman, dan Pembinaan Jumat;
- Audio Qur’an, preset latihan, dua qari, target latihan, dan riwayat sesi;
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
- `academy.sullamulhifz.or.id`: portal LMS Academy mandiri dengan Audio Qur’an internal;
- `api.sullamulhifz.or.id`: API starter;
- `staging.sullamulhifz.or.id`: staging, disarankan memakai resource/database terpisah.

## Mulai dari sini

1. [`START-HERE.md`](START-HERE.md)
2. [`UPGRADE-V2.3.0.md`](UPGRADE-V2.3.0.md)
3. [`DEPLOY-QUICK-V2.3.0.txt`](DEPLOY-QUICK-V2.3.0.txt)
4. [`docs/ROADMAP-10-PHASES-V2.3.0.md`](docs/ROADMAP-10-PHASES-V2.3.0.md)
5. [`docs/CURRENT-STATE.md`](docs/CURRENT-STATE.md)
6. [`BUILD-REPORT-V2.3.0.md`](BUILD-REPORT-V2.3.0.md)

## Aturan produksi

- backup database dan volume `storage` sebelum upgrade;
- jangan menjalankan `db:wipe` atau `migrate:fresh` pada produksi;
- untuk database yang sudah berisi, gunakan `BOOTSTRAP_PRODUCTION=false`;
- jangan commit `.env`, `APP_KEY`, kredensial database, token, atau data pribadi;
- pertahankan persistent volume `storage/app` saat mengganti image/container;
- aktifkan SMTP agar reset kata sandi dapat dikirim melalui email.

Migration v2.3.0 bersifat additive. Data operasional lama dipertahankan; seeder startup kini hanya memberi nilai awal sehingga perubahan feature flag, konten Academy, cabang, dan periode yang dilakukan admin tidak ditimpa saat restart/redeploy.
