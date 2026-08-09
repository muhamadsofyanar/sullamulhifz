# v4.0.0 — Satu Ruang Qur’an — 2026-08-09

- Menggabungkan sepuluh workstream pengalaman produk dalam satu paket deploy.
- Membuat Beranda Personal baru dengan tindakan utama, status hari ini, program aktif, notifikasi, dan kesinambungan.
- Menambah halaman Perjalanan Saya yang menyatukan jurnal, latihan, Qur’an Journey, dan setoran asatidz tanpa ranking.
- Menambah check-in harian privat agar beban dapat disesuaikan dengan keadaan nyata.
- Memperluas enrollment Personal ke Academy, Community Terbatas, dan Pembayaran Program; URL langsung tetap dijaga middleware.
- Menambah Kendali Ekosistem admin untuk menetapkan akses per akun tanpa redeploy.
- Mengaktifkan alur community bermoderasi: ruang draft/aktif, tulisan pending, keputusan manusia, dan audit moderasi.
- Mengaktifkan alur transfer bank: tujuan resmi BSI, snapshot rekening, konfirmasi pengguna, serta rekonsiliasi admin.
- Menjaga feature flag Community dan Payments tetap OFF setelah migration; keduanya harus diaktifkan dan ditugaskan secara sengaja.
- Menjaga AI Assist tetap human-review/audit dan tidak diaktifkan otomatis.

# v3.4.0 — Personal Enrollment Lifecycle — 2026-08-09

- Menambahkan pilihan Latihan Qur’an, Qur’an Journey, dan Program dengan Asatidz langsung pada pendaftaran Personal; pengguna tetap boleh memulai hanya dengan jurnal dan target.
- Menyatukan pilihan pendaftaran, Program Saya, Beranda, sidebar, navigasi bawah ponsel, serta route guard pada enrollment modul yang sama.
- Menambahkan nonaktivasi program tanpa menghapus histori latihan, jurnal, target, setoran, atau progres lama.
- Membuat status enrollment eksplisit menjadi sumber keputusan utama agar histori lama tidak membuka kembali program yang sudah dinonaktifkan.
- Menjaga Program Asatidz/Qur’an Journey tetap aktif selama masih terhubung ke enrollment program yang berjalan; Academy tetap hanya diturunkan dari program Guided Quran yang terhubung.
- Tidak menambah migration, tidak mengubah konfigurasi rekening resmi, dan tidak mengaktifkan payment/AI/community secara otomatis.

# v3.3.0 — Personal Program Hub — 2026-08-09

- Mengubah Ruang Personal dari pengalaman yang berpusat pada `Belajar & Audio` menjadi hub modular berbasis enrollment.
- Menambah `personal_module_enrollments` sebagai sumber hak akses eksplisit untuk Latihan Qur’an, Qur’an Journey, dan Program dengan Asatidz.
- Beranda, sidebar, dan navigasi mobile Personal hanya menampilkan modul yang aktif untuk akun tersebut; jurnal, target, dan catatan aktivitas tetap menjadi fondasi privat yang selalu tersedia.
- Academy muncul otomatis hanya jika program Guided Quran aktif yang diikuti memang terhubung ke materi Academy.
- Menambah `Program Saya` agar pengguna Personal dapat mengaktifkan modul self-service yang tersedia tanpa membuka semua modul secara default.
- Menambah middleware akses Personal sehingga URL langsung ke modul yang belum terdaftar ditolak tanpa memengaruhi Guru/Wali/Admin.
- Membackfill entitlement akun lama dari Guided Quran enrollment, Qur’an Journey enrollment, dan histori Latihan Qur’an yang sudah nyata.
- Latihan Qur’an Personal dapat membaca pustaka murattal/timing bersama tanpa menggandakan ribuan timing ke setiap workspace Personal.
- Menyesuaikan Home publik, halaman Program, pendaftaran, dan copy Personal agar arsitektur modular ini terlihat konsisten dari depan sampai portal.
- Menjaga konfigurasi transfer resmi v3.2.1 dan seluruh fondasi v3.2.0 tanpa perubahan destruktif.

