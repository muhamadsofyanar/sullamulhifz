# Database v1.5.0

Migration: `2026_08_06_000200_academic_core_v150.php`

## Perubahan additive

### Kolom baru `academic_years`

- `code`
- `active_semester`
- `enrollment_status`

### Tabel `quran_rubus`

Master delapan rubu' Juz 30. Data di-upsert secara idempotent.

### Tabel `memorization_targets`

Target individual yang terhubung ke:

- lembaga;
- tahun ajaran;
- santri;
- kelompok belajar bila ada;
- guru pemberi target bila ada;
- rubu';
- surat dan rentang ayat;
- marhalah;
- status dan tanggal.

### Tabel `learning_observations`

Catatan observasi guru mengenai metode dan respons belajar. Tabel ini bukan hasil diagnosis dan bukan sumber ranking.

## Data lama

Migration tidak menghapus atau mengganti:

- santri;
- wali;
- guru;
- kelas;
- kelompok;
- setoran;
- murāja'ah;
- tahsīn;
- rapor;
- akun.
