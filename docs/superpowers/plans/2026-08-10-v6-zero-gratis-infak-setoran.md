# Sullamul Hifz v6.0.0 Implementation Plan

> **For agentic workers:** Implement this plan task-by-task with a failing-test-first cycle. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menghasilkan satu rilis v6.0.0 yang membuat pencatatan setoran selesai dalam beberapa detik, membuka seluruh fungsi inti secara gratis, menyediakan infak sukarela transparan, dan menutup temuan keamanan/deployment v5.3.0.

**Architecture:** Data historis v5.3.0 tetap dipertahankan. Alur baru menambahkan profil fokus individual dan asesmen berkala, sedangkan setoran harian menggunakan tiga keputusan sederhana yang dipetakan ke nilai lama agar laporan lama tetap terbaca. Modul subscription dibuat arsip/nonaktif; infak memakai ledger tersendiri yang idempoten dan tidak menentukan hak akses. Seluruh perubahan database bersifat additive.

**Tech Stack:** PHP 8.4, Laravel 13, Blade, MySQL/SQLite test, JavaScript mandiri, Docker/NGINX Unit.

## Global Constraints

- Semua fungsi pembinaan inti gratis tanpa paywall dan tanpa syarat infak.
- Saat anak membaca, ustadz tidak diminta mengisi data; input dilakukan setelah setoran.
- Setoran harian berisi bagian ayat, keputusan `lanjut|kuatkan|ulang`, dan satu catatan opsional.
- Fokus pembinaan hanya satu yang aktif per anak; penilaian lengkap hanya pada asesmen berkala.
- Data v5.3.0 tidak dihapus; kolom rinci lama tetap dapat dibaca.
- Infak sukarela, tidak memengaruhi akses atau perlakuan pengguna, dan alokasinya dapat dilaporkan.
- Isolasi tenant, perlindungan anak, audit, idempotensi transaksi, dan rollback-safe migration wajib dipertahankan.
- Target rilis tunggal `6.0.0`; deployment produksi dilakukan setelah CI dan staging lulus.

---

### Task 1: Schema v6.0.0 dan model domain

**Files:**
- Create: `database/migrations/2026_08_10_007000_free_infaq_distraction_free_v600.php`
- Create: `app/Models/StudentMemorizationFocus.php`
- Create: `app/Models/StudentMemorizationAssessment.php`
- Create: `app/Models/InfaqTransaction.php`
- Modify: `app/Models/Student.php`
- Modify: `app/Models/MemorizationRecord.php`
- Modify: `app/Models/MurajaahRecord.php`
- Test: `tests/Feature/FreeInfaqDistractionFreeV600Test.php`

**Interfaces:**
- Produces `StudentMemorizationFocus::activeFor(int $institutionId, int $studentId)`.
- Produces `student_memorization_assessments` dengan lima aspek nullable dan satu ringkasan.
- Produces `infaq_transactions` dengan `public_id` dan `idempotency_key` unik.
- Menambahkan `daily_decision` dan `short_note` pada catatan setoran/Murāja‘ah.

- [ ] Tulis test skema yang memastikan ketiga tabel baru, unique constraint, dan empat kolom additive tersedia.
- [ ] Jalankan `php artisan test --filter=FreeInfaqDistractionFreeV600Test` dan pastikan gagal sebelum migration.
- [ ] Tambahkan migration dengan indeks tenant, status, tanggal, serta foreign key yang aman.
- [ ] Tambahkan model, relasi, casts, dan scope tenant/fokus aktif.
- [ ] Jalankan test skema sampai lulus.

### Task 2: Setoran Tanpa Distraksi dan tindak lanjut otomatis

**Files:**
- Create: `app/Services/DistractionFreeSubmissionService.php`
- Modify: `app/Services/TahfizhLearningService.php`
- Modify: `app/Http/Controllers/Teacher/TahfizhController.php`
- Modify: `app/Http/Controllers/Teacher/MeetingController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/DistractionFreeSubmissionV600Test.php`

**Interfaces:**
- `recordMemorization(User $actor, Student $student, array $data, ?Meeting $meeting): MemorizationRecord`.
- `recordMurajaah(User $actor, Student $student, array $data, ?Meeting $meeting): MurajaahRecord`.
- Input harian: target/jadwal opsional, surah, ayat awal/akhir, `daily_decision`, `short_note`, dan konfirmasi porsi bila benar-benar diperlukan.
- Mapping kompatibilitas: `lanjut→fluent/maintained`, `kuatkan→fair/strengthening_needed`, `ulang→repeat_needed/reactivation_needed`.

