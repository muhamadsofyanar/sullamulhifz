# Upgrade v2.6.0 — Qur’an Journey

## Ringkas
Upgrade ini additive. Tidak menghapus data Tahfizh, target, Murāja‘ah, Academy, atau Full Qur’an yang sudah ada.

## Deploy
1. Backup database dan persistent storage.
2. Upload **isi folder** `sullamulhifz-v2.6.0` ke root repository GitHub, menimpa source lama.
3. Commit/push ke branch `main`.
4. Redeploy satu kali di Coolify.
5. Tidak perlu mengubah environment variable untuk upgrade ini.

Startup akan menjalankan migration, seeder Qur’an Journey, sinkronisasi pembagian mushaf, verifikasi, cache, lalu menyalakan NGINX Unit.

## Setelah deploy
Buka akun guru:
- `Guru → Qur’an Journey Santri`;
- pilih satu santri;
- inisialisasi posisi Juz nyata;
- buat satu porsi Marhalah;
- uji milestone dan penjagaan;
- uji Khatam 30 Hari / Fami Bisyauqin.

Buka akun wali dan pastikan Qur’an Journey anak tampil read-only.

## Perubahan database
Migration: `2026_08_08_001400_quran_journey_v260.php`.

Menambahkan metadata Marhalah berbasis Juz, profil perjalanan, porsi Marhalah, milestone, pemeriksaan penjagaan, template/program/progres Qur’an, unit pembagian mushaf, dan istilah warisan ulama.

## Rollback
Rollback migration akan menghapus struktur Fase 4. Jangan rollback produksi tanpa backup karena data Qur’an Journey yang dibuat setelah upgrade akan ikut hilang.
