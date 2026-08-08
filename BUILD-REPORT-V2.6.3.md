# Build Report — Sullamul Hifz v2.6.3

## Release
`v2.6.3 — All Marhalah Portion Engine`

## Scope
Menuntaskan porsi Marhalah sampai Ṣafḥatayn:
- Āyah ≥1 ayat;
- Tsalātsiyyah 3 baris;
- Khamsiyyah 5 baris;
- Niṣfiyyah ½ halaman visual;
- Ṣafḥah 1 halaman;
- Ṣafḥatayn 2 halaman.

## Implementasi utama
- `MushafPageService` baru untuk ½/1/2 halaman.
- Niṣfiyyah: slot 1–8 / 9–15.
- Ṣafḥah: slot 1–15.
- Ṣafḥatayn: dua halaman berurutan.
- Porsi batas Juz tidak menyeberangkan target ke Juz berikutnya.
- Target Tahfizh menyimpan halaman awal dan akhir.
- Selector halaman tampil langsung pada Qur’an Journey guru.
- Roadmap Fase 4 mempunyai criterion khusus seluruh Marhalah.

## Pemeriksaan statis
- PHP lint: **225 file lulus**.
- JSON parse: **4 file lulus**.
- Shell syntax: **29 file lulus**.
- JavaScript syntax (`node --check`): **5 file lulus**.
- Scan inline Blade `@php(...)` dengan multi-statement berbahaya: **tidak ditemukan**.
- Route `page-portions` dan handler `storePagePortion` terdeteksi.

## Keterbatasan pengujian lokal
Folder release tidak membawa `vendor/` dan environment ini tidak memiliki Composer, sehingga PHPUnit/Laravel integration test penuh tidak dijalankan secara lokal. Integration gate tetap dilakukan oleh build/runtime Coolify setelah deployment.

## Database
Migration baru:
`2026_08_08_001700_mushaf_page_engine_v263.php`

Menambah:
`memorization_targets.mushaf_end_page_number`

## Deployment
Tidak ada Environment variable baru. Gunakan deployment Coolify biasa dengan `AUTO_MIGRATE=true` seperti versi sebelumnya.
