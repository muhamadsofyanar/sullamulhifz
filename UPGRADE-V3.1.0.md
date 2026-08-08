# Upgrade v3.1.0 — Guided Quran Learning

## Tujuan

Menghubungkan Ruang Personal dengan pembelajaran terarah tanpa membuka jurnal Personal kepada pengelola program. Pengguna dapat mendengarkan Al-Qur’an, mengikuti Program Online, mengirim setoran teks/audio, menerima koreksi teks/audio, dan membuka materi Academy yang dikaitkan oleh penyelenggara.

## Deploy

1. Upload seluruh isi paket v3.1.0 ke root repository branch `main`.
2. Pastikan GitHub Actions/build gate hijau.
3. Redeploy Coolify satu kali.
4. Startup menjalankan migration bila `AUTO_MIGRATE=true`.
5. Pastikan `/up` HTTP 200 dan `cat RELEASE` menampilkan `v3.1.0`.
6. Jalankan `php artisan migrate:status | grep guided_quran_learning` dan pastikan `Ran`.
7. Jalankan `php artisan sullam:verify-guided-quran`.

## Smoke test wajib

1. Admin penyelenggara membuka **Program Online**, membuat `Tahfizh Juz 30 Online`, mencentang audio + publik, lalu `Published`.
2. Tambahkan satu asatidz sebagai reviewer.
3. Login akun Personal dan buka **Belajar & Audio**; putar satu surah dari Al-Husary/Al-Minshawi.
4. Ikuti program, lalu kirim setoran audio untuk satu rentang ayat.
5. Login reviewer; buka **Review Setoran Online**, dengarkan audio, pilih `Perlu perbaikan`, kirim feedback teks atau audio.
6. Login Personal; pastikan feedback tampil dan kirim setoran baru.
7. Reviewer ubah setoran baru menjadi `Terverifikasi`.
8. Jika program dikaitkan ke Academy, buka materi Academy dari akun Personal dan pastikan program lain milik penyelenggara tidak ikut terbuka.
9. Gunakan akun Personal kedua; pastikan akun kedua tidak dapat membuka enrollment, setoran, audio, atau feedback akun pertama.
10. Jalankan kembali `php artisan sullam:verify-guided-quran`; masalah ownership/reviewer harus `0`.

Status stabil hanya diberikan setelah seluruh smoke test di atas lulus di produksi.
