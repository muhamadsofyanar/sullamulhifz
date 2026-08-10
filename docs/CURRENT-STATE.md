# Current State

## Current candidate: v4.9.0

Fokus pengembangan aktif: **Satu Ruang Qur’an — perubahan nyata dari Home publik, Beranda Personal, timeline lintas program, sampai kendali admin**.

v3.0.0 membuka penggunaan mandiri kepada masyarakat umum tanpa mengubah mereka menjadi anggota lembaga. Di backend setiap pengguna Personal memiliki workspace privat untuk mempertahankan isolasi tenant; di UI pengguna melihat pengalaman Personal, bukan struktur admin lembaga.

Fase 5 tetap dipertahankan utuh dari v2.7.0. Smoke test manual prerequisite → quiz → worksheet → completion → unlock → certificate → public verification telah berhasil pada produksi saat pengembangan v2.8.0 dimulai; status dashboard tetap mengikuti launch check yang tersimpan di database.

Alur positif Fase 6 sudah terbukti di produksi, tetapi uji negatif akses lintas guru/wali dan review manual guardrail STIFIn ditunda. Karena itu Fase 6 tidak dinaikkan menjadi 100% secara administratif.

Fase 7 v2.9.0 telah lolos migration, verifier, rekomendasi berbasis observasi nyata, dan smoke test teacher override `modified` di produksi. Verifier menunjukkan observasi 1, rekomendasi 1, review/override 1, serta evidence/rekomendasi memuat STIFIn 0.

Personal Mode v3.0.0 telah membuktikan pendaftaran publik, onboarding, target, jurnal, dan progres otomatis pada smoke test produksi. Fitur v3.1.0 menambahkan alur belajar → latihan → setoran → review → perbaikan/verifikasi, dan kandidat recovery v3.1.1 memperbaiki migration Guided Quran yang gagal pada deploy pertama. v3.1.1 baru boleh dinyatakan stabil setelah migration, verifier, audio player, pembuatan program, enrollment Personal, setoran audio, review asatidz, feedback audio/teks, Academy terhubung, dan isolasi dua akun lolos smoke test produksi.

v3.2.0 menambahkan progres Character/Talent non-ranking, evidence portofolio, reminder Murāja‘ah idempotent, AI Assist draft dengan human review/audit wajib, community moderation audit, dan payment ledger opsional. Fase 8–9 dapat mencapai 100% implementasi setelah migration ini berjalan, tetapi total fase tetap dibatasi oleh launch check produksi. Fase 10 sengaja tetap belum 100% sampai multi-tenant, community, integrasi eksternal, payment provider, backup/restore dan uji beban benar-benar diaktifkan serta diverifikasi.

v3.2.1 menambahkan rekening transfer resmi **BSI (Bank Syariah Indonesia) · 7350451147 · YYS INSAN QURAN MADANI** sebagai konfigurasi dan snapshot audit pada transaksi transfer manual. Patch ini tidak mengubah schema database dan tidak otomatis mengaktifkan feature flag pembayaran.

v3.3.0 menambahkan tabel enrollment modul Personal dan menjadikan Beranda/navigasi Personal dinamis. Jurnal, target, dan catatan aktivitas tetap tersedia untuk setiap akun Personal; Latihan Qur’an, Qur’an Journey, Program dengan Asatidz, dan Academy hanya tampil bila aksesnya aktif. Enrollment/histori lama dibackfill agar aktivitas nyata tidak hilang. Academy tetap mengikuti hubungan program Guided Quran, bukan dibuka bebas. Migration v3.3.0 bersifat additive.

v3.4.0 menutup gap lifecycle tanpa migration baru. Pendaftar dapat memilih modul awal, Program Saya dapat mengaktifkan atau menonaktifkan modul mandiri, dan status enrollment eksplisit menjadi sumber keputusan utama. Beranda, sidebar, navigasi bawah ponsel, serta akses URL membaca keputusan yang sama. Nonaktivasi tidak menghapus histori. Program yang masih terhubung ke enrollment Guided Quran/Qur’an Journey aktif tidak dapat disembunyikan secara semu, dan Academy tetap bersifat turunan.

