# Build Report — Sullamul Hifz v2.6.1

## Scope

Mushaf Line Engine untuk Fase 4 Qur’an Journey: layout halaman/baris, selector blok Tsalātsiyyah/Khamsiyyah, batas kata, integrasi target Tahfizh, verifier/startup, dan roadmap gate.

## Pemeriksaan statis

- PHP lint: 220 file, 0 gagal.
- Shell syntax: 29 file, 0 gagal.
- JavaScript syntax: 5 file, 0 gagal.
- JSON parse: 4 file, 0 gagal.
- Scan regresi inline Blade `@php(...;)`: tidak ditemukan.
- Balance directive pada dua Blade yang diubah diperiksa.

## Keterbatasan build lokal

`vendor/` dan `composer.lock` tidak tersedia pada source workspace, sehingga `php artisan route:list` dan PHPUnit penuh tidak dijalankan lokal. Docker build Coolify tetap memiliki fail-fast `package:discover` + `route:list`, sehingga bootstrap/route regression akan menghentikan image build sebelum rolling update.

## Production gates

Deploy sukses belum menutup Fase 4. `sullam:ensure-mushaf-lines` menargetkan 604 halaman serta coverage 15 slot untuk Juz 29 dan 28. Roadmap menambahkan launch check `phase4_mushaf_line_blocks`.
