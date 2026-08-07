# Upgrade v2.0.2

1. Backup source/repository aktif.
2. Timpa file patch ke root repository dan pilih **Replace files in destination**.
3. Commit dan push ke `main`.
4. Redeploy Coolify satu kali.
5. Setelah container aktif, jalankan `php artisan optimize:clear` lalu `php artisan view:cache` bila startup belum melakukannya.
6. Uji `/admin/academy`.

Tidak ada migration baru. Jangan menjalankan `migrate:fresh` atau `db:wipe`.
