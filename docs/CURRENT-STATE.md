# Current State

## Current candidate: v2.9.0

Fokus pengembangan aktif: **Fase 7 — Personal Learning System**.

v2.9.0 menambah rekomendasi personal berbasis evidence dengan teacher override tercatat. Evidence mesin dibatasi ke observasi belajar, setoran Tahfizh dan Murāja‘ah; STIFIn tidak dibaca sebagai input rekomendasi.

Fase 5 tetap dipertahankan utuh dari v2.7.0. Smoke test manual prerequisite → quiz → worksheet → completion → unlock → certificate → public verification telah berhasil pada produksi saat pengembangan v2.8.0 dimulai; status dashboard tetap mengikuti launch check yang tersimpan di database.

Alur positif Fase 6 sudah terbukti di produksi, tetapi uji negatif akses lintas guru/wali dan review manual guardrail STIFIn ditunda. Karena itu Fase 6 tidak dinaikkan menjadi 100% secara administratif.

Fase 7 baru 100% setelah migration v2.9.0 berjalan, rekomendasi benar-benar terbukti berasal dari evidence nyata, dan guru berhasil menerima, mengubah, serta menolak draf tanpa keputusan otomatis dari sistem.

Launch penuh baru direkomendasikan setelah Fase 1–10 semuanya 100% dan release gate produksi lulus.
