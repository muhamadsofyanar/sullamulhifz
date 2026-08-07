# Changelog

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