# v3.2.1 — Official Bank Transfer Configuration — 2026-08-09

- Menambahkan rekening transfer resmi BSI atas nama YYS INSAN QURAN MADANI.
- Menambahkan helper ledger untuk membuat transaksi transfer manual dengan snapshot rekening tujuan.
- Feature flag pembayaran tetap tidak diaktifkan otomatis; tidak ada perubahan schema/database.

# v3.2.0 — Roadmap Completion Foundations — 2026-08-09

## Added
- Menambah progres Character/Talent berbasis rubrik naratif non-ranking dan evidence portofolio.
- Menambah reminder Murāja‘ah terjadwal, database-only, dan idempotent melalui `reminder_sent_at`.
- Menambah AI Assist draft dengan human review wajib, final text terpisah, serta audit keputusan.
- Menambah audit moderasi Community dan payment transaction ledger sebagai readiness Fase 10.
- Menambah verifier `sullam:verify-roadmap-foundations-v320` dan command `sullam:send-murajaah-reminders`.

## Fixed
- Membawa patch production morph map `quran_guided_submission` ke baseline kandidat.
- Menambah morph map `user` agar database notification dapat disimpan dengan `Relation::enforceMorphMap`.

## Guardrail
- Tidak mengaktifkan multi-institution, Community, provider pembayaran, atau integrasi eksternal secara otomatis.
- Fase 8–9 tetap memerlukan launch check produksi; Fase 10 tetap belum 100% sampai tenant/integrasi/scale test nyata lulus.

# v3.1.1 — Guided Quran Learning Recovery — 2026-08-08

## Fixed
- Memberi nama eksplisit yang pendek pada dua foreign key tabel `quran_guided_submission_reviews` agar kompatibel dengan batas identifier MySQL 64 karakter.
- Menjaga migration Guided Quran tetap dapat dijalankan sebagai migration `002200` karena rilis v3.1.0 di production belum pernah tercatat `Ran`.
- Tidak mengubah tabel atau data Personal v3.0.0; recovery hanya menyasar struktur Guided Quran v3.1.x yang sebelumnya gagal dan kosong.

# v3.1.0 — Guided Quran Learning — 2026-08-08

- Menambah Learning Hub Personal: murattal Al-Husary/Al-Minshawi, katalog Program Online, setoran dan riwayat feedback dalam satu alur.
- Menambah Program Al-Qur’an `online`, `offline`, atau `hybrid` untuk Tahfizh, Tahsin, membaca Al-Qur’an, dan Murāja‘ah.
- Pengguna Personal dapat mengikuti program publik tanpa berubah menjadi santri lembaga penyelenggara; workspace dan jurnal pribadinya tetap terisolasi.
- Menambah setoran teks atau voice note/audio dengan status `pending`, `revision`, `verified`, atau `rejected`.
- Menambah reviewer asatidz eksplisit per program serta review teks/audio; admin/kepala penyelenggara tetap dapat menangani antrian program miliknya.
- Akses media lintas workspace hanya diberikan kepada pengirim, reviewer yang ditugaskan, dan pengelola lembaga penyelenggara; jurnal Personal tidak ikut terbuka.
- Program dapat dikaitkan ke materi Academy. Pengguna Personal hanya dapat membuka Academy lintas workspace melalui program yang aktif diikutinya.
- Santri TPA dapat dimasukkan ke program yang sama untuk jalur offline/hybrid; pencatatan pertemuan/Tahfizh offline tetap memakai workflow lembaga yang sudah ada.
- Menambah permission `guided_learning.use`, `guided_learning.review`, `guided_learning.manage`, verifier `sullam:verify-guided-quran`, dan regression test isolasi enrollment dua akun Personal.
- Memperbarui Privasi/Syarat agar membedakan self-record Personal dari setoran yang sengaja dibagikan untuk review.

