# Upgrade ke v4.4.4

`@phase 4.4.4`

Hotfix ini hanya memperbaiki konfigurasi PHPUnit untuk GitHub Actions.

## Langkah

1. Salin isi paket ke repository dan push ke GitHub.
2. Tunggu seluruh GitHub Actions hijau.
3. Redeploy melalui Coolify hanya jika seluruh pemeriksaan lulus.

Tidak ada perubahan `.env`, `APP_KEY`, database, atau persistent storage.
