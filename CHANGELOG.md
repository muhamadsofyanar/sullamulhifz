# Changelog

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
