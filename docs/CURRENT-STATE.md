# Current State

## Current candidate: v3.3.0

Fokus pengembangan aktif: **Personal Program Hub — satu Ruang Personal dengan program modular berbasis enrollment nyata**.

v3.0.0 membuka penggunaan mandiri kepada masyarakat umum tanpa mengubah mereka menjadi anggota lembaga. Di backend setiap pengguna Personal memiliki workspace privat untuk mempertahankan isolasi tenant; di UI pengguna melihat pengalaman Personal, bukan struktur admin lembaga.

Fase 5 tetap dipertahankan utuh dari v2.7.0. Smoke test manual prerequisite → quiz → worksheet → completion → unlock → certificate → public verification telah berhasil pada produksi saat pengembangan v2.8.0 dimulai; status dashboard tetap mengikuti launch check yang tersimpan di database.

Alur positif Fase 6 sudah terbukti di produksi, tetapi uji negatif akses lintas guru/wali dan review manual guardrail STIFIn ditunda. Karena itu Fase 6 tidak dinaikkan menjadi 100% secara administratif.

Fase 7 v2.9.0 telah lolos migration, verifier, rekomendasi berbasis observasi nyata, dan smoke test teacher override `modified` di produksi. Verifier menunjukkan observasi 1, rekomendasi 1, review/override 1, serta evidence/rekomendasi memuat STIFIn 0.

Personal Mode v3.0.0 telah membuktikan pendaftaran publik, onboarding, target, jurnal, dan progres otomatis pada smoke test produksi. Fitur v3.1.0 menambahkan alur belajar → latihan → setoran → review → perbaikan/verifikasi, dan kandidat recovery v3.1.1 memperbaiki migration Guided Quran yang gagal pada deploy pertama. v3.1.1 baru boleh dinyatakan stabil setelah migration, verifier, audio player, pembuatan program, enrollment Personal, setoran audio, review asatidz, feedback audio/teks, Academy terhubung, dan isolasi dua akun lolos smoke test produksi.

v3.2.0 menambahkan progres Character/Talent non-ranking, evidence portofolio, reminder Murāja‘ah idempotent, AI Assist draft dengan human review/audit wajib, community moderation audit, dan payment ledger opsional. Fase 8–9 dapat mencapai 100% implementasi setelah migration ini berjalan, tetapi total fase tetap dibatasi oleh launch check produksi. Fase 10 sengaja tetap belum 100% sampai multi-tenant, community, integrasi eksternal, payment provider, backup/restore dan uji beban benar-benar diaktifkan serta diverifikasi.

v3.2.1 menambahkan rekening transfer resmi **BSI (Bank Syariah Indonesia) · 7350451147 · YYS INSAN QURAN MADANI** sebagai konfigurasi dan snapshot audit pada transaksi transfer manual. Patch ini tidak mengubah schema database dan tidak otomatis mengaktifkan feature flag pembayaran.

v3.3.0 menambahkan tabel enrollment modul Personal dan menjadikan Beranda/navigasi Personal dinamis. Jurnal, target, dan catatan aktivitas tetap tersedia untuk setiap akun Personal; Latihan Qur’an, Qur’an Journey, Program dengan Asatidz, dan Academy hanya tampil bila aksesnya aktif. Enrollment/histori lama dibackfill agar aktivitas nyata tidak hilang. Academy tetap mengikuti hubungan program Guided Quran, bukan dibuka bebas. Migration v3.3.0 bersifat additive.

Launch penuh baru direkomendasikan setelah Fase 1–10 semuanya 100% dan release gate produksi lulus.
