# Fase 3 — Tahfizh Learning Engine

Versi implementasi: **v2.5.0**  
Status: **pengembangan selesai pada level source, menunggu validasi produksi**.

## Tujuan fase

Fase 3 mengubah pencatatan Tahfizh dari sekumpulan form menjadi perjalanan belajar yang tersambung:

`Target → Persiapan → Talaqqi/Latihan → Setoran → Penguatan → Muraja‘ah → Tindak lanjut`

Prinsipnya tetap mengikuti Sullamul Ḥifẓ: tidak ada ranking, tidak ada skor total anak, dan sistem tidak menggantikan keputusan guru.

## Yang dibangun pada v2.5.0

1. **Tahfizh Learning Cycle**
   - menghubungkan target dengan persiapan, setoran, penguatan dan penyelesaian;
   - metode persiapan dapat berupa talaqqi, audio berulang, membaca berulang, menulis, menyusun kata, gerak, teach-back, campuran, atau metode khusus;
   - guru dapat menulis arahan untuk dirinya sendiri dan arahan singkat untuk keluarga.

2. **Jadwal penjagaan / Murāja‘ah**
   - guru menentukan tanggal, rentang ayat, jenis dan prioritas;
   - tidak ada interval otomatis wajib;
   - setoran atau Murāja‘ah dapat membuat jadwal tindak lanjut berikutnya;
   - jadwal yang dikerjakan dihubungkan dengan catatan Murāja‘ah aktual.

3. **Fokus koreksi terstruktur**
   - makhraj;
   - tajwid;
   - panjang-pendek;
   - ghunnah;
   - waqaf/ibtida;
   - kelancaran;
   - ragu/terhenti;
   - lafaz terlewat;
   - pergantian lafaz;
   - urutan;
   - ketergantungan pada prompt guru.

   Fokus koreksi adalah alat tindak lanjut, bukan label permanen pada santri.

4. **Setoran lebih bermakna**
   - cara penyampaian: talaqqi, individual, tasmi‘ kelompok, setoran rumah, ujian;
   - jumlah prompt guru;
   - jumlah koreksi mandiri;
   - jadwal Murāja‘ah berikutnya;
   - terhubung ke learning cycle dan target.

5. **Murāja‘ah lebih terhubung**
   - dapat memilih jadwal Murāja‘ah yang sedang jatuh tempo;
   - catatan aktual menyelesaikan jadwal tersebut;
   - dapat membuat jadwal berikutnya;
   - merekam prompt dan koreksi mandiri.

6. **Dashboard Perjalanan Tahfizh Guru**
   - Murāja‘ah jatuh tempo;
   - siklus aktif;
   - santri yang membutuhkan tindak lanjut;
   - perjalanan individual;
   - fokus koreksi;
   - jadwal penjagaan;
   - histori setoran dan Murāja‘ah.

7. **Ringkasan untuk wali**
   - target aktif;
   - jadwal penjagaan berikutnya;
   - arahan keluarga dari guru;
   - status tindak lanjut tanpa menampilkan ranking atau data anak lain.

8. **Laporan administratif**
   - ekspor Tahfizh mencakup cara penyampaian, prompt, koreksi mandiri, dan review berikutnya;
   - ekspor Murāja‘ah mencakup prompt, koreksi mandiri, review dan rekomendasi.

## Struktur database baru

- `tahfizh_learning_cycles`
- `memorization_review_plans`
- `quran_learning_error_items`

Kolom tambahan:
- `memorization_records.learning_cycle_id`
- `memorization_records.delivery_mode`
- `memorization_records.prompt_count`
- `memorization_records.self_correction_count`
- `memorization_records.next_review_date`
- `murajaah_records.learning_cycle_id`
- `murajaah_records.review_plan_id`
- `murajaah_records.prompt_count`
- `murajaah_records.self_correction_count`

## Kriteria Fase 3 menjadi 100%

### Implementasi
- [x] Tahsīn, setoran dan Murāja‘ah terhubung dengan pertemuan.
- [x] Target terhubung ke siklus belajar.
- [x] Talaqqi/tasmi‘ dapat dicatat sebagai bagian alur.
- [x] Jadwal penjagaan tersedia.
- [x] Fokus koreksi terstruktur tersedia.
- [x] Dashboard perjalanan Tahfizh guru tersedia.
- [x] Ringkasan penjagaan untuk wali tersedia.
- [x] Audio/preset Quran tetap dapat dipakai sebagai pendamping.

### Validasi produksi
- [ ] Guru menjalankan target → persiapan → setoran → penguatan → Murāja‘ah pada data nyata.
- [ ] Wali hanya melihat anak sendiri dan memahami arahan keluarga.
- [ ] Talaqqi dan tasmi‘ diuji dengan setidaknya satu pertemuan nyata/pilot.
- [ ] Jadwal Murāja‘ah dapat diselesaikan dan dijadwalkan ulang tanpa duplikasi yang salah.
- [ ] Form Tahsīn/Tahfizh/Murāja‘ah nyaman digunakan dari ponsel.

**Fase 3 tidak boleh dinyatakan selesai sebelum seluruh validasi di atas ditandai selesai pada Kesiapan Peluncuran.**

## Batas fase

Fase 3 belum membangun keputusan naik/tetap/turun Marhalah dan milestone penjagaan lintas surah/rubu‘/juz secara penuh. Itu menjadi fokus **Fase 4 — Marhalah & Milestone**.