# v3.0.0 — Public Self-Registration + Personal Mode — 2026-08-08

- Membuka pendaftaran mandiri masyarakat melalui `/daftar-personal` tanpa harus bergabung dengan lembaga.
- Setiap pendaftar mendapat workspace privat internal, role `personal`, dan profil perjalanan yang terisolasi dari pengguna Personal lain.
- Menambah onboarding fokus, ritme harian, target juz/surah dan tanggal target tanpa menjadikannya penilaian kemampuan.
- Menambah jurnal mandiri untuk hafalan baru, Murāja‘ah, tilawah, dan refleksi dengan rentang ayat, durasi, penilaian diri, dan catatan.
- Menambah target terukur untuk ayat hafalan, ayat Murāja‘ah, menit latihan, hari aktif, atau jumlah sesi; progres dihitung dari jurnal nyata.
- Menambah dashboard Personal dengan ringkasan 7 hari, streak konsistensi, target, jurnal terbaru, dan arahan harian berbasis aktivitas.
- Membuat navigasi Personal terpisah dari menu lembaga serta mempertahankan form pendaftaran TPA yang sudah ada.
- Memperbarui Privasi dan Syarat & Ketentuan untuk menjelaskan self-record, isolasi data Personal, dan guardrail STIFIn.
- Menambah `sullam:verify-personal-mode` dan regression test pendaftaran, workspace privat, ownership lintas akun, serta independensi arahan dari STIFIn.
- Fase 7 v2.9.0 telah lulus smoke test produksi observasi → rekomendasi → teacher override; dua gate manual Fase 6 tetap tercatat pending.

# v2.9.0 — Personal Learning System — 2026-08-08

- Mengaktifkan Fase 7 sebagai workflow personalisasi berbasis evidence dengan keputusan akhir tetap pada guru.
- Menambah halaman Guru `Personalisasi Belajar` untuk memilih santri ampuan, melihat observasi, membuat draf rekomendasi, lalu menerima/mengubah/menolaknya.
- Menambah `PersonalLearningRecommendationService` yang hanya memakai observasi belajar, setoran Tahfizh, dan Murāja‘ah sebagai evidence.
- Menambah audit `learning_recommendation_reviews` untuk menyimpan rekomendasi awal, keputusan guru, rekomendasi final dan alasan review.
- Menambah tenant/assignment guard agar guru tidak dapat membuat atau mereview rekomendasi santri di luar penugasannya.
- STIFIn tidak dipakai sebagai input mesin rekomendasi; verifier produksi gagal bila evidence atau isi rekomendasi membawa STIFIn.
- Menambah command `sullam:verify-personal-learning` dan regression test Fase 7.
- Fase 6 tetap dipertahankan; dua gate manual keamanan lintas pengguna dan guardrail STIFIn Fase 6 tetap tercatat pending sampai diuji.

# v2.8.0 — Family & Teacher Ecosystem — 2026-08-08

- Menambah aktivitas keluarga terstruktur: guru memilih santri, aktivitas, instruksi, tenggat opsional dan materi Parent Academy opsional.
- Wali dapat menyelesaikan aktivitas dengan refleksi naratif; guru dapat mereview dan menulis tindak lanjut.
- Menambah kompetensi/pelatihan guru yang dapat dikaitkan ke materi Teacher Academy.
- Guru menyimpan proses, refleksi dan bukti praktik; admin/kepala mereview sebagai `demonstrated` atau `needs_follow_up` tanpa skor/ranking.
- Menambah tenant/ownership guard untuk aktivitas anak, materi Academy terhubung, kompetensi dan review.
- Menambah halaman Admin, Guru dan Wali untuk Fase 6 serta command `sullam:verify-family-teacher`.
- Memperbaiki tampilan materi Academy lama yang menyimpan `\\n` literal agar kembali tampil sebagai paragraf/baris baru.
- Menambah regression test struktur Fase 6 dan memastikan tabel progres tidak memiliki kolom score/rank.

