# Upgrade v4.4.3 — Blade Directive Structure Hotfix

`@phase 4.4.3`

## Sifat upgrade

- Hotfix non-database di atas v4.4.2.
- Memperbaiki kompilasi Blade pada halaman Admin → WhatsApp & Email.
- Tidak menambah migration dan tidak mengubah data produksi.

## Sebelum deploy

1. Salin seluruh isi paket v4.4.3 ke repository GitHub.
2. Commit dan push perubahan.
3. Tunggu `php-tests`, `docker-build`, dan `release-docs` hijau.
4. Jangan redeploy Coolify sebelum semua pemeriksaan hijau.

## Sesudah deploy

1. Buka Admin → WhatsApp & Email.
2. Pastikan daftar variabel template tampil, misalnya `{{recipient_name}}`.
3. Pastikan filter riwayat dan tombol Kirim ulang dapat ditampilkan.

## Rollback

Rollback kode dapat dilakukan tanpa rollback migration karena v4.4.3 tidak menambah atau mengubah schema database.
