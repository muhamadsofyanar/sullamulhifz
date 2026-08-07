# Build Report — Sullamul Hifz v2.2.0

Tanggal: 7 Agustus 2026
Rilis: `v2.2.0`

## Pemeriksaan statis

- 160 file PHP pada `app`, `bootstrap`, `config`, `database`, dan `routes` lolos `php -l`.
- 24 shell script lolos `sh -n`.
- 6 file JSON/webmanifest valid.
- 15 aset Service Worker ditemukan seluruhnya; tidak ada aset cache yang hilang.
- Seluruh literal route `academy.portal.*` yang dipakai view mempunyai definisi route yang sesuai.
- Video contoh Academy menggunakan `https://www.youtube.com/watch?v=V_dovd7ezCA`.

## Fitur yang ditambahkan

- Portal standalone `academy.sullamulhifz.or.id`.
- Navigasi: Beranda, Program, Kelas Saya, Modul, Materi, Video, Audio, Artikel, Progres, Rekomendasi Guru, Profil.
- Session sharing lintas subdomain secara otomatis ketika domain separation aktif.
- Empat program contoh: STIFIn, STIFIn Parenting, Al-Qur'an, Pendidikan Anak.
- Katalog audio terhubung ke Quran Learning.
- Manifest PWA khusus Academy.
- API starter `academy-preview` tanpa data pribadi.
- Landing staging ketika `STAGING_ENABLED=true`.
- Seeder v2.2 idempoten/create-only agar konten contoh yang sudah diedit admin tidak ditimpa ulang.

## Catatan runtime

Workspace tidak memiliki folder `vendor/` dan executable Composer, sehingga `php artisan route:list` dan pengujian koneksi database tidak dijalankan lokal. Dockerfile tetap memiliki build smoke check `php artisan package:discover` dan `php artisan route:list`; Coolify akan menghentikan build sebelum rolling update jika Laravel gagal bootstrap.

Tidak ada migration database baru pada v2.2.0. `AcademyExpansionV220Seeder` dijalankan otomatis oleh startup ketika `AUTO_MIGRATE=true`.
