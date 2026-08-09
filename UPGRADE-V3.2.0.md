# Upgrade v3.2.0 — Roadmap Completion Foundations

Upgrade ini additive terhadap v3.1.1. Tidak ada tabel Guided Quran, Personal, Tahfizh, Academy, atau data lembaga yang dihapus.

## Sebelum deploy

1. Backup database dan persistent volume `storage`.
2. Pastikan production sudah berada pada v3.1.1 dan migration `002200` berstatus `Ran`.
3. Jangan menjalankan `migrate:fresh`, `db:wipe`, DROP manual, atau cleanup migration.

## Migration baru

`2026_08_09_002300_roadmap_completion_foundations_v320.php`

Migration menambah:
- `talent_progress_records`
- `student_portfolio_evidence`
- `ai_assist_drafts`
- `ai_assist_reviews`
- `community_moderation_actions`
- `payment_transactions`
- kolom `memorization_review_plans.reminder_sent_at`

## Setelah deploy

1. Pastikan `/up` HTTP 200.
2. Pastikan migration `002300` berstatus `Ran`.
3. Jalankan `php artisan sullam:verify-guided-quran`.
4. Jalankan `php artisan sullam:verify-roadmap-foundations-v320`.
5. Uji satu progres bakat + evidence portofolio, satu reminder Murāja‘ah, dan satu AI draft → human review.
6. Jangan aktifkan feature flag Fase 10 sebelum tenant isolation, policy moderasi, credential integrasi dan prosedur payment siap.

## Catatan roadmap

Fase 8 dan 9 dapat mencapai 100% pada kolom Implementasi setelah migration berjalan. Kolom Validasi tetap mengikuti Launch Check produksi. Fase 10 sengaja tetap parsial sampai fitur eksternal benar-benar diaktifkan dan diuji.
