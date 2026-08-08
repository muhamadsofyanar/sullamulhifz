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
