# Database v1.6.1 — Qari Tahfizh

## Migration

`2026_08_06_000310_qari_tahfizh_v161.php`

## Dampak

Migration tidak membuat ulang tabel dan tidak menghapus data operasional. Migration:

1. menambahkan atau memperbarui sumber Al-Husary;
2. menambahkan atau memperbarui sumber Al-Minshawi;
3. menjadikan Al-Husary sebagai default;
4. menonaktifkan sumber Al-Ajmi tanpa menghapus timing lama;
5. memindahkan referensi preset lama dari Al-Ajmi ke Al-Husary.

Timing ayat dua qari diisi secara idempoten oleh `sullam:ensure-quran-audio` setelah aplikasi aktif.

## Target runtime

- Al-Husary: 564 timing Juz 30;
- Al-Minshawi: 564 timing Juz 30;
- total: 1.128 timing.

## Data yang tidak diubah

- santri dan wali;
- guru;
- kelas dan kelompok;
- target hafalan;
- observasi belajar;
- absensi;
- setoran dan murāja‘ah;
- rapor dan komunikasi.
