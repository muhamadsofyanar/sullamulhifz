# Fase 4 — Qur’an Journey v2.6.0

## Status

**Tahap:** implementasi kandidat → validasi produksi setelah deploy.

Fase 4 tidak boleh dinyatakan 100% hanya karena tabel, menu, atau service sudah tersedia. Nilai 100% memerlukan implementasi lengkap dan seluruh launch check Fase 4 dinyatakan lulus dari pengujian nyata.

## 1. Konsep Marhalah yang dikunci

Marhalah Sullamul Ḥifẓ **bukan level kecerdasan, ranking, atau label kemampuan anak**. Marhalah adalah standar porsi minimal **hafalan baru/setoran per sesi** yang mengikuti Juz yang sedang ditempuh.

| Wilayah perjalanan | Marhalah | Porsi satu sesi |
|---|---|---|
| Juz 30 | Āyah | 1 ayat atau lebih |
| Juz 29 | Tsalātsiyyah | 3 baris Mushaf Madinah |
| Juz 28 | Khamsiyyah | 5 baris Mushaf Madinah |
| Juz 27 | Niṣfiyyah | ½ halaman |
| Juz 26 | Ṣafḥah | 1 halaman |
| Juz 1–25 | Ṣafḥatayn | 2 halaman |

Urutan perjalanan default: **30 → 29 → 28 → 27 → 26 → 1 → 2 → … → 25**.

Frekuensi dapat harian, mingguan, beberapa hari sekali, atau fleksibel. Porsi tidak berarti kewajiban setor setiap hari.

Perpindahan Marhalah tidak dilakukan bebas. Guru menandai Juz saat ini selesai hafalan, lalu sistem membuka Juz berikutnya dan menentukan Marhalah otomatis.

## 2. Porsi lintas surah

Satu porsi ½, 1, atau 2 halaman dapat melewati batas surah. v2.6.0 memiliki `quran_journey_portions` sebagai unit induk. Guru menentukan awal dan akhir porsi; sistem boleh memecahnya menjadi beberapa `memorization_targets` per surah tanpa menghilangkan makna bahwa semuanya adalah **satu porsi Marhalah**.

Porsi tidak boleh melewati batas Juz karena Marhalah ditentukan oleh Juz aktif.

Untuk Tsalātsiyyah dan Khamsiyyah, posisi baris **tidak ditebak otomatis** dari metadata ayat. Guru wajib mengonfirmasi kesesuaian 3/5 baris pada Mushaf Madinah.

## 3. Tahap Fondasi 5 Juz

Juz 30 → 29 → 28 → 27 → 26 adalah **Tahap Fondasi — 5 Juz Awal Program**.

Tahap ini berfokus pada bagian akhir Al-Qur’an. Sistem tidak menyatakan Juz 26–30 identik seluruhnya dengan al-Mufaṣṣal. Di dalam tahap ini terdapat wilayah **Qāf sampai An-Nās**, yaitu manzil Qaf dalam pola Fami Bisyauqin.

Ketika Juz 26–30 selesai hafalan:
- milestone **Fondasi 5 Juz** tercapai;
- milestone hafalan **Manzil Qaf — Qāf sampai An-Nās** tercapai;
- status penjagaan Manzil Qaf tetap dinilai terpisah, tidak otomatis “terjaga”.

## 4. Milestone

Milestone bukan badge kompetisi. Ia adalah penanda perjalanan nyata.

Status hafalan dan penjagaan dipisah:
- Hafalan: belum dimulai → berjalan → selesai hafalan.
- Penjagaan: belum dinilai → penguatan → terjaga → perlu dipanggil kembali.

Unit milestone dapat berupa Surah, Rubu‘ al-Ḥizb, Ḥizb, Juz, Manzil Fami, Fondasi 5 Juz, dan 30 Juz.

Pemeriksaan penjagaan disimpan sebagai histori sehingga keputusan terbaru tidak menghapus bukti sebelumnya.

## 5. Program Qur’an

### Khatam Al-Qur’an 30 Hari
30 langkah; satu Juz per langkah. Dapat digunakan untuk:
- Tilawah;
- Murāja‘ah;
- Tilawah + Murāja‘ah.

Mode jadwal dapat mengikuti hari program atau fleksibel. Hari terlewat tidak diberi status “gagal”.

