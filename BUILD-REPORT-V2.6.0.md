# Build Report — Sullamul Ḥifẓ v2.6.0

Tanggal build: 8 Agustus 2026  
Release: `v2.6.0`  
Fase: **4 — Qur’an Journey: Marhalah, Milestone, Program Khatam & Warisan Ulama**

## Status release

**Implementation candidate untuk validasi produksi. Fase 4 belum dinyatakan 100%.**

v2.6.0 menambahkan fondasi dan workflow Qur’an Journey tanpa mengubah prinsip Human Before Data. Marhalah mengikuti wilayah Juz, bukan ranking atau label kecerdasan. Program Khatam/Tilawah/Murāja‘ah dipisahkan dari jalur hafalan.

## Implementasi utama

- Marhalah resmi: Juz 30 Āyah (≥1 ayat), Juz 29 Tsalātsiyyah (3 baris), Juz 28 Khamsiyyah (5 baris), Juz 27 Niṣfiyyah (½ halaman), Juz 26 Ṣafḥah (1 halaman), Juz 1–25 Ṣafḥatayn (2 halaman).
- Porsi Marhalah memakai Mushaf Madinah sebagai acuan. Untuk 3/5 baris, guru wajib mengonfirmasi; sistem tidak menebak jumlah baris dari metadata ayat.
- Satu porsi boleh melewati pergantian Surah selama masih dalam Juz yang sama; sistem membentuk beberapa target anak tetapi mempertahankan satu parent porsi.
- Milestone hafalan terpisah dari pemeriksaan penjagaan/retention.
- Fondasi 5 Juz (26–30), Manzil Qaf, dan 30 Juz sebagai aggregate milestone.
- Khatam 30 Hari: 30 langkah, 1 Juz per langkah.
- Fami Bisyauqin: 7 manzil Fa, Mim, Ya, Ba, Syin, Wau, Qaf.
- Tujuan program: Tilawah, Murāja‘ah, atau keduanya; ritme dapat harian atau fleksibel dan tidak memiliki status “gagal” karena hari terlewat.
- Peta Mushaf: 30 Juz, 60 Ḥizb, 240 Rubu‘ al-Ḥizb, 7 Manzil Fami, serta Rukū‘ berdasarkan metadata korpus.
- Peta Warisan Ulama: Āyah, halaman, Juz, Ḥizb, Rubu‘ al-Ḥizb, Manzil, Rukū‘, Waqaf, Sajdah, Makki/Madani.
- Delapan `quran_rubus` lama dilabel ulang di UI sebagai **Segment Juz 30 (legacy)** dan tidak lagi dipresentasikan sebagai Rubu‘ al-Ḥizb.
- Workspace guru, ringkasan wali, program personal di app, serta **Program Qur’an native di Academy**.
- Startup Coolify menjalankan migration, seeder v2.6.0, verifikasi Qur’an Journey, lalu sinkronisasi division map di background setelah korpus tersedia.

## Static QA final

- PHP non-Blade: **221 file** — `php -l` lulus.
- Blade: **102 file** — ikut `php -l` sebagai PHP-family syntax; scan regresi inline `@php(...;...)`: **0 temuan**.
- Shell/startup scripts: **28 file** — `sh -n` lulus.
- Public JavaScript: **5 file** — `node --check` lulus.
- JSON: **4 file** — parse lulus.
- `RELEASE` dan `public/release.txt`: `v2.6.0`.
- Migration v2.6.0 bersifat additive terhadap data operasional dan memiliki alur `down()` untuk struktur baru.
- Seeder v2.6.0 bersifat idempoten untuk master/feature/check yang dibuat dan tidak menghapus histori operasional.

## Batas validasi lokal

Source package tidak membawa `vendor/` dan repository belum mempunyai `composer.lock`. Karena itu **full Laravel boot, route compilation, migration ke MySQL nyata, dan PHPUnit tidak dijalankan di workspace build ini**. Coolify/runtime production tetap menjadi integration test utama seperti fase sebelumnya.

## Production gates Fase 4

Fase 4 baru boleh 100% setelah minimal alur berikut dibuktikan di deployment:

1. Guru menginisialisasi posisi Juz dan Marhalah yang benar.
2. Porsi Marhalah dibuat dan dapat melintasi pergantian Surah dalam Juz yang sama.
3. Penyelesaian Juz membuka Juz berikut sesuai urutan 30→29→28→27→26→1→…→25.
4. Milestone hafalan dan status penjagaan tetap terpisah dan histori pemeriksaan tersimpan.
5. Khatam 30 Hari berjalan 30 langkah tanpa label gagal.
6. Fami Bisyauqin berjalan 7 manzil Fa→Mim→Ya→Ba→Syin→Wau→Qaf.
7. Peta Mushaf menunjukkan 30 Juz, 60 Ḥizb, 240 Rubu‘ al-Ḥizb, dan 7 Manzil Fami.
8. Wali melihat Qur’an Journey anak secara read-only tanpa data anak lain.
9. Academy menjalankan Program Qur’an tanpa berpindah ke domain operasional.
10. Workflow mobile nyaman dan checklist Fase 4 ditandai lulus hanya setelah pengujian nyata.

## Catatan teknis yang belum ditutup

`composer.lock` tetap merupakan technical debt Platform Core/Fase 1. v2.6.0 tidak mengklaim telah menyelesaikannya.