- [ ] Tulis test bahwa payload minimal membuat record, memperbarui target, dan menghasilkan review plan otomatis.
- [ ] Tulis test bahwa target/jadwal lintas santri atau lintas tenant ditolak.
- [ ] Implementasikan satu service transaksional untuk kedua controller agar logika tidak terduplikasi.
- [ ] Jadwalkan rekomendasi default: lanjut `+7 hari`, kuatkan `+2 hari`, ulang `+1 hari`, tetap dapat diubah ustadz.
- [ ] Pastikan penyimpanan ganda akibat refresh menggunakan idempotency key form dan ditolak aman.
- [ ] Jalankan test fitur sampai lulus.

### Task 3: Antarmuka ustadz 5–10 detik

**Files:**
- Create: `resources/views/teacher/tahfizh/partials/quick-memorization-form.blade.php`
- Create: `resources/views/teacher/tahfizh/partials/quick-murajaah-form.blade.php`
- Modify: `resources/views/teacher/tahfizh/student.blade.php`
- Modify: `resources/views/teacher/meetings/show.blade.php`
- Modify: `resources/views/teacher/tahfizh/index.blade.php`
- Modify: `public/css/app.css`
- Test: `tests/Feature/DistractionFreeSubmissionV600Test.php`

**Interfaces:**
- Form target mengisi santri/surah/ayat otomatis melalui `data-*`, dengan tombol ubah bagian.
- Tombol keputusan besar: `Lanjut`, `Kuatkan`, `Ulang`.
- Hanya satu `short_note` opsional tampil pada kondisi normal.

- [ ] Tulis test view yang menolak field `prompt_count`, `error_count`, `self_correction_count`, dan multi-aspek pada form harian.
- [ ] Buat partial yang sama dipakai halaman individual dan pertemuan.
- [ ] Tambahkan pemilihan anak/target otomatis dan tombol langsung ke anak berikutnya.
- [ ] Pindahkan rincian lama ke riwayat/asesmen, bukan form harian.
- [ ] Verifikasi aksesibilitas label, fokus keyboard, ukuran sentuh, dan tampilan mobile.

### Task 4: Tangga Fokus dan asesmen berkala

**Files:**
- Create: `app/Http/Controllers/Teacher/MemorizationFocusController.php`
- Create: `resources/views/teacher/tahfizh/assessment.blade.php`
- Modify: `app/Http/Controllers/Teacher/TahfizhController.php`
- Modify: `resources/views/teacher/tahfizh/student.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/MemorizationFocusAssessmentV600Test.php`

**Interfaces:**
- Fokus: `accuracy`, `fluency`, `independence`, `makhraj_tajwid`, `retention`.
- Hanya satu record fokus aktif per `(institution_id, student_id)`; perubahan menutup fokus lama.
- Asesmen: `initial`, `monthly`, `completion`, `tasmi`, `exam`, `stagnation`.

- [ ] Tulis test fokus individual, tenant authorization, riwayat fokus, dan asesmen lima aspek.
- [ ] Implementasikan update fokus dalam transaksi dengan `lockForUpdate()`.
- [ ] Implementasikan asesmen berkala terpisah dari setoran harian.
- [ ] Tampilkan fokus hanya sebagai pengingat, bukan skor atau peringkat.
- [ ] Jalankan test fitur sampai lulus.

### Task 5: Ringkasan otomatis untuk ustadz dan wali

**Files:**
- Create: `app/Services/MemorizationSummaryService.php`
- Modify: `app/Services/TahfizhProgressService.php`
- Modify: `app/Http/Controllers/FamilyPortalController.php`
- Modify: `resources/views/family/index.blade.php`
- Modify: `resources/views/teacher/tahfizh/student.blade.php`
- Test: `tests/Feature/MemorizationSummaryV600Test.php`

**Interfaces:**
- `forStudent(Student $student, CarbonInterface $from, CarbonInterface $to): array` mengembalikan setoran, keputusan, fokus, latihan, target berikutnya, dan tren tanpa ranking.

- [ ] Tulis test bahwa wali hanya melihat anak dengan hubungan dan consent aktif.
- [ ] Buat agregasi harian/mingguan dari data minimal.
- [ ] Tampilkan bahasa yang dapat ditindaklanjuti: bagian latihan dan target berikutnya.
- [ ] Pastikan catatan privat internal tidak bocor ke wali.

### Task 6: Akses gratis dan infak sukarela transparan

