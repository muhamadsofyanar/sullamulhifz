# Build Report v3.1.1

## Scope

Recovery patch untuk migration Guided Quran Learning v3.1.0 yang belum tercatat `Ran`.

## Perubahan inti

- Dua foreign key dengan nama otomatis lebih dari 64 karakter kini memakai nama eksplisit `guided_review_submission_fk` dan `guided_review_feedback_audio_fk`.
- Versi kandidat dinaikkan menjadi v3.1.1.
- Tidak ada perubahan destruktif pada tabel Personal v3.0.0.

## Gate

- PHP syntax check dijalankan bila runtime PHP tersedia.
- Release documentation gate harus lulus.
- Smoke test production tetap wajib setelah deploy.