# v2.7.0 — Academy LMS 2.0 — 2026-08-08

- Menambah prerequisite lesson/path dengan enforcement sebelum konten dibuka.
- Menambah kuis pilihan ganda, passing score, batas percobaan, attempt dan jawaban terstruktur.
- Menambah worksheet/refleksi atau self-check sebagai syarat penyelesaian materi.
- Completion lesson sekarang memeriksa quiz/worksheet wajib.
- Menambah sertifikat otomatis setelah program tuntas dan halaman verifikasi publik.
- Menambah authoring prerequisite, quiz, pertanyaan dan worksheet di Academy Studio.
- Menambah regression test Fase 5 dan mempertahankan compile/lint Blade sebagai release gate.

# v2.6.4 — Qur’an Journey Stabilization — 2026-08-08

- Memperbaiki HTTP 500 detail Qur’an Journey guru akibat directive Blade `@endif` yang menempel pada teks.
- Menambahkan compile + syntax-lint seluruh compiled Blade pada GitHub Actions dan Docker build.
- Menambahkan regression test HTTP untuk detail Qur’an Journey pada Juz 30, 29, 28, 27, 26, dan jalur Juz 1–25.
- Memindahkan sinkronisasi jaringan Mushaf keluar dari request GET detail guru; cache disiapkan oleh startup/background sync.
- Mengganti startup versioned dengan `scripts/container-start.sh` yang membaca versi dari `RELEASE`.
- Menyelaraskan label image Docker dan dokumentasi deploy ke v2.6.4.
- Post-deployment command Coolify tidak lagi diperlukan; migration dijalankan sekali oleh startup container saat `AUTO_MIGRATE=true`.
- Tidak ada migration database baru dan tidak ada environment variable baru.

# v2.6.3 — All Marhalah Portion Engine — 2026-08-08

- Menambahkan Mushaf Page Engine untuk porsi ½, 1, dan 2 halaman.
- Menambahkan penyimpanan halaman akhir target melalui migration `mushaf_end_page_number`.
- Menyelesaikan implementasi porsi kandidat untuk seluruh enam pola Marhalah.

# v2.6.2 — Stage Schedule History

- Arahan guru sekarang berlaku per Juz/Marhalah, bukan menempel selamanya pada profil santri.
- Tahap baru selalu dimulai dengan pola Fleksibel; sistem tidak mengarang atau mewariskan instruksi tahap lama.
- Catatan tahap lama diarsipkan sebagai riwayat lengkap dengan Juz, Marhalah, porsi, pola, dan periode.
- Guru dapat memperbarui pola pelaksanaan dan arahan tahap aktif langsung dari Qur’an Journey.
- Migrasi memperbaiki data legacy v2.6.0 yang sempat membawa catatan Juz 30 ke Juz 29.

# v2.6.1 — Mushaf Line Engine

- Tsalātsiyyah (Juz 29) sekarang menggunakan blok fisik 3 slot Mushaf: 1–3, 4–6, 7–9, 10–12, 13–15.
- Khamsiyyah (Juz 28) menggunakan blok fisik 5 slot: 1–5, 6–10, 11–15.
- Nama surah dan basmalah tetap dihormati sebagai slot fisik layout halaman; batas porsi disimpan sampai lokasi kata.
- Layout 604 halaman disinkronkan resume-safe dan dapat dimuat on-demand ketika guru membuka halaman.
- Target Tahfizh menyimpan halaman/baris/batas kata sehingga porsi yang mulai/berakhir di tengah ayat tidak kehilangan batas Mushaf.
- Target tunggal aktif diprioritaskan otomatis di Perjalanan Tahfizh.
- Statistik memisahkan Juz selesai hafalan dari Juz terjaga; dropdown milestone mengikuti status aktual.
- Catatan porsi tahap lama tidak dibawa ke Juz/Marhalah baru.

# Changelog

