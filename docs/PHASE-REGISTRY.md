# Phase Registry — v4.9.0

`@phase 4.2` · `@phase 4.3` · `@phase 4.4` · `@phase 4.5` · `@phase 4.6` · `@phase 4.7` · `@phase 4.8` · `@phase 4.9`

Registry ini menjadi sumber kebenaran manusia untuk mengetahui fitur dan file berasal dari fase mana. Pasangannya, `PHASE-MANIFEST.json`, digunakan oleh pemeriksaan otomatis.

## Aturan pencatatan

1. Setiap file runtime, migration, view, asset, dan test yang baru atau berubah karena roadmap wajib masuk `PHASE-MANIFEST.json`.
2. File tersebut wajib memiliki anotasi `@phase <nomor>` di komentar kode.
3. Jika file berkembang di lebih dari satu fase, semua fase dicatat; fase pertama bukan ditimpa.
4. Migration tidak diubah setelah pernah masuk produksi. Perubahan skema berikutnya memakai migration baru dengan anotasi fase baru.
5. Status fase menggunakan `planned`, `in_progress`, `completed`, atau `verified`.

## Fase yang digabung dalam rilis ini

| Fase | Versi sasaran | Status | Hasil utama |
|---|---|---|---|
| 1 | v4.2.0 | Completed | Home universal, empat jalur solusi, lima pola hubungan, fitur dan paket |
| 2 | v4.3.0 | Completed | Membership multi-workspace, context switcher, relasi berbasis persetujuan, invitation ledger |
| 3 | v4.4.0 | Completed | Jenis lembaga, istilah adaptif, branding, pendaftaran, review superadmin, onboarding, dan gerbang status operasional |
| 4 | v4.5.0 | Verified | Personal 2.0: usia, minat, cita-cita, tujuan Qur’ani, empat jalur, perlindungan anak, dan portofolio privat tanpa ranking |
| 5 | v4.6.0 | Completed | Ustadz Privat: consent, scope data, jadwal sesi, catatan Ustadz, dan guardrail pengguna minor |
| 6 | v4.7.0 | Completed | Suite Lembaga: readiness, direktori anggota, invitation ledger, penerimaan peran, dan suspend terisolasi |
| 7 | v4.8.0 | Completed | Portal Keluarga: relasi anak–wali, batas akses milik anak, ringkasan progres, dan catatan dukungan privat |
| 8 | v4.9.0 | Completed | Learning & Academy Integration: Ruang Belajar terpadu, ringkasan lintas mesin belajar, target Personal, Ustadz, Academy, dan tugas lembaga tanpa duplikasi data |

## Peta ketergantungan

- Fase 4.2 berdiri di lapisan website publik.
- Fase 4.3 memakai `institutions` lama sebagai workspace agar kompatibilitas modul v4.1.0 tetap terjaga.
- Fase 4.4 memakai membership Fase 4.3 untuk owner dan pengelola lembaga.
- Fase 4.5 memperluas Ruang Personal yang sudah ada; tidak membuat aplikasi, akun, atau modul profesi baru.
- Fase 4.6 memakai `user_relationships` sebagai consent ledger dan tidak menyalin profil Personal ke ruang Ustadz.
- Fase 4.7 mengaktifkan `workspace_invitations` serta `workspace_memberships`; status anggota selalu diubah per lembaga, bukan pada akun global.
- Fase 4.8 memakai hubungan global anak–wali agar keluarga dapat mendampingi Personal tanpa harus menjadi lembaga.
- Fase 4.9 tidak membuat storage pembelajaran baru; ia merangkum mesin yang sudah ada dan tetap mengikuti permission, consent, enrollment, serta membership aktif.
- `users.institution_id` belum dihapus; nilainya tetap menjadi fallback untuk modul lama, sedangkan konteks aktif ditentukan oleh session dan membership.

## Cara memeriksa

```bash
python3 scripts/check-phase-manifest.py
```

Pemeriksaan akan gagal jika file hilang, fase tidak dikenal, atau anotasi fase tidak ada.
