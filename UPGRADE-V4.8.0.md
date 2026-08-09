# Upgrade v4.8.0 — Pendampingan Terhubung

`@phase 4.6` · `@phase 4.7` · `@phase 4.8`

## Versi asal

Upgrade ini ditujukan untuk v4.5.0. Seluruh data Personal 2.0, komunikasi, Qur’an Engine, Academy, Guru, Wali, dan lembaga dipertahankan.

## Perubahan

- Ustadz Privat: persetujuan dua pihak, batas akses milik Personal, sesi, jadwal, dan catatan Ustadz;
- Suite Lembaga: checklist kesiapan, direktori anggota, invitation ledger, penerimaan peran lintas workspace, dan suspend terisolasi;
- Portal Keluarga: hubungan anak–wali, batas akses milik anak, ringkasan progres, dan catatan dukungan privat;
- verifier produksi gabungan untuk Fase 5–7.

## Dampak database

Satu migration additive membuat tabel `mentorship_sessions` dan `family_support_notes`. Relasi, membership, dan invitation ledger memakai tabel v4.4.0 yang sudah ada. Tidak ada tabel atau kolom lama yang dihapus dan tidak ada Environment Variable baru.

## Sebelum deploy

1. Backup database MySQL dan persistent volume `storage`.
2. Pertahankan `.env`, `APP_KEY`, kredensial komunikasi, serta data produksi.
3. Salin isi paket ke root repository, lalu commit dan push satu kali.
4. Tunggu `Tests / php-tests`, `Tests / docker-build`, dan `Release Documentation Check` hijau.

## Deploy

Redeploy satu kali melalui Coolify. Startup container menjalankan migration otomatis dan verifier secara non-destruktif. Jangan menjalankan `migrate:fresh`, jangan menjalankan seeder demo, dan jangan menghapus tabel relasi lama.

## Verifikasi

Jalankan di Terminal Coolify:

```sh
php artisan migrate:status
php artisan sullam:verify-personal-v450
php artisan sullam:verify-expansion-v480
```

Kemudian uji:

1. Personal dewasa mengundang Ustadz, Ustadz menyetujui, lalu satu sesi dicatat;
2. akun anak/remaja tanpa hubungan Wali ditolak saat mengundang Ustadz;
3. anak dan Wali saling menyetujui, lalu catatan dukungan hanya terlihat oleh keduanya;
4. Admin Lembaga mengundang akun aktif sebagai Guru, akun menerima, dan workspace baru muncul;
5. suspend Guru pada satu lembaga tidak menonaktifkan Ruang Personal atau lembaga lain;
6. dashboard Guru Sofyan tetap terbuka tanpa 403;
7. jurnal pribadi dan isi portofolio tidak tampil tanpa izin eksplisit.

## Rollback

Kode dapat dikembalikan ke v4.5.0 karena migration hanya menambah dua tabel. Biarkan tabel v4.8.0 tetap ada agar histori sesi dan catatan keluarga tidak hilang. Jangan menjalankan rollback migration di produksi hanya untuk mematikan UI.
