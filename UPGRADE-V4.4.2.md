# Upgrade v4.4.2 — Blade Compilation & Release Docs Hotfix

`@phase 4.4.2`

## Sifat upgrade

- Hotfix non-database di atas v4.4.0/v4.4.1.
- Memperbaiki kompilasi Blade pada halaman Admin → WhatsApp & Email.
- Melengkapi dokumen release gate GitHub Actions.
- Tidak menambah migration dan tidak mengubah data produksi.

## Sebelum deploy

1. Salin seluruh isi paket v4.4.2 ke repository GitHub.
2. Commit dan push perubahan.
3. Tunggu `php-tests`, `docker-build`, dan `release-docs` hijau.
4. Jangan redeploy Coolify sebelum semua pemeriksaan hijau.

## Sesudah deploy

1. Buka Admin → WhatsApp & Email.
2. Pastikan daftar variabel template tampil, misalnya `{{recipient_name}}`.
3. Uji halaman lain dan pastikan data lama tetap tersedia.

## Rollback

Rollback kode dapat dilakukan tanpa rollback migration karena v4.4.2 tidak menambah atau mengubah schema database.
