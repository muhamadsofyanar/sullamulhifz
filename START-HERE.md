# START HERE — Sullamul Ḥifẓ

Dokumen ini adalah pintu masuk resmi proyek ketika riwayat chat hilang, pekerjaan berpindah akun, atau pengembang berganti.

## Status yang harus dibedakan

- **Produksi yang sedang berjalan:** v1.3.0 pada `taysriulqurani.id`.
- **Paket kandidat pengembangan:** v1.4.4 Ikrar Santri, berbasis fitur v1.4.0 TPA Operational Complete dan dokumentasi v1.4.1.
- **Status v1.4.x:** belum boleh dianggap production-ready sebelum pengujian staging dan upgrade terhadap salinan database selesai.
- **Domain baru yang dipersiapkan:**
  - `sullamulhifz.or.id` — website publik;
  - `app.sullamulhifz.or.id` — portal TPA;
  - `academy.sullamulhifz.or.id` — Academy pada fase v2.

## Data produksi yang harus dipertahankan

- 88 santri;
- 88 akun wali;
- 4 guru: Nurul, Jundi, Yanti, dan Sofyan;
- 6 kelas utama;
- Tahfizh A: 30 santri;
- Tahfizh B: 27 santri.

## Urutan membaca

1. `docs/CURRENT-STATE.md`
2. `docs/ROADMAP.md`
3. `docs/ARCHITECTURE.md`
4. `docs/DECISIONS.md`
5. `docs/IKRAR-SANTRI.md`
6. `docs/NEXT-RELEASE-v2.0.0.md`
7. `UPGRADE-V1.4.3.md`
8. `docs/UPGRADE-v1.4.0.md`
9. `docs/TEST-v1.4.0.md`
10. `docs/ROLLBACK-v1.4.0.md`
11. `docs/HANDOVER-NEXT-CHAT.md`

## Aturan keselamatan

- Jangan menjalankan `db:wipe`, `migrate:fresh`, atau `scripts/first-install.sh` pada database produksi yang sudah berisi data.
- Jangan menjalankan `ProductionSeeder` untuk upgrade produksi.
- Sebelum migration produksi, buat backup database dan uji pemulihannya.
- Migration lama yang pernah berjalan tidak boleh diedit; tambahkan migration baru.
- Jangan mengunggah `.env`, `APP_KEY`, `DB_URL`, password, dump database, atau `INITIAL_TPA_DATA_KEY` ke GitHub.
- Jangan memasang domain yang sama pada dua resource Coolify sekaligus.

## Sumber kebenaran

Jika chat bertentangan dengan repository, gunakan urutan berikut:

1. kode pada branch yang benar-benar dideploy;
2. status deployment Coolify dan migration database;
3. `RELEASE` dan `public/release.txt`;
4. `docs/CURRENT-STATE.md`;
5. dokumen rilis di `docs/releases/`;
6. `CHANGELOG.md`;
7. riwayat chat.


## Profil referensi lembaga

- `/lembaga/tpa-al-insyirah` menampilkan implementasi pertama.
- `/referensi-lembaga` menjelaskan cara lembaga lain mengadaptasi struktur tanpa menyalin identitas.

## Update v1.5.0 — Academic Core Complete

Rilis ini menggabungkan profil lembaga, semester aktif, delapan rubu’ Juz 30, target hafalan personal, observasi metode belajar, integrasi portal wali, serta migration otomatis additive dalam satu upload dan satu redeploy. Lihat `UPGRADE-V1.5.0.md` dan `docs/PHASES-v1.5.0.md`.
