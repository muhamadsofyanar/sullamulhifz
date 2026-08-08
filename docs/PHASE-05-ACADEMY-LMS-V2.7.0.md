# Fase 5 — Academy LMS 2.0 — v2.7.0

## Scope

Fondasi yang sudah tersedia dari v2.0–v2.6 tetap dipakai: program, modul, materi, multimedia, learning path, bookmark, refleksi, progress dan resume.

v2.7.0 menutup tiga gap implementasi Fase 5:

1. prerequisite untuk lesson dan learning path;
2. quiz/worksheet terstruktur beserta attempt/submission;
3. sertifikat penyelesaian program.

## Aturan completion

- Lesson yang memiliki prerequisite tidak dapat dibuka sebelum prerequisite selesai.
- Quiz berstatus `published` harus lulus sebelum lesson dapat ditandai selesai.
- Worksheet `published` dan `is_required=true` harus diselesaikan sebelum lesson dapat ditandai selesai.
- Sertifikat hanya diterbitkan bila seluruh lesson terbit pada program sudah berstatus `completed` untuk pengguna tersebut.

## Validasi produksi

Fase 5 belum boleh ditandai 100% hanya karena tabel dan menu tersedia. Uji alur resume/prerequisite/progress, multimedia desktop-mobile, serta quiz/worksheet/certificate end-to-end, lalu tandai tiga launch check Fase 5 sebagai `done` melalui Kesiapan Peluncuran.
