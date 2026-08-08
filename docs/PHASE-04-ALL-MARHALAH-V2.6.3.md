# Fase 4 — All Marhalah Portion Engine v2.6.3

## Prinsip
Marhalah adalah standar porsi hafalan baru/setoran berdasarkan perjalanan Juz, bukan ranking atau ukuran kecerdasan. Frekuensi dapat harian, mingguan, atau fleksibel.

## Peta final porsi
| Juz | Marhalah | Porsi | Mesin |
|---|---|---|---|
| 30 | Āyah | ≥1 ayat | ayat/rentang |
| 29 | Tsalātsiyyah | 3 baris | Mushaf Line Engine |
| 28 | Khamsiyyah | 5 baris | Mushaf Line Engine |
| 27 | Niṣfiyyah | ½ halaman | Mushaf Page Engine |
| 26 | Ṣafḥah | 1 halaman | Mushaf Page Engine |
| 1–25 | Ṣafḥatayn | 2 halaman | Mushaf Page Engine |

## Niṣfiyyah
Karena Mushaf Madinah memakai 15 slot fisik, setengah halaman dipresentasikan sebagai dua bagian visual tetap:
- bagian atas: slot 1–8;
- bagian bawah: slot 9–15.

Ini adalah pembagian visual operasional. Nama surah dan basmalah tetap menempati slot fisiknya sebagaimana layout Mushaf.

## Ṣafḥah
Satu halaman fisik penuh: slot 1–15 pada satu halaman.

## Ṣafḥatayn
Dua halaman fisik berurutan: halaman N dan N+1. Target Tahfizh menyimpan halaman awal/akhir serta batas ayat/kata bila tersedia.

## Batas Juz
Porsi tidak boleh menggabungkan dua Juz. Bila ruang fisik yang dipilih menyentuh batas Juz, sistem memotong target pada batas ayat Juz dan memberi tanda `porsi batas Juz`. Hal ini menjaga struktur perjalanan Juz tanpa memalsukan posisi baris/kata.

## Koneksi Fase 3
Setiap porsi tetap membuat target Tahfizh. Guru kemudian dapat menjalankan talaqqi, setoran, fokus koreksi, jadwal penjagaan, dan Murāja‘ah dari Perjalanan Tahfizh.

## Gate 100%
Implementasi source bukan validasi 100%. Fase 4 baru ditutup setelah alur Marhalah, milestone/retention, Khatam 30 Hari, Fami Bisyauqin, warisan ulama, wali/Academy, dan mobile lulus di produksi.
