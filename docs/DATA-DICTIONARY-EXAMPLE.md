# Data Dictionary Konseptual

Dokumen ini memberi contoh struktur. Nama tabel final mengikuti source aplikasi.

## Institution

- master_brand_name
- institution_name
- tagline
- address
- phone
- email
- leader_name
- logo_path
- vision
- mission
- active_academic_year_id

## AcademicYear

- institution_id
- name
- code
- start_date
- end_date
- active_semester
- status

## ClassRoom

- academic_year_id
- level_id
- name
- code
- schedule_start
- schedule_end
- status

## LearningGroup

Dipakai untuk kelompok lintas kelas seperti Tahfizh A dan Tahfizh B.

- academic_year_id
- program_id
- name
- teacher_id
- status

## MemorizationTarget

- student_id
- academic_year_id
- learning_group_id
- rubu_id
- surah_id
- start_ayah
- end_ayah
- marhalah_type_id
- target_type
- status

## MemorizationRecord

- target_id
- meeting_id
- teacher_id
- submitted_at
- fluency_status
- tajwid_status
- mistakes
- notes
- result_status
- next_review_date

## LearningObservation

- student_id
- method_name
- context
- response
- observed_by
- observed_at
- notes

## StifinProfile

Default status: `not_tested`.

- student_id
- test_status
- profile_type
- tested_at
- source
- notes

## CommunityContent

- institution_id
- content_type
- title
- body
- audience_type
- publish_at
- expire_at
- status
- requires_acknowledgement
