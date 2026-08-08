# Build Report — Sullamul Ḥifẓ v2.5.2

Release: **Tahfizh Unified Workflow / Phase 3 Closure**  
Date: **2026-08-07**

## Scope
Rilis ini menutup gap UX Fase 3 yang ditemukan saat validasi produksi: halaman Perjalanan Tahfizh sudah menampilkan siklus, jadwal, fokus koreksi, dan riwayat, tetapi guru masih harus berpindah ke Operasional Hari Ini untuk mencatat Setoran dan Murāja‘ah aktual.

v2.5.2 menambahkan pencatatan individual langsung pada halaman perjalanan santri:

- Catat Setoran Tahfizh;
- Catat Murāja‘ah;
- hubungkan target/siklus;
- fokus koreksi;
- tindak lanjut/rekomendasi;
- jadwal Murāja‘ah berikutnya;
- penyelesaian review plan;
- activity log;
- auto-fill target dan jadwal ke surah/rentang ayat.

Operasional Hari Ini tetap dipertahankan untuk pencatatan kelas/massal.

## Perubahan source utama
- `app/Http/Controllers/Teacher/TahfizhController.php`
- `resources/views/teacher/tahfizh/student.blade.php`
- `routes/web.php`
- `app/Services/RoadmapStatusService.php`
- `tests/Feature/TahfizhUnifiedWorkflowV252Test.php`

## Database
Tidak ada migration baru. `meeting_id` pada `memorization_records` dan `murajaah_records` sudah nullable sejak fondasi awal, sehingga pencatatan individual dapat disimpan tanpa membuat pertemuan palsu.

## Security / isolation
- tetap memverifikasi institution tenant;
- tetap memverifikasi bahwa santri berada dalam penugasan guru;
- target dan review plan dibatasi ke institution + santri yang sedang dibuka;
- rentang ayat divalidasi terhadap jumlah ayat surah;
- target terpilih wajib cocok dengan surah/rentang setoran sebelum status target boleh diperbarui;
- pencatatan individual masuk Activity Log.

## Static checks
- PHP syntax lint: **200 file OK**
- Blade/PHP lexical check: **97 file OK**
- route/action/view assertions: **OK**
- regression guard `$errors` Laravel: **OK**
- ZIP integrity: dilakukan setelah packaging.

## Runtime tests
Full PHPUnit tidak dijalankan di workspace karena source tidak membawa folder `vendor/` dan belum memiliki `composer.lock`. Test `TahfizhUnifiedWorkflowV252Test.php` disertakan untuk dijalankan pada environment Laravel lengkap/Coolify.

## Production validation yang masih diperlukan untuk menutup Fase 3
1. Setoran individual tersimpan ke Riwayat Setoran.
2. Fokus koreksi muncul dan dapat ditandai selesai.
3. Tanggal review menghasilkan Jadwal Penjagaan.
4. Murāja‘ah dapat ditautkan ke jadwal dan menyelesaikannya.
5. Siklus berubah sesuai hasil setoran/Murāja‘ah.
6. Tampilan mobile tetap nyaman.
7. Ringkasan wali menampilkan hasil yang benar dan tetap terisolasi per anak.

Fase 3 hanya boleh menjadi 100% setelah implementasi dan seluruh validasi produksi di atas lulus.
