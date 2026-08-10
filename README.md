# Sullamul Ḥifẓ

**Bukan Sekadar Hafal, Tapi KUAT.**

Rilis kandidat: **v6.0.0 — Gratis, Infak Sukarela & Setoran Tanpa Distraksi**.

Sullamul Ḥifẓ adalah platform web responsif/PWA untuk perjalanan Al-Qur’an secara personal, bersama ustadz, bersama keluarga, atau melalui lembaga. TPA Al-Insyirah adalah implementasi pertama dan studi kasus, bukan identitas utama produk.

## Modul utama

- admin, kepala TPA, guru, dan orang tua/wali;
- tahun ajaran, periode, jenjang, kelas, kelompok, jadwal, dan penugasan guru;
- pertemuan, absensi, tahsīn, tahfizh, murāja‘ah, target hafalan, dan observasi belajar;
- setoran harian cepat dengan keputusan **Lanjut / Kuatkan / Ulang**, Tangga Fokus individual, asesmen lima aspek berkala, serta form rinci lama untuk kebutuhan khusus;
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
- seluruh fungsi inti gratis tanpa subscription; infak sukarela memiliki pencatatan dan verifikasi tersendiri serta tidak mengubah hak akses.
- Pusat Komunikasi WhatsApp/email: StarSender, webhook generik, SMTP, Mailketing API, template, retry, webhook masuk, audit, dan notifikasi Buku Penghubung tanpa menyalin isi privat.
- satu akun dengan beberapa workspace, pemilih konteks, dan relasi berbasis persetujuan;
- onboarding multi-tenant untuk TPA, sekolah, pesantren, kampus, serta komunitas dengan istilah dan branding adaptif;
- phase registry dan manifest otomatis agar asal fase setiap file pengembangan dapat diaudit.
- Ruang Belajar Terpadu v4.9: ringkasan Personal atas Latihan Qur’an, Qur’an Journey, Program Asatidz, Academy, target, arahan Ustadz Privat, dan tugas lembaga tanpa menggandakan data privat.

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
2. [`UPGRADE-V6.0.0.md`](UPGRADE-V6.0.0.md)
3. [`DEPLOY-QUICK-V6.0.0.txt`](DEPLOY-QUICK-V6.0.0.txt)
4. [`docs/CURRENT-STATE.md`](docs/CURRENT-STATE.md)
5. [`docs/ROADMAP-10-PHASES-V2.6.0.md`](docs/ROADMAP-10-PHASES-V2.6.0.md)
6. [`docs/PRODUCT-P1-PUBLIC-PERSONAL-V3.0.0.md`](docs/PRODUCT-P1-PUBLIC-PERSONAL-V3.0.0.md)

## Aturan produksi

- backup database dan volume `storage` sebelum upgrade;
- jangan menjalankan `db:wipe` atau `migrate:fresh` pada produksi;
- untuk database yang sudah berisi, gunakan `BOOTSTRAP_PRODUCTION=false`;
- web replica memakai `RUN_RELEASE_TASKS=false`; migration dijalankan sekali melalui release job atau terminal operator dengan `--isolated`;
- jangan commit `.env`, `APP_KEY`, kredensial database, token, atau data pribadi;
- pertahankan persistent volume `storage/app` saat mengganti image/container;
- aktifkan SMTP atau Mailketing API dari Pusat Komunikasi agar reset kata sandi dapat dikirim melalui email.

Upgrade v6.0.0 bersifat additive terhadap v5.3.0. Data lembaga, pembelajaran, media, workspace Personal, hubungan Ustadz/Keluarga, ledger komunikasi, serta histori paket/invoice lama tetap dipertahankan. Form setoran rinci lama juga tetap tersedia untuk asesmen dan kasus khusus.