**Files:**
- Create: `app/Services/InfaqService.php`
- Create: `app/Http/Controllers/InfaqController.php`
- Create: `resources/views/infaq/index.blade.php`
- Create: `resources/views/admin/infaq/index.blade.php`
- Modify: `app/Services/BusinessBillingService.php`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `routes/web.php`
- Modify: `config/sullam.php`
- Modify: `.env.example`
- Test: `tests/Feature/FreeAccessInfaqV600Test.php`

**Interfaces:**
- Tujuan: `teacher_development`, `scholarship`, `foundation_operations`, `technology`, `general`.
- Status: `pending`, `verified`, `rejected`, `refunded`.
- `InfaqService::createPending(User $user, array $data, string $idempotencyKey): InfaqTransaction`.
- Entitlement tidak lagi membaca subscription; fungsi inti selalu tersedia.

- [ ] Tulis test akses inti tanpa subscription dan tanpa transaksi infak.
- [ ] Tulis test infak idempoten, tenant-scoped, bukti penerimaan, dan laporan agregat.
- [ ] Nonaktifkan pembuatan subscription/invoice baru tanpa menghapus histori.
- [ ] Buat halaman dukungan tanpa pola manipulatif dan tanpa status sosial pemberi.
- [ ] Buat verifikasi admin serta ringkasan alokasi publik berbasis transaksi terverifikasi.

### Task 7: Security dan deployment hardening

**Files:**
- Modify: `app/Http/Controllers/Admin/GuardianController.php`
- Modify: `app/Http/Middleware/EnforceDomainSeparation.php`
- Modify: `routes/api.php`
- Modify: `app/Models/Institution.php`
- Modify: `scripts/container-start.sh`
- Create: `scripts/release-tasks.sh`
- Modify: `.github/workflows/tests.yml`
- Test: `tests/Feature/SecurityHardeningV600Test.php`
- Test: `tests/Feature/DomainSeparationTest.php`

**Interfaces:**
- Menonaktifkan wali hanya menutup profil/membership/role tenant saat ini; `users.status` global tidak berubah.
- `/api/*` hanya diterima di API host saat domain separation aktif.
- Academy preview hanya mengambil lembaga dengan `settings.public_academy=true`.
- Migration/seeder/verifier berat dipindah ke release task satu-kali; web replica hanya menunggu schema dan start.

- [ ] Tulis regression test akun lintas workspace, API wrong-host, preview nonpublik, dan idempotensi transaksi.
- [ ] Perbaiki status wali pada membership dan role scoped tenant.
- [ ] Tolak API pada host non-API dan route non-API pada host API.
- [ ] Tambahkan opt-in publik Academy di settings.
- [ ] Pisahkan release task dari startup setiap replica dan cek shell dengan `sh -n`.

### Task 8: Release, dokumentasi, dan quality gate

**Files:**
- Modify: `RELEASE`
- Modify: `VERSION`
- Modify: `README.md`
- Modify: `docs/CURRENT-STATE.md`
- Modify: `docs/PHASE-REGISTRY.md`
- Create: `docs/releases/v6.0.0.md`
- Create: `UPGRADE-V6.0.0.md`
- Create: `DEPLOY-QUICK-V6.0.0.txt`
- Modify: `Dockerfile`
- Modify: `scripts/check-release-docs.sh`

**Interfaces:**
- Verifier release `sullam:verify-release-v600` memeriksa schema, free-access, route, privacy, dan konfigurasi infak.

- [ ] Tambahkan verifier dan test command.
- [ ] Jalankan PHPUnit lengkap, Composer audit, route list, Blade cache/lint, phase manifest, shell syntax, dan Docker build bila runtime tersedia.
- [ ] Catat dengan jelas pemeriksaan yang tidak dapat dijalankan di lingkungan lokal.
- [ ] Sinkronkan semua penanda versi aktif ke `6.0.0` tanpa menulis ulang arsip rilis lama.
- [ ] Buat ZIP bersih tanpa `.env`, cache, credential, vendor lokal, atau artefak audit.

## Self-Review

- Cakupan spesifikasi: setoran minimal, tangga fokus, asesmen berkala, Murāja‘ah otomatis, ringkasan wali, gratis, infak, keamanan, deployment, migrasi, dan rilis tercakup.
- Tidak ada penghapusan tabel/kolom historis; rollback migration hanya menghapus objek v6.0.0.
- Nama keputusan, fokus, tujuan infak, dan status konsisten di seluruh task.
- Pilot lembaga, rekening/payment gateway produksi, dan formula pembagian resmi adalah aktivasi operasional setelah deploy; sistem konfigurasinya disiapkan, tetapi tidak dipalsukan sebagai sudah diverifikasi.
