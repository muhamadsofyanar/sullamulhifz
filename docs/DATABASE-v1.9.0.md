# Database v1.9.0

Migration: `2026_08_06_000400_tpa_launch_complete_v190.php`.

## Perubahan additive

### meetings

- `meeting_type`
- `attendance_completed_at`
- `learning_completed_at`
- `summary_published_at`
- `guardian_summary`
- `closed_by_user_id`

### tahsin_records

- `fluency_status`
- `makhraj_status`
- `tajwid_status`
- `adab_status`
- `decision`

### memorization_records

- `memorization_target_id`
- `fluency_status`
- `tajwid_status`
- `error_count`
- `review_recommendation`

### murajaah_records

- `strength_status`
- `review_recommendation`

### assignments

- `assignment_type`
- `quran_audio_source_id`
- `repeat_count`
- `repeat_mode`

### assignment_submissions

- `guardian_checklist_completed`

### launch_checks

Tabel baru untuk checklist peluncuran lembaga.

Tidak ada tabel atau data lama yang dihapus pada proses `up()`.
