# Build Report — Sullamul Ḥifẓ v2.1.0

## Status

Paket source telah digabungkan sebagai **v2.1.0 Unified Platform & Secure Media** dan disiapkan untuk pola upload manual ke GitHub lalu satu kali redeploy melalui Coolify.

## Pemeriksaan statis yang lulus

- **170 file PHP** pada `app`, `bootstrap`, `config`, `database`, `routes`, dan `tests`: syntax lint lulus.
- **81 file Blade**: pasangan directive utama lulus pemeriksaan keseimbangan.
- **23 script shell**: `sh -n` lulus.
- `unit.json`, `composer.json`, dan `public/manifest.webmanifest`: JSON valid.
- **161 nama route** dan **153 referensi route statis**: tidak ditemukan nama hilang atau duplikat.
- **13 aset service worker**: seluruhnya tersedia dan hanya aset statis eksplisit yang dicache.
- `git diff --check`: lulus.
- pemeriksaan dokumentasi rilis: lulus.
- pemindaian fungsi PHP berisiko: tidak ditemukan pola eksekusi shell pada source aplikasi.
- pemindaian private key, APP key nyata, dan password fallback lama: tidak ditemukan.
- trusted proxy wildcard: tidak ditemukan.
- upload tanpa allow-list: tidak ditemukan; impor CSV dibatasi ke CSV/TXT.
- media privat memiliki `Cache-Control: private, no-store` dan CSP khusus.

## Perubahan penting yang diperiksa

- migration additive dan seeder idempoten;
- tenant isolation pada website, konten, pertemuan, Academy, tugas, dan Buku Penghubung;
- assignment guru berdasarkan periode aktif;
- akses multi-role guru/wali pada Buku Penghubung;
- permission middleware dan feature flag;
- aktivasi akun dan reset kata sandi;
- pusat media privat dan migrasi media legacy;
- navigasi mobile serta icon system baru;
- pemetaan domain publik, portal, Academy, API, dan staging;
- startup persistent volume dan satu kali redeploy.

## Batas pemeriksaan

Automated Laravel test suite dan migration runtime belum dapat dijalankan di workspace karena source awal tidak menyertakan `vendor/` dan `composer.lock`, serta executable Composer tidak tersedia. Docker build di Coolify akan memasang dependency melalui tahap Composer. Karena itu, log build/migration dan smoke test setelah deploy tetap menjadi pemeriksaan final yang wajib.
