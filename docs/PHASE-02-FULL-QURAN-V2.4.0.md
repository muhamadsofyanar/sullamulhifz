# Fase 2 — Full Qur’an Engine

Versi implementasi: **v2.4.0**

## Tujuan fase
Menaikkan Quran Learning dari fondasi Juz 30 menjadi mesin Al-Qur’an lengkap yang dapat dipakai Academy dan operasional TPA tanpa kehilangan fokus Sullamul Ḥifẓ pada talaqqi, murāja‘ah, dan penjagaan hafalan.

## Yang dibangun pada v2.4.0
- korpus 114 surah / 6.236 ayat Uthmani;
- metadata 30 juz, 604 halaman Mushaf Madinah, dan 240 Rubu‘ al-Hizb;
- sinkronisasi korpus idempoten melalui AlQuran.cloud;
- Al-Husary sebagai qari utama dan Al-Minshawi sebagai pilihan murāja‘ah untuk seluruh 114 surah;
- sinkronisasi audio resume-safe: hanya surah yang belum lengkap yang diulang;
- player Academy tetap di `academy.sullamulhifz.or.id`;
- mushaf teks Arab berada bersama audio dan ayat aktif di-highlight;
- pemilihan berdasarkan ayat, rentang, surah, juz, halaman, Rubu‘ al-Hizb, dan milestone Juz 30 Sullam;
- repeat 1/3/5/10/20/tanpa batas, per ayat atau seluruh pilihan;
- bookmark ayat dan preset;
- riwayat latihan dan posisi terakhir dibaca;
- target hafalan admin/guru dapat memilih seluruh 114 surah;
- dashboard roadmap menghitung implementasi dan validasi secara terpisah.

## Yang belum boleh dianggap selesai hanya dari source code
Setelah deploy, data eksternal perlu benar-benar tersinkron dan pengalaman pengguna perlu diuji. Karena itu Fase 2 tidak boleh disebut 100% sebelum seluruh release gate di bawah lulus.

## Kriteria Fase 2 = 100%
### Otomatis / data
1. `quran_ayahs` dan `quran_reading_progress` tersedia.
2. 114/114 surah tersedia.
3. 6.236/6.236 ayat tersedia.
4. 30/30 juz terdeteksi.
5. 604/604 halaman terdeteksi.
6. 240/240 Rubu‘ al-Hizb terdeteksi.
7. Al-Husary 6.236/6.236 timing.
8. Al-Minshawi 6.236/6.236 timing.
9. Mushaf + player Academy tersedia.

### Validasi produksi
1. Mushaf/player diuji pada desktop dan ponsel.
2. Dua qari diuji pada bagian awal, tengah, dan akhir Al-Qur’an.
3. Navigasi juz/surah/halaman/rubu‘ dan repeat diuji.
4. Bookmark, riwayat, dan “lanjut terakhir dibaca” diuji.

Fase 2 hanya tampil **100%** ketika implementasi dan validasi produksi di atas telah selesai.

## Sumber data
Teks/metadata Uthmani disinkron melalui AlQuran.cloud. Audio/timing berasal dari MP3Quran.net. Sullamul Ḥifẓ tidak menggantikan talaqqi atau koreksi guru dengan audio digital.