## v2.6.0 — Qur’an Journey — 2026-08-08
- Mengunci Marhalah berdasarkan perjalanan Juz: 30 Āyah, 29 Tsalātsiyyah, 28 Khamsiyyah, 27 Niṣfiyyah, 26 Ṣafḥah, Juz 1–25 Ṣafḥatayn.
- Menegaskan porsi adalah standar per sesi, bukan kewajiban harian.
- Menambahkan porsi Marhalah lintas surah dalam Juz yang sama dan memecahnya ke target setoran terkait.
- Menambahkan milestone hafalan terpisah dari status penjagaan dan histori retention check.
- Menambahkan Fondasi 5 Juz dan jembatan milestone Manzil Qaf (Qāf–An-Nās).
- Menambahkan Khatam Al-Qur’an 30 Hari dan Fami Bisyauqin 7 Manzil untuk tilawah, Murāja‘ah, atau keduanya.
- Menambahkan Peta Mushaf & Warisan Ulama: Juz, Ḥizb, Rubu‘ al-Ḥizb, Manzil, Rukū‘, Waqaf, Sajdah, Makki/Madani.
- Meluruskan delapan `quran_rubus` v1.5 sebagai Segment Juz 30 legacy, bukan Rubu‘ al-Ḥizb standar.
- Menambahkan Qur’an Journey untuk guru, program pribadi, tampilan read-only wali, roadmap, verifier, dan startup seeder/sync.

## v2.5.2 — Tahfizh Unified Workflow — 2026-08-07
- Menyatukan pencatatan individual Tahfizh di halaman Perjalanan Tahfizh santri.
- Guru dapat mencatat setoran, Murāja‘ah, fokus koreksi, tindak lanjut, dan jadwal review tanpa keluar ke Operasional Hari Ini.
- Target dan jadwal Murāja‘ah dapat mengisi otomatis surah/rentang ayat pada form individual.
- Catatan individual tetap terhubung dengan siklus belajar, target, fokus koreksi, activity log, dan histori.
- Operasional Hari Ini tetap dipertahankan untuk pencatatan kelas/massal.
- Menambahkan production criterion Fase 3 untuk workflow individual terpadu.

## v2.5.1 — Phase 3 Detail Hotfix — 2026-08-07
- Memperbaiki HTTP 500 pada detail Perjalanan Tahfizh akibat variabel `$errors` menimpa validation error bag Laravel.
- Mengganti collection fokus koreksi menjadi `$correctionItems`.
- Menambahkan regression check agar benturan variabel tidak berulang.

## v2.5.0 — Tahfizh Learning Engine — 2026-08-07
- Menambahkan siklus belajar target → persiapan → setoran → penguatan → Murāja‘ah.
- Menambahkan talaqqi/tasmi‘ sebagai cara belajar/setoran yang tercatat.
- Menambahkan jadwal penjagaan yang ditentukan guru dan dapat diselesaikan oleh catatan Murāja‘ah.
- Menambahkan fokus koreksi terstruktur tanpa ranking atau label negatif.
- Menambahkan prompt guru, koreksi mandiri, dan Murāja‘ah berikutnya pada catatan.
- Menambahkan dashboard Perjalanan Tahfizh guru dan ringkasan penjagaan untuk wali.
- Menambahkan verifikasi Fase 3 dan checklist validasi produksi konservatif.

## v2.4.0 — Full Qur’an & Mushaf Engine — 2026-08-07
- Full Quran corpus: 114 surah, 6.236 ayat, 30 juz, 604 halaman, 240 Rubu‘ al-Hizb.
- Academy mushaf + active ayah highlighting, full-selection player, bookmark ayah/preset, reading progress and resume.
- Al-Husary and Al-Minshawi upgraded from Juz 30 scope to full Quran timing sync, resume-safe per surah.
- Admin/teacher memorization target supports all 114 surahs; Juz 30 Sullam milestones remain optional.
- Honest 10-phase roadmap dashboard: 100% requires both implementation and production validation.

