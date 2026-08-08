# Fase 4 — Mushaf Line Engine v2.6.1

## Mengapa dibuat

Marhalah Sullamul Ḥifẓ tidak boleh mengubah **3 baris** atau **5 baris** menjadi perkiraan jumlah ayat. Pada Mushaf Madinah, satu baris dapat memuat bagian dari sebuah ayat, beberapa potongan ayat, atau sebuah slot khusus seperti nama surah/basmalah. Karena itu batas porsi disimpan pada level **halaman → slot baris → lokasi kata**.

## Aturan yang dikunci

- Juz 30 / Āyah: minimal 1 ayat atau lebih.
- Juz 29 / Tsalātsiyyah: 3 slot fisik Mushaf per porsi.
- Juz 28 / Khamsiyyah: 5 slot fisik Mushaf per porsi.
- Juz 27 / Niṣfiyyah: ½ halaman.
- Juz 26 / Ṣafḥah: 1 halaman.
- Juz 1–25 / Ṣafḥatayn: 2 halaman.

Untuk 3 baris: `1–3`, `4–6`, `7–9`, `10–12`, `13–15`.
Untuk 5 baris: `1–5`, `6–10`, `11–15`.

## Guardrail

1. Marhalah tetap ditentukan otomatis oleh Juz.
2. Porsi tidak menjadi kewajiban harian; jadwal tetap fleksibel.
3. Blok yang menyentuh batas Juz tidak dibuat otomatis.
4. Header surah/basmalah tidak dibuang dari posisi fisik layout.
5. Target Tahfizh menyimpan batas kata walaupun UI Tahfizh masih menggunakan ayat untuk navigasi.
6. `Selesai hafalan` dan `Terjaga` tetap dua status berbeda.
7. Fase 4 belum selesai sampai 604 halaman tersinkron, coverage Juz 29/28 lengkap, dan alur produksi divalidasi.

## Kriteria closure bagian Mushaf Line

- tabel `quran_mushaf_lines` tersedia;
- 604 halaman layout tersinkron;
- seluruh halaman Juz 29 dan Juz 28 yang dipakai Marhalah memiliki 15 slot line terpetakan;
- blok 3/5 baris tampil sesuai slot fisik;
- halaman dengan nama surah/basmalah telah diuji;
- batas kata tersimpan pada porsi dan target Tahfizh;
- satu target berhasil dipakai untuk siklus → setoran → Murāja‘ah tanpa kehilangan batas Mushaf;
- mobile dan desktop lulus.
