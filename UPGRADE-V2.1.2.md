# Upgrade v2.1.2 — Coolify Composer Network Resilience

Hotfix deployment untuk Coolify/BuildKit.

Perubahan:
- Composer pada Docker build dipaksa menggunakan IPv4 melalui `COMPOSER_IPRESOLVE=4`.
- `composer install` memiliki hingga 4 percobaan dengan incremental delay.
- Paralel HTTP Composer dibatasi agar koneksi build lebih stabil.
- Tidak ada perubahan skema database pada hotfix ini.

Alasan: host VPS dapat mengakses `repo.packagist.org`, sementara BuildKit sebelumnya gagal `curl error 7` saat Composer mengunduh metadata.
