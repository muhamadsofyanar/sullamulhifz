# Upgrade v2.0.4 — Academy Full Video & Focus Player

Perubahan ini fokus pada pengalaman video Academy.

- Video YouTube Shorts mempertahankan rasio 9:16 sehingga bagian video tidak dipotong.
- Video YouTube biasa mempertahankan rasio 16:9.
- Tombol **Layar penuh** tersedia di atas player.
- Mode fullscreen menggunakan `contain` berbasis rasio asli, bukan `cover`.
- PWA cache dinaikkan ke v204 agar CSS/JS player baru segera dipakai.
- Tidak ada migration atau perubahan database.

## Upgrade

1. Backup tetap disarankan sebelum deploy.
2. Salin patch ke root repository dengan **Replace files in destination**.
3. Commit dan push.
4. Redeploy satu kali di Coolify.
5. Pada PWA lama, tutup-buka aplikasi atau refresh sekali agar service worker v204 aktif.
