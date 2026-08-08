# Build Report — Sullamul Hifz v2.6.4

## Release

`v2.6.4 — Qur’an Journey Stabilization`

## Perbaikan utama

- Root cause HTTP 500 dikoreksi pada `teacher/quran-journey/student.blade.php`.
- Directive kondisional Blade dibuat eksplisit dan tidak lagi menempel pada teks.
- Request GET detail guru tidak melakukan network sync Mushaf.
- Startup container tidak lagi memakai nama versi lama; versi dibaca dari `RELEASE`.
- Docker image diberi label `2.6.4`.
- Post-deployment command Coolify tidak lagi diperlukan.

## Release gates

- GitHub Actions mengompilasi semua Blade dan menjalankan `php -l` pada seluruh compiled view.
- Docker build melakukan gate Blade yang sama sebelum image runtime dibuat.
- Feature test membuka detail Qur’an Journey guru pada Juz 30, 29, 28, 27, 26, dan 1.
- Regression test memastikan GET detail guru tidak memanggil `syncPage()` secara langsung.

## Database

Tidak ada migration v2.6.4. Migration terakhir tetap
`2026_08_08_001700_mushaf_page_engine_v263.php`.

## Dependency reproducibility

Source snapshot yang diterima belum memiliki `composer.lock`. Rilis ini tidak
mengarang lock file tanpa resolver Composer. Dependency lock tetap menjadi
hardening lanjutan, sementara CI dan Docker build menjalankan gate aplikasi.

## Validasi lokal

Environment penyusunan paket tidak menyediakan binary PHP/Composer, sehingga
PHPUnit dan kompilasi Blade aktual tidak dapat dieksekusi lokal. Gate tersebut
dipasang pada GitHub Actions dan Docker build sehingga harus hijau sebelum
redeploy Coolify.
