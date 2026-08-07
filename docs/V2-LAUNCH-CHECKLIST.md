# Checklist Peluncuran v2.0.0

## Deployment
- [ ] Backup database berhasil.
- [ ] `RELEASE` = `v2.0.0`.
- [ ] Migration selesai tanpa error.
- [ ] `sullam:verify-academy` berhasil.
- [ ] NGINX Unit menampilkan `laravel application started`.

## Mobile/PWA
- [ ] Tidak ada strip sidebar hijau saat menu tertutup.
- [ ] Tidak ada horizontal scroll pada 360, 390, 430, dan 768 px.
- [ ] Bottom navigation dapat ditekan dengan satu tangan.
- [ ] Tombol rutin minimal nyaman disentuh.
- [ ] Instal PWA berhasil.
- [ ] Halaman offline dasar tampil.

## Quran Player
- [ ] Al-Husary dapat diputar.
- [ ] Al-Minshawi dapat diputar.
- [ ] Satu ayat 10× berjalan.
- [ ] Rentang ayat berjalan per ayat.
- [ ] Satu surah berjalan.
- [ ] Halaman dan rubu’ berjalan.
- [ ] Pause/lanjut/sebelumnya/berikutnya/stop bekerja.
- [ ] Target guru dapat dibuka satu sentuhan.

## Academy
- [ ] Parent Academy muncul untuk wali.
- [ ] Teacher Academy muncul untuk guru.
- [ ] Admin dapat membuat program/modul/materi.
- [ ] Guru hanya dapat merekomendasikan materi kepada santri yang diampu.
- [ ] Wali hanya melihat rekomendasi anak miliknya.
- [ ] Progress materi tersimpan.
- [ ] Catatan pribadi anak tetap di Buku Penghubung, bukan community.

## Keamanan & data
- [ ] Tidak ada credential di GitHub.
- [ ] Hak akses diuji per peran.
- [ ] Backup restore diuji pada lingkungan non-production.
- [ ] Data capaian santri tidak diisi fiktif.

## Keputusan launch
Semua butir kritis harus lolos. Temuan minor UI boleh masuk v2.0.1; temuan hak akses, kehilangan data, 5xx, atau audio inti gagal berarti **NO-GO**.
