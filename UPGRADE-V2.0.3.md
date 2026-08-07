# Upgrade v2.0.3 — Academy Experience & Video

## Tujuan

- Memperbaiki error 500 pada halaman Kelola Academy dengan view admin yang lebih sederhana dan aman.
- Merapikan Academy desktop dan mobile.
- Menghilangkan horizontal scroll pada sidebar.
- Memperjelas header program Academy pada layar kecil.
- Menampilkan video YouTube/YouTube Shorts langsung di materi Academy.
- Menambahkan satu video contoh dari URL yang diberikan pengelola.
- Menambah editor materi Academy agar judul, jenis, isi, URL media, durasi, status, dan tindak lanjut dapat diperbarui.

## Instalasi

1. Backup database.
2. Salin isi upgrade patch ke root repository dan pilih Replace files.
3. Pastikan `RELEASE` berisi `v2.0.3`.
4. Commit dan push ke branch production.
5. Redeploy satu kali di Coolify.
6. Jalankan `sh scripts/smoke-test-v2.0.3.sh`.

Seeder v2.0.3 idempotent. Tidak ada `db:wipe` atau `migrate:fresh`.
