# Database v1.4.0

Migration: `2026_08_06_000100_upgrade_tpa_operational_v140.php`.

Tabel baru:

- `login_histories`
- `announcement_reads`
- `public_pages`
- `public_articles`
- `admission_registrations`
- `report_cards`
- `report_card_items`
- `import_batches`
- `import_rows`
- `system_settings`

Kolom tambahan:

- `students.birth_place`, `students.photo_path`
- `users.login_count`
- targeting dan lampiran pada `announcements`
- media pada `friday_development_sessions`
- lampiran privat pada `liaison_messages`

Migration tidak menghapus tabel atau data lama.
