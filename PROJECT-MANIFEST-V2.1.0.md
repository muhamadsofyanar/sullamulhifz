# Project Manifest — Sullamul Ḥifẓ v2.1.0

## Identitas rilis

- **Nama:** Sullamul Ḥifẓ
- **Rilis:** v2.1.0 — Unified Platform & Secure Media
- **Tagline:** Bukan Sekadar Hafal, Tapi KUAT
- **Stack:** PHP 8.4, Laravel 13, Blade, MySQL, NGINX Unit, Docker
- **Pola deploy:** GitHub → Coolify → migration additive → seeder idempoten

## Pemetaan domain

- `sullamulhifz.or.id` dan `www.sullamulhifz.or.id`: website publik
- `app.sullamulhifz.or.id`: portal aplikasi
- `academy.sullamulhifz.or.id`: pintu masuk Academy
- `api.sullamulhifz.or.id`: API starter dan health endpoint
- `staging.sullamulhifz.or.id`: resource staging terpisah; nonaktif pada production secara default

## Modul inti

- autentikasi, aktivasi akun, dan reset kata sandi;
- lembaga, cabang, tahun ajaran, periode, jenjang, kelas, kelompok, dan jadwal;
- guru, santri, wali, hubungan keluarga, dan penugasan guru;
- pertemuan, absensi, tahsīn, tahfizh, murāja‘ah, dan rencana belajar;
- tugas kelas/individual, unggah bukti, pemeriksaan, dan revisi;
- Buku Penghubung privat;
- pengumuman dan Pembinaan Jumat dengan target fleksibel;
- laporan, rapor perkembangan, audit, dan privasi media.

## Modul pengembangan yang dipertahankan

- Audio dan latihan Al-Qur’an;
- Parent Academy / LMS;
- website publik dan artikel;
- pendaftaran santri;
- rapor perkembangan;
- fondasi community dan multi-cabang.

Modul pengembangan dikendalikan melalui **Admin → Fondasi Platform**, sehingga dapat disiapkan lebih dahulu dan diaktifkan tanpa mengubah source code.

## Fondasi database v2.1.0

Migration baru menambahkan:

- `branches`;
- `academic_periods`;
- `media_assets` dan `media_links`;
- `announcement_targets`;
- `friday_session_targets`;
- `student_marhalah_histories`;
- `account_invitations`;
- `feature_flags`;
- kolom cabang, media, dan riwayat pada tabel lama.

Migration bersifat additive dan mempertahankan kolom legacy untuk kompatibilitas data lama.

## Keamanan utama

- tenant scoping berdasarkan `institution_id`;
- role dan permission middleware;
- trusted proxy dibatasi ke jaringan internal;
- upload menggunakan allow-list tipe file, UUID, checksum, visibility, dan masa simpan;
- media privat disajikan melalui controller berizin dengan `no-store`;
- service worker hanya menyimpan aset statis eksplisit;
- akses administratif ke percakapan/media privat dicatat;
- password awal produksi tidak memiliki fallback yang mudah ditebak;
- staging production dinonaktifkan kecuali `STAGING_ENABLED=true`.

## Perilaku startup container

Startup v2.1.0 akan:

1. menyiapkan direktori persistent storage;
2. menunggu database;
3. menjalankan migration;
4. menjalankan seeder idempoten;
5. mengamankan media legacy publik;
6. menjalankan verifier;
7. membuat storage link dan cache aplikasi;
8. menjalankan NGINX Unit;
9. menyinkronkan Audio Qur’an di latar belakang.

## Berkas operasional utama

- `START-HERE.md`
- `DEPLOY-QUICK-V2.1.0.txt`
- `DEPLOY-COOLIFY-V2.1.0.md`
- `UPGRADE-V2.1.0.md`
- `BUILD-REPORT-V2.1.0.md`
- `scripts/smoke-test-v2.1.0.sh`

## Batas verifikasi workspace

Source awal tidak menyertakan `composer.lock` atau `vendor/`, dan executable Composer tidak tersedia di workspace. Karena itu test runtime Laravel tidak dijalankan secara lokal. Docker build Coolify akan memasang dependency, lalu pemeriksaan runtime dilakukan melalui log startup dan smoke test setelah deploy.
