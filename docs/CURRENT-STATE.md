# Current State

## Current candidate: v3.1.0

Fokus pengembangan aktif: **Guided Quran Learning — Personal mandiri yang dapat naik ke program terarah tanpa kehilangan privasi workspace**.

v3.0.0 membuka penggunaan mandiri kepada masyarakat umum tanpa mengubah mereka menjadi anggota lembaga. Di backend setiap pengguna Personal memiliki workspace privat untuk mempertahankan isolasi tenant; di UI pengguna melihat pengalaman Personal, bukan struktur admin lembaga.

Fase 5 tetap dipertahankan utuh dari v2.7.0. Smoke test manual prerequisite → quiz → worksheet → completion → unlock → certificate → public verification telah berhasil pada produksi saat pengembangan v2.8.0 dimulai; status dashboard tetap mengikuti launch check yang tersimpan di database.

Alur positif Fase 6 sudah terbukti di produksi, tetapi uji negatif akses lintas guru/wali dan review manual guardrail STIFIn ditunda. Karena itu Fase 6 tidak dinaikkan menjadi 100% secara administratif.

Fase 7 v2.9.0 telah lolos migration, verifier, rekomendasi berbasis observasi nyata, dan smoke test teacher override `modified` di produksi. Verifier menunjukkan observasi 1, rekomendasi 1, review/override 1, serta evidence/rekomendasi memuat STIFIn 0.

Personal Mode v3.0.0 telah membuktikan pendaftaran publik, onboarding, target, jurnal, dan progres otomatis pada smoke test produksi. Kandidat v3.1.0 menambahkan alur belajar → latihan → setoran → review → perbaikan/verifikasi. v3.1.0 baru boleh dinyatakan stabil setelah migration, verifier, audio player, pembuatan program, enrollment Personal, setoran audio, review asatidz, feedback audio/teks, Academy terhubung, dan isolasi dua akun lolos smoke test produksi.

Launch penuh baru direkomendasikan setelah Fase 1–10 semuanya 100% dan release gate produksi lulus.