## v2.1.1 — Coolify bootstrap hotfix
- Fix `Target class [config] does not exist` during Composer package discovery.
- Trusted proxies are parsed before Application construction without `config()`.
- Add Docker build smoke-check for Laravel bootstrap and route registration.
- Clarify runtime-only environment variables for Coolify.

## v2.1.0 — Unified Platform & Secure Media — 2026-08-07

- Menggabungkan source v2.0.4 dengan PRD, sitemap, wireframe, ERD, brand strategy, dan icon system terbaru.
- Menambahkan cabang, periode akademik, target konten, histori marhalah, invitation, feature flag, serta pusat media privat.
- Menutup cache respons privat, memperketat upload, tenant isolation, permission, trusted proxy, dan audit media.
- Menambahkan aktivasi akun dan reset kata sandi dengan aturan kata sandi kuat.
- Menyelaraskan navigasi mobile dan icon system solid-organic emerald–gold.
- Mempertahankan Audio Qur’an, Parent Academy/LMS, rapor, website, dan pendaftaran sebagai modul terkontrol.
- Menambahkan API starter, staging noindex, startup satu-redeploy, migration additive, serta utilitas pengamanan media lama.

# v2.0.3 — Academy Experience & Video

- Fix Kelola Academy 500.
- Premium Academy desktop/mobile.
- Embedded YouTube/Shorts lesson support.
- Sample Academy video.
- Full lesson editing from admin.

# v2.0.2 — Academy View Hotfix

- Memperbaiki ParseError pada halaman Kelola Academy.
- Tidak ada perubahan database.

## v2.0.1 — Premium Mobile UX & Academy Domain

- PWA mobile diperhalus menjadi tampilan modern, eksklusif, dan lebih ramah pengguna berusia lanjut.
- Sidebar mobile benar-benar keluar dari viewport saat tertutup; tidak lagi menyisakan strip hijau.
- Asset CSS/JS memakai cache-busting agar PWA tidak menampilkan stylesheet versi lama.
- Quran Player disederhanakan: target guru dan latihan siap pakai menjadi tindakan utama, pengaturan manual dipindahkan ke bagian sekunder.
- Kartu latihan, tombol, player, dan bottom navigation diperbarui dengan area sentuh lebih besar.
- `academy.sullamulhifz.or.id` didukung sebagai pintu masuk Academy pada resource Coolify yang sama.
- Tidak ada perubahan database.


## v2.0.0 — Family Learning & Academy Launch
- PWA mobile-first dan bottom navigation per peran.
- Quran Player v2 yang lebih sederhana dan ramah orang tua.
- Parent Academy dan Teacher Academy.
- Progress materi dan rekomendasi guru ke wali berdasarkan santri yang diampu.
- Admin Academy untuk program, modul, dan materi.
- Integrasi rekomendasi Academy pada profil anak wali.
- Migration additive dan startup/verification v2.0.0.

## v1.9.0 — TPA Launch Complete

See `docs/releases/v1.9.0.md`.

## v1.6.1 — Qari Tahfizh — 2026-08-06

- Al-Husary menjadi qari utama tahfizh.
- Al-Minshawi ditambahkan untuk murajaah dan tadabbur.
- Sinkronisasi dua sumber mencapai target 1.128 timing Juz 30.
- Pemilihan qari berlaku untuk preset, target, dan latihan manual.
- Sumber Al-Ajmi dinonaktifkan tanpa menghapus data lama.

## v1.6.0 — Quran Learning Complete — 2026-08-06

- Menambahkan pustaka audio Juz 30 dan timing 564 ayat.
- Menambahkan pemutar pengulangan ayat/rentang/surah/halaman/rubu’.
- Menambahkan latihan dari target santri dan pencatatan sesi.
- Menambahkan video bacaan terkurasi.
- Menambahkan sinkronisasi audio latar belakang tanpa menunda startup web.


## v1.5.0 — Academic Core Complete

