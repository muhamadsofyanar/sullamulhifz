# Upgrade v2.4.1 — Phase 2 Audio Closure Hotfix

## Masalah yang diperbaiki
MP3Quran timing untuk sebagian read dapat kehilangan hanya penanda ayat terakhir. Pada Al-Husary Al-Fatihah, data timing yang diterima tidak memenuhi 7/7 sehingga v2.4.0 menolak seluruh surah dan statistik berhenti di 6.229/6.236.

## Perbaikan
- timing valid dalam surah tidak lagi dibuang hanya karena penanda final hilang;
- fallback hanya berlaku bila tepat ayat terakhir yang hilang dan seluruh ayat sebelumnya berurutan;
- ayat terakhir dimulai dari akhir timing ayat sebelumnya dan berhenti pada event akhir file audio;
- player menampilkan pesan eksplisit jika file audio gagal dimuat/diputar.

## Setelah deploy
Sinkronisasi background harus mengisi ulang Al-Fatihah dan target statistik Al-Husary menjadi 6.236/6.236. Jika belum, jalankan sinkronisasi Quran Audio dari panel admin.
