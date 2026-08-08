# Fase 7 — Personal Learning System — v2.9.0

## Tujuan

Membantu guru menyusun tindak lanjut personal dari evidence belajar yang benar-benar tercatat, tanpa mengubah rekomendasi sistem menjadi keputusan otomatis atau label kemampuan anak.

## Alur

1. Guru memilih santri yang berada dalam penugasannya.
2. Guru mencatat respons nyata melalui observasi belajar, atau memakai progres Tahfizh/Murāja‘ah yang sudah tersedia.
3. Sistem membuat draf rekomendasi berbasis evidence dan menyimpan ID evidence sumber.
4. Draf berstatus `pending_review` dan belum menjadi keputusan.
5. Guru memilih `accepted`, `modified`, atau `rejected`.
6. Sistem menyimpan rekomendasi awal, rekomendasi final, keputusan, catatan review, guru dan waktu review sebagai audit.

## Guardrail

- STIFIn tidak dibaca oleh `PersonalLearningRecommendationService`.
- Tidak ada score/ranking santri pada workflow ini.
- Guru hanya dapat mengakses santri dalam class/group assignment aktif miliknya.
- Rekomendasi tanpa evidence ditolak.
- Draf tidak dapat dianggap final tanpa teacher override.
- `sullam:verify-personal-learning` gagal jika rekomendasi/evidence tersimpan memuat STIFIn.

## Production gates

Fase 7 belum boleh 100% sebelum:

1. migration v2.9.0 `Ran`;
2. command verifier lulus dengan kebocoran STIFIn = 0;
3. rekomendasi berhasil dibuat dari evidence nyata;
4. aksi Terima, Ubah dan Tolak berhasil serta tercatat;
5. guru lain tidak dapat mengakses santri/rekomendasi di luar penugasannya;
6. tampilan mobile workflow guru diperiksa.

Dua gate manual Fase 6 yang ditunda tetap pending dan harus diselesaikan terpisah.