- Added editable institution profile and academic readiness overview.
- Added active semester and enrollment status to academic years.
- Added eight Juz 30 rubu’ master records.
- Added personal memorization targets and teacher learning observations.
- Integrated matching memorization and murāja‘ah records with target status.
- Added teacher target workspace and guardian target visibility.
- Added additive automatic migration during container startup for one-upload deployment.
- Added verification, smoke test, upgrade, rollback, and database documentation.

## v1.4.5 — Portal Domain Separation

- Separated the public website and authenticated portal by hostname.
- Added canonical redirect from `www.sullamulhifz.or.id` to the root domain.
- Redirected login, dashboard, and operational routes to `app.sullamulhifz.or.id`.
- Redirected public pages requested on the portal host back to the public domain.
- Kept `taysriulqurani.id` available during the transition.
- Added feature tests, smoke test, upgrade, rollback, and Coolify guidance.
- No new database migration.

## v1.4.4 — Institution Reference

- Added a comprehensive public profile for TPA Al-Insyirah.
- Added a public adoption guide for other institutions.
- Added class, Tahfizh group, teacher, program, learning path, values, family partnership, and governance reference sections.
- Clearly separated established facts from institutional placeholders.
- Added the user-provided Ikrar Santri poster as a reference asset.
- Added config-driven institution profile overrides through institution settings.
- Added navigation, sitemap, tests, upgrade guidance, and release documentation.
- No new database migration.

## v1.4.3 — Ikrar Santri

- Added a responsive public Ikrar Santri page.
- Added an authenticated portal Ikrar Santri page for admin, teacher, and guardian accounts.
- Added an admin editor backed by `system_settings` with a safe config fallback.
- Added seven pledge points, five shared cultures, and three spaces of practice.
- Added print-friendly layouts.
- Added links from the homepage, TPA page, footer, and portal navigation.
- Added feature tests and release documentation.
- No new database migration.

## v1.4.2 — Academic Foundation Examples

- Added example documentation for institution profile, academic year, Juz 30 rubu’, marhalah, STIFIn safeguards, Community, user flow, and conceptual data dictionary.
- Added non-private example JSON data.
- No runtime or database changes.

## v1.4.1 — Documentation Sync

- Synchronized production and candidate release status.
- Updated START-HERE, current state, roadmap, documentation index, and handover.
- Archived the completed v1.3.0 plan.
- Added the v2.0.0 Academy MVP planning document.
- No database or runtime feature changes.

## v1.4.0 — TPA Operational Complete

- Added guardian management and password reset workflow.
- Added student birth place and photo support.
- Added validated CSV import preview and commit.
- Added public website CMS, articles, and admission registrations.
- Added targeted announcements, attachments, pinning, and read acknowledgement.
- Added Friday development media and worksheet fields.
- Added private liaison attachments.
- Added semester report cards with print-friendly output.
- Added expanded CSV reports.
- Added login history and operational settings.
- Added additive upgrade, smoke test, rollback, and database documentation.

## v1.3.0

- Public website and host-based portal routing.

## v2.2.0 — 2026-08-07

- Menjadikan `academy.sullamulhifz.or.id` portal LMS mandiri, bukan redirect ke website utama.
- Menambahkan navigasi Academy: program, kelas, modul, materi, video, audio, artikel, progres, rekomendasi, profil.
- Menambahkan session lintas subdomain secara otomatis ketika domain separation aktif.
- Menambahkan e-course contoh STIFIn, STIFIn Parenting, Al-Qur'an, dan Pendidikan Anak.
- Mengganti video demo Academy dengan URL video contoh yang diberikan pengelola.
- Menambahkan katalog audio yang terhubung ke pustaka Quran Learning.
- Menambahkan endpoint API preview katalog Academy tanpa data pribadi.
- Menambahkan landing staging yang hanya aktif jika `STAGING_ENABLED=true`.
- Menambah verifikasi konten Academy v2.2 pada `sullam:verify-academy`.