### Fami Bisyauqin — 7 Manzil
1. Fa — Al-Fātiḥah sampai An-Nisā’
2. Mim — Al-Mā’idah sampai At-Taubah
3. Ya — Yūnus sampai An-Naḥl
4. Ba — Al-Isrā’ sampai Al-Furqān
5. Syin — Asy-Syu‘arā’ sampai Yāsīn
6. Wau — Aṣ-Ṣāffāt sampai Al-Ḥujurāt
7. Qaf — Qāf sampai An-Nās

Program memakai kelompok surah/manzil, bukan membagi jumlah halaman menjadi tujuh bagian sama rata.

## 6. Peta Mushaf & Warisan Ulama

v2.6.0 memperkenalkan dan menggunakan:
- Āyah;
- halaman Mushaf Madinah;
- Juz;
- Ḥizb;
- Rubu‘ al-Ḥizb;
- Manzil;
- Rukū‘;
- Waqaf;
- Ayat Sajdah;
- Makkiyah/Madaniyah.

Penjelasan di aplikasi menegaskan bahwa penanda/pembagian tersebut membantu interaksi, pembelajaran, tilawah, dan penjagaan; **bukan tambahan pada teks wahyu Al-Qur’an**.

### Pelurusan istilah Rubu‘
Data `quran_rubus` versi awal berisi delapan segment internal Juz 30. Mulai v2.6.0 UI menyebutnya **Segment Juz 30 (legacy)**, bukan Rubu‘. Rubu‘ yang digunakan dalam Qur’an Journey adalah **Rubu‘ al-Ḥizb standar 1–240** yang dibangun dari metadata korpus Full Qur’an.

## 7. Area pengguna

### Guru
`/teacher/quran-journey`
- melihat posisi Juz/Marhalah santri;
- inisialisasi posisi awal sekali;
- membuat porsi Marhalah, termasuk porsi yang melintasi surah;
- menandai milestone hafalan;
- mencatat pemeriksaan penjagaan;
- membuka tahap Juz berikutnya;
- menugaskan Khatam 30 Hari / Fami Bisyauqin.

### Pengguna pribadi
`/perjalanan-quran`
- memulai program Khatam 30 Hari atau Fami Bisyauqin;
- memilih tujuan tilawah / Murāja‘ah / keduanya;
- memperbarui progres;
- mempelajari Peta Mushaf & Warisan Ulama.

### Academy
`https://academy.sullamulhifz.or.id/quran-journey`
- menjalankan Program Qur’an tanpa pindah ke domain aplikasi operasional;
- membuka Mushaf & Audio Academy dari halaman yang sama;
- melihat Khatam 30 Hari, Fami Bisyauqin, progres, dan Peta Mushaf.

### Wali
Perkembangan Anak menampilkan posisi Juz, Marhalah, porsi, milestone, status penjagaan, dan program Qur’an anak secara read-only.

## 8. Kriteria Fase 4 = 100%

Implementasi harus lulus:
- 6 Marhalah terikat Juz sesuai metode;
- porsi Marhalah lintas surah terstruktur;
- 30 Juz, 60 Ḥizb, 240 Rubu‘ al-Ḥizb;
- Khatam 30 Hari memiliki 30 langkah;
- Fami Bisyauqin memiliki 7 manzil;
- Peta Mushaf & Warisan Ulama tersedia;
- milestone dan histori penjagaan tersedia.

Validasi produksi harus membuktikan:
1. alur Juz → Marhalah → porsi → setoran → milestone → lanjut Juz;
2. selesai hafalan tidak otomatis menjadi terjaga;
3. porsi yang melewati surah tetap satu unit perjalanan;
4. Khatam 30 Hari berjalan dari Juz 1 sampai 30;
5. Fami berjalan Fa → Mim → Ya → Ba → Syin → Wau → Qaf;
6. istilah warisan mushaf tampil dan dipahami tanpa mencampurkannya dengan teks wahyu;
7. wali hanya melihat data anaknya;
8. Program Qur’an berjalan native di Academy tanpa lompat domain;
9. workflow nyaman di mobile.

Sebelum semua validasi tersebut lulus, Fase 4 tetap berstatus **validasi**, bukan selesai.