v4.0.0 menggabungkan sepuluh workstream menjadi satu kandidat deploy. Perubahan terlihat pada Home publik dan Beranda Personal; check-in serta Perjalanan Saya menyatukan jejak lintas program. Admin memperoleh Kendali Ekosistem untuk assignment akses, ruang community bermoderasi, dan rekonsiliasi transfer BSI. Community dan Payments tetap OFF setelah migration dan baru dapat diakses jika feature flag aktif sekaligus enrollment akun diberikan.

v4.1.0 menyelesaikan scaffold WhatsApp/email menjadi Pusat Komunikasi operasional. Provider didukung melalui StarSender atau webhook generik untuk WhatsApp, serta SMTP atau Mailketing API untuk email. API key hanya berasal dari environment Coolify. Pengiriman, retry, status, pesan masuk webhook, template, undangan akun, reset kata sandi, dan notifikasi Buku Penghubung dicatat; isi pesan disimpan terenkripsi. Kanal tetap OFF sampai admin mengaktifkannya setelah kredensial tersedia.

v4.4.0 menggabungkan Fase Produk 1–3. Home dan navigasi publik kini menerangkan Personal, Bimbingan Ustadz, Keluarga, serta Lembaga secara setara. Identity Core menambah membership lintas workspace, pemilih konteks, relationship consent, dan invitation ledger tanpa menghapus fallback `users.institution_id`. Multi-tenant Foundation menambah jenis lembaga, istilah adaptif, branding, pendaftaran, status onboarding, dan review superadmin. Seluruh file perubahan dicatat dalam Phase Registry.

v4.4.2 adalah hotfix non-database untuk kompilasi Blade Pusat Komunikasi dan kelengkapan dokumen release gate. Seluruh fungsi v4.4.0 tetap dipertahankan.

v4.5.0 menjalankan Fase 4 Product Expansion Track: **Personal 2.0 — Setiap Orang, Setiap Cita**. Profil Personal menerima kelompok usia tanpa tanggal lahir, minat, cita-cita, tujuan Qur’ani, dan empat jalur pendampingan. Beranda, rekomendasi, Perjalanan Saya, serta portofolio privat memakai konteks tersebut tanpa mengubahnya menjadi kelas profesi atau ranking. Pengguna di bawah 18 tahun memerlukan pengakuan pendampingan orang tua/wali; profil, jurnal, dan portofolio tetap privat, sedangkan Community tidak otomatis aktif. Kandidat ini juga mempermanenkan perbaikan relasi profil Guru/Wali pada dashboard multi-workspace.

v4.8.0 menggabungkan Fase 5–7 Product Expansion Track. Ustadz Privat mengaktifkan hubungan Personal–Ustadz, batas akses, dan lifecycle sesi. Suite Lembaga mengaktifkan invitation ledger, penerimaan peran lintas workspace, checklist kesiapan, serta suspend terisolasi. Portal Keluarga mengaktifkan hubungan anak–wali, kontrol batas informasi milik anak, dan catatan dukungan privat. Jurnal pribadi tidak dibuka, isi portofolio tidak dibagikan otomatis, serta pengguna di bawah 18 tahun harus mempunyai hubungan keluarga aktif sebelum bimbingan privat.

v4.9.0 menjalankan Fase 8 Product Expansion Track: **Learning & Academy Integration**. Ruang Belajar menjadi integration layer untuk Personal: modul aktif, Latihan Qur’an 30 hari, Qur’an Journey, Guided Quran/Program Asatidz, Academy yang memang terhubung, target Personal, sesi Ustadz Privat, serta tugas lembaga dari workspace aktif diringkas tanpa membuat salinan data baru. Jurnal dan isi portofolio tetap tidak ikut diringkas. Tidak ada migration baru pada v4.9.0.

Launch penuh baru direkomendasikan setelah Fase 1–10 semuanya 100% dan release gate produksi lulus.
