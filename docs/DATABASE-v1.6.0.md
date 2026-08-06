# Database v1.6.0

Migration: `2026_08_06_000300_quran_learning_player_v160.php`

## Tabel baru

### quran_audio_sources
Sumber murattal per lembaga, qari, riwayat, server audio, atribusi, dan status sumber utama.

### quran_ayah_timings
Timing awal/akhir setiap ayat, nomor halaman, URL gambar halaman, dan URL audio surah.

Unique key: sumber + surah + ayat.

### quran_practice_presets
Pilihan latihan siap pakai untuk ayat, rentang, surah, halaman, dan rubu’.

### quran_practice_sessions
Riwayat latihan pengguna, target pengulangan, durasi, dan status selesai/berhenti.

### quran_video_resources
Video terkurasi per lembaga. Status awal dapat berupa draf sebelum diterbitkan.

## Data awal

Migration hanya membuat sumber audio default per lembaga. Timing dan preset diisi secara idempoten oleh `sullam:ensure-quran-audio` atau tombol sinkronisasi admin.

## Integritas

- Tidak ada tabel lama yang dihapus.
- Foreign key menggunakan `cascade`, `restrict`, atau `nullOnDelete` sesuai kepemilikan.
- Sinkronisasi dapat diulang tanpa menduplikasi timing.
