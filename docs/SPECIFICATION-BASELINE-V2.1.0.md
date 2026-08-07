# Specification Baseline — Sullamul Ḥifẓ v2.1.0

Dokumen ini mengunci hasil penggabungan Brand Strategy, PRD, Sitemap/User Flow, Wireframe, ERD, source v2.0.4, dan Icon System terbaru.

## Urutan sumber keputusan

1. Brand Strategy: filosofi, KUAT, karakter merek, dan budaya tanpa ranking.
2. PRD: batas MVP, kebutuhan pengguna, keamanan, privasi, dan indikator keberhasilan.
3. Sitemap/User Flow: menu, hak akses, dan alur tugas utama.
4. Wireframe: struktur mobile-first dan prioritas tindakan.
5. ERD: riwayat data, relasi, tenant, dan privasi media.
6. Source aplikasi: implementasi teknis aktif.
7. Icon System: bahasa visual antarmuka.

## Keputusan produk yang dikunci

- Pembinaan nyata tetap menjadi pusat; aplikasi menjaga jejak, komunikasi, dan kesinambungan.
- Tidak ada ranking hafalan, kelas terbaik, atau perbandingan terbuka antarsantri.
- Pertemuan menjadi induk absensi, tahsīn, tahfizh, dan murāja‘ah.
- Tahsīn, hafalan baru, dan murāja‘ah disimpan terpisah.
- Catatan individual bersifat selektif, bukan kewajiban untuk seluruh santri setiap hari.
- Kelas utama dan kelompok belajar dipisahkan.
- Perpindahan kelas dan pergantian guru tidak menghapus riwayat.
- Satu wali dapat mendampingi beberapa anak dan satu anak dapat memiliki beberapa wali.
- Tugas dapat ditujukan ke kelas, kelompok, beberapa santri, atau individu.
- Bukti tugas dan Buku Penghubung bersifat privat.
- STIFIn hanya informasi tambahan, bukan penentu nilai manusia, kelas, atau marhalah.
- Data penting diarsipkan dan diaudit, bukan mudah dihapus permanen.

## Navigasi mobile utama

### Guru

`Beranda · Kelas · Tugas · Pesan · Lainnya`

### Orang tua/wali

`Beranda · Anak · Tugas · Pesan · Lainnya`

### Admin

`Beranda · Akademik · Pengguna · Laporan · Lainnya`

Audio Qur’an, Academy/LMS, website, rapor, dan modul pengembangan tersedia melalui akses cepat atau menu Lainnya agar alur inti tidak tenggelam.

## Icon System

Referensi visual disimpan pada:

`docs/assets/icon-system-reference-v2.1.0.png`

Pemetaan utama:

- Home → Beranda
- Student → Santri/Anak
- Teacher → Guru
- Community → Komunitas/Pengumuman
- Guidance → Pembinaan
- Growth → Perkembangan
- Preservation → Penjagaan hafalan
- Continuity → Murāja‘ah
- Focus → Target
- Progress → Laporan perkembangan
- Achievement → Rapor/pencapaian
- Values → Ikrar/adab/nilai
- Schedule → Jadwal
- Plan → Rencana belajar
- Lesson → Materi/Academy
- Listen → Audio dan latihan Al-Qur’an
- Discussion → Buku Penghubung
- Profile → Profil

Bahasa visual menggunakan bentuk solid-organic, emerald, emas, ivory, sudut lembut, dan status yang selalu memiliki teks/ikon—bukan warna saja.

## Pengembangan spontan yang diperbolehkan

Fitur baru boleh dikembangkan ketika:

1. mendukung pembinaan, komunikasi, keputusan, atau kesinambungan;
2. tidak menambah budaya ranking;
3. tidak membuka data anak;
4. dapat dikendalikan melalui feature flag;
5. tidak memaksa seluruh pengguna memakai fitur yang belum siap;
6. memiliki contoh data, status kosong, validasi, permission, dan jalur deaktivasi.

Contoh pengembangan bernilai yang dipertahankan pada v2.1.0: Audio Qur’an, Parent Academy/LMS, rapor perkembangan, website publik, artikel, dan pendaftaran santri.

## Prinsip teknis untuk versi berikutnya

- Semua tabel utama tenant-aware.
- Semua file baru masuk pusat media.
- Semua fitur baru mendapat permission dan feature flag.
- Semua perubahan struktur memakai migration additive.
- Seeder harus idempoten dan tidak menimpa data operasional.
- Navigasi utama tetap ringkas.
- Setiap fitur memiliki audit, retention, atau fallback yang sesuai risikonya.
