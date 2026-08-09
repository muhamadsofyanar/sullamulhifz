# Phase Registry — v4.5.0

`@phase 4.2` · `@phase 4.3` · `@phase 4.4` · `@phase 4.5`

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
| 4 | v4.5.0 | In progress | Personal 2.0: usia, minat, cita-cita, tujuan Qur’ani, empat jalur, perlindungan anak, dan portofolio privat tanpa ranking |

## Peta ketergantungan

- Fase 4.2 berdiri di lapisan website publik.
- Fase 4.3 memakai `institutions` lama sebagai workspace agar kompatibilitas modul v4.1.0 tetap terjaga.
- Fase 4.4 memakai membership Fase 4.3 untuk owner dan pengelola lembaga.
- Fase 4.5 memperluas Ruang Personal yang sudah ada; tidak membuat aplikasi, akun, atau modul profesi baru.
- `users.institution_id` belum dihapus; nilainya tetap menjadi fallback untuk modul lama, sedangkan konteks aktif ditentukan oleh session dan membership.

## Cara memeriksa

```bash
python3 scripts/check-phase-manifest.py
```

Pemeriksaan akan gagal jika file hilang, fase tidak dikenal, atau anotasi fase tidak ada.
