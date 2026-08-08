# Upgrade v2.6.3 — All Marhalah Portion Engine

## Tujuan
Menuntaskan mesin porsi Marhalah dari Āyah sampai Ṣafḥatayn tanpa mengubah prinsip: porsi adalah standar satu sesi hafalan/setoran, bukan kewajiban harian.

## Pola yang dikunci
- Juz 30 — Āyah — 1 ayat atau lebih.
- Juz 29 — Tsalātsiyyah — 3 baris fisik: 1–3, 4–6, 7–9, 10–12, 13–15.
- Juz 28 — Khamsiyyah — 5 baris fisik: 1–5, 6–10, 11–15.
- Juz 27 — Niṣfiyyah — ½ halaman visual: bagian atas slot 1–8 atau bagian bawah slot 9–15.
- Juz 26 — Ṣafḥah — 1 halaman fisik, slot 1–15.
- Juz 1–25 — Ṣafḥatayn — 2 halaman fisik berurutan, 15 + 15 slot.

## Batas Juz
Satu target tidak pernah menyeberang Juz. Jika sebuah pilihan fisik menyentuh batas Juz, Mushaf Page Engine hanya mengambil ayat pada Juz aktif dan menandainya sebagai porsi batas Juz. Ini adalah penutup/pembuka alami Juz, bukan penurunan kapasitas Marhalah.

## Integrasi Tahfizh
Semua porsi membuat `quran_journey_portion` dan target Tahfizh yang terhubung. Untuk porsi dua halaman, target menyimpan halaman awal dan akhir.

## Database
Migration baru menambahkan `memorization_targets.mushaf_end_page_number`.

## Environment
Tidak ada environment variable baru.
