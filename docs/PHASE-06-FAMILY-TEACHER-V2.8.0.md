# Fase 6 — Family & Teacher Ecosystem — v2.8.0

## Tujuan

Membuat hubungan Parent Academy dan Teacher Academy menghasilkan tindakan nyata yang dapat ditindaklanjuti, tanpa mengubah keluarga, anak, atau guru menjadi angka/ranking.

## Alur aktivitas keluarga

1. Guru memilih santri yang benar-benar berada dalam kelas/kelompok yang diampu.
2. Guru membuat satu aktivitas keluarga yang spesifik dan dapat menghubungkannya ke materi Parent Academy.
3. Wali yang terhubung ke santri melihat aktivitas, mencoba di rumah, lalu menulis refleksi singkat.
4. Guru membaca refleksi dan dapat menulis tindak lanjut.
5. Semua akses dibatasi oleh `institution_id` dan hubungan guru/wali terhadap santri.

Status aktivitas: `assigned` → `completed` → `reviewed`. Tidak ada score, points, rating, atau rank.

## Alur kompetensi guru

1. Admin/kepala mendefinisikan kompetensi dan, bila relevan, menghubungkannya ke materi Teacher Academy.
2. Guru dapat menyimpan status `in_progress` beserta refleksi/bukti praktik naratif.
3. Guru mengirim refleksi sebagai `reflection_submitted`.
4. Reviewer lembaga memilih `demonstrated` atau `needs_follow_up` dan dapat memberi catatan.

Status kompetensi adalah status pembinaan, bukan nilai kinerja numerik dan bukan leaderboard.

## Guardrail STIFIn

- STIFIn, bila digunakan, hanya informasi tambahan atau hipotesis awal.
- Aktivitas keluarga harus memakai kebutuhan/perilaku yang dapat diamati.
- Hasil tipe tidak boleh menentukan kemampuan hafalan, kecerdasan, martabat, marhalah, atau batas perkembangan anak.
- Standar bacaan tetap dijaga; metode dan beban boleh disesuaikan berdasarkan evidence nyata.

## Release gate produksi

1. Migration `2026_08_08_001900_family_teacher_ecosystem_v280` berstatus `Ran`.
2. `php artisan sullam:verify-family-teacher` berhasil.
3. Guru membuat satu aktivitas untuk santri yang diampu.
4. Wali terkait melihat dan menyelesaikan aktivitas dengan refleksi.
5. Guru melihat refleksi dan menyelesaikan review.
6. Admin membuat satu kompetensi guru.
7. Guru menyimpan lalu mengirim refleksi kompetensi.
8. Admin mereview kompetensi tanpa skor/ranking.
9. Uji negatif: wali lain dan guru yang tidak mengampu tidak dapat mengakses/mengubah data tersebut.
10. Tinjau manual konten STIFIn dan tandai launch check `phase6_parent_teacher_flow` serta `phase6_stifin_guardrail` hanya setelah bukti produksi cukup.
