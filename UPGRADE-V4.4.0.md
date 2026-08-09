# Upgrade v4.4.0 — Fase 1–3

`@phase 4.2` · `@phase 4.3` · `@phase 4.4`

## Sifat upgrade

- Additive terhadap v4.1.0.
- Menambah tabel `workspace_memberships`, `user_relationships`, dan `workspace_invitations`.
- Menambah metadata jenis, onboarding, branding, istilah, domain, serta persetujuan pada `institutions`.
- Membackfill membership dari `user_roles` dan `users.institution_id`.
- Tidak menghapus data lembaga, Personal, pembelajaran, media, komunikasi, atau kredensial environment.

## Sebelum deploy

1. Backup MySQL dan persistent volume `storage`.
2. Pertahankan `APP_KEY` serta seluruh environment v4.1.0.
3. Pastikan source yang diunggah berisi `PHASE-MANIFEST.json`.
4. Tunggu workflow Tests dan Release Documentation Check hijau sebelum redeploy Coolify.

## Sesudah deploy

1. Pastikan migration `2026_08_09_003000_identity_relationship_multitenant_v440` berstatus Ran.
2. Masuk dengan akun lembaga lama; dashboard dan data lama harus tetap terbuka.
3. Buka Home publik dan cek empat jalur solusi.
4. Buka Hubungan Saya dan uji permintaan dengan dua akun uji.
5. Bila satu akun mempunyai dua membership, uji Ganti Ruang dan pastikan data workspace lain tidak muncul.
6. Uji `/daftar-lembaga` dengan data uji, lalu setujui melalui Admin → Ruang & Lembaga memakai akun superadmin.
7. Sebelum approval, pastikan akun onboarding hanya dapat membuka halaman status/profil; setelah approval, pastikan modul operasional terbuka.
8. Uji WhatsApp/email v4.1.0 untuk memastikan integrasi lama tetap sehat.

## Rollback aman

Kode dapat dikembalikan ke v4.1.0 hanya setelah session pengguna dibersihkan. Jangan menjalankan `migrate:rollback` di produksi hanya untuk menonaktifkan UI baru karena rollback migration menghapus ledger membership dan relationship. Jika perlu menghentikan onboarding baru, batasi route pendaftaran di layer aplikasi terlebih dahulu.
