# Build Report — Sullamul Hifz v2.6.2

## Fokus rilis

Stage Schedule History untuk Fase 4 Qur’an Journey.

## Perubahan tervalidasi secara statis

- Arahan guru disimpan per Juz/Marhalah pada `student_marhalah_histories`.
- Tahap baru otomatis kembali ke `cadence_mode=flexible` dan `cadence_notes=null`.
- Tahap lama menyimpan snapshot Juz, stage, porsi, pola pelaksanaan, dan catatan guru.
- Endpoint guru untuk mengubah arahan tahap aktif tersedia.
- UI menampilkan **Arahan tahap aktif** dan **Riwayat arahan tahap sebelumnya**.
- Migrasi melakukan koreksi legacy untuk catatan yang sempat terbawa dari tahap lama pada v2.6.0.
- Roadmap Fase 4 menambahkan pemeriksaan fondasi schedule-history.

## Pemeriksaan

- PHP non-Blade: **328 file**, seluruhnya lolos `php -l`.
- Shell script: **29 file**, seluruhnya lolos `bash -n`.
- JSON: **4 file**, seluruhnya valid.
- Scan regresi inline Blade `@php(...;...)`: lulus.
- Scan regresi reserved Laravel `$errors`: lulus.
- Test source baru: `QuranJourneyStageScheduleV262Test.php`.

## Batas pemeriksaan lokal

Repository tidak membawa folder `vendor/` dan belum memiliki `composer.lock`, sehingga full Laravel boot/PHPUnit tidak dijalankan di workspace ini. Coolify tetap menjadi integration/runtime validation setelah deploy.

## Validasi produksi yang diminta

1. Affan pada Juz 29/Tsalātsiyyah tidak lagi menampilkan arahan lama “Senin menghafal minimal 1 ayat” sebagai arahan aktif.
2. Arahan lama tersebut tampil pada histori Juz 30/Āyah.
3. Juz 29 dimulai dengan pola **Fleksibel**.
4. Guru dapat menyimpan arahan baru Juz 29 dan nilai tetap ada setelah refresh.
5. Perpindahan tahap berikutnya mengarsipkan arahan Juz aktif lalu memulai tahap baru secara fleksibel.
