# Master Checklist Peluncuran

Gunakan dokumen ini sebagai checklist tunggal. Setiap item membutuhkan bukti berupa URL, screenshot, hasil test, commit, atau nama backup.

## A. Source dan deployment

- [ ] Branch production adalah `main`.
- [ ] Commit SHA production dicatat.
- [ ] `RELEASE` konsisten.
- [ ] `public/release.txt` konsisten.
- [ ] Build selesai tanpa error.
- [ ] Rolling update selesai.
- [ ] Laravel application started.
- [ ] Healthcheck memberi HTTP 200.
- [ ] Tidak ada route penting yang 404/500.

## B. Data inti

- [ ] 88 santri aktif.
- [ ] 88 wali terhubung.
- [ ] 4 guru aktif.
- [ ] 6 kelas utama.
- [ ] 2 kelompok Tahfizh.
- [ ] Keanggotaan Tahfizh A benar.
- [ ] Keanggotaan Tahfizh B benar.
- [ ] Tidak ada duplikasi akun.
- [ ] Tidak ada data pribadi di repository.

## C. Akademik

- [ ] Tahun ajaran aktif benar.
- [ ] Semester aktif benar.
- [ ] Jadwal kelas benar.
- [ ] Delapan rubu’ benar.
- [ ] Marhalah tersedia.
- [ ] Target hafalan dapat dibuat.
- [ ] Setoran memperbarui target sesuai aturan.
- [ ] Riwayat setoran tidak ditimpa.
- [ ] Murāja‘ah tercatat terpisah.
- [ ] Tidak ada ranking.

## D. Operasional harian

- [ ] Pertemuan dapat dibuka.
- [ ] Absensi massal berfungsi.
- [ ] Pertemuan tidak dapat ditutup bila absensi belum lengkap.
- [ ] Tahsīn dapat dicatat.
- [ ] Tahfizh dapat dicatat.
- [ ] Murāja‘ah dapat dicatat.
- [ ] Tugas rumah dapat dibuat.
- [ ] Ringkasan wali dapat dipublikasikan.
- [ ] Buku Penghubung tetap privat.

## E. Latihan Al-Qur’an

- [ ] Al-Husary tersedia.
- [ ] Al-Minshawi tersedia.
- [ ] Timing Juz 30 lengkap atau kekurangannya terdokumentasi.
- [ ] Repeat per ayat berfungsi.
- [ ] Repeat seluruh pilihan berfungsi.
- [ ] Satu surat berfungsi.
- [ ] Satu halaman berfungsi.
- [ ] Satu rubu’ berfungsi.
- [ ] Target santri dapat membuka latihan.
- [ ] Penggunaan ponsel lancar.

## F. Portal wali

- [ ] Satu wali dapat melihat beberapa anak miliknya.
- [ ] Wali tidak dapat melihat anak lain.
- [ ] Ringkasan hari ini tampil.
- [ ] Ringkasan bulanan tampil.
- [ ] Tugas dan bukti berfungsi.
- [ ] Rapor terbit dapat dilihat.
- [ ] File privat terlindungi.

## G. Laporan dan rapor

- [ ] Rapor menggunakan data nyata.
- [ ] Tidak ada ranking.
- [ ] Identitas lembaga benar.
- [ ] Catatan guru tampil benar.
- [ ] Cetak PDF terbaca.
- [ ] Ekspor CSV terbaca.
- [ ] Ringkasan bulanan sesuai data sumber.

## H. Keamanan dan privasi

- [ ] Reset password berfungsi.
- [ ] Logout semua perangkat berfungsi.
- [ ] Riwayat login tersedia.
- [ ] Rate limiting aktif.
- [ ] Security headers aktif.
- [ ] Role access diuji.
- [ ] Secret tidak ada di GitHub.
- [ ] Data anak tidak tampil di halaman publik.
- [ ] Upload media memiliki batas ukuran.
- [ ] Kebijakan retensi media tersedia.

## I. Backup dan pemulihan

- [ ] Backup database otomatis aktif.
- [ ] Backup manual sebelum launch tersedia.
- [ ] Backup storage privat tersedia.
- [ ] Restore diuji di lingkungan non-production.
- [ ] Prosedur rollback tersedia.
- [ ] Penanggung jawab restore ditetapkan.

## J. Peluncuran

- [ ] Pilot selesai.
- [ ] Blocker ditutup.
- [ ] Panduan admin tersedia.
- [ ] Panduan guru tersedia.
- [ ] Panduan wali tersedia.
- [ ] Orientasi guru selesai.
- [ ] Aktivasi wali disiapkan.
- [ ] Kontak dukungan diumumkan.
- [ ] Git tag stabil dibuat.
- [ ] Backup final dibuat.
