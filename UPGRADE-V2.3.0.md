# Upgrade v2.3.0 — Integrated Learning Ecosystem

## Tujuan rilis
v2.3.0 menyatukan pengalaman belajar di Academy dan menyiapkan fondasi roadmap 10 fase tanpa mengganggu operasional TPA yang sudah berjalan.

## Perubahan utama
- Audio Qur’an tidak lagi melempar pengguna dari `academy.sullamulhifz.or.id` ke `app.sullamulhifz.or.id`.
- Player, qari, preset, pengulangan, riwayat latihan, dan builder latihan tersedia langsung di Academy.
- Learning Path dapat disusun admin dari materi Academy + preset Qur’an tanpa edit kode.
- Bookmark untuk materi dan preset Qur’an.
- Refleksi privat setelah belajar.
- Program Academy dapat dikelompokkan menjadi Parent Academy, Teacher Academy, STIFIn Learning, Al-Qur’an, Pendidikan Anak, Character & Talent.
- Fondasi fase 7–10 disiapkan melalui tabel portofolio, insight, community moderasi, dan koneksi integrasi.
- Roadmap 10 fase dan feature flag dapat diatur dari Fondasi Platform.
- Seeder startup diperbaiki agar perubahan admin tidak ditimpa kembali saat restart atau redeploy.

## Migration baru
`2026_08_07_001100_integrated_learning_ecosystem_v230.php`

Migration bersifat additive. Migration menambahkan metadata Academy dan tabel baru tanpa menghapus data pembelajaran lama.

## Sebelum upgrade
1. Backup database.
2. Backup persistent volume `storage`.
3. Pastikan `APP_KEY` lama tidak berubah.
4. Jangan menjalankan `migrate:fresh` atau `db:wipe`.

## Environment Coolify
Tidak ada environment variable baru yang wajib dibanding v2.2. Pastikan nilai berikut benar:

```env
APP_ENV=production
APP_DEBUG=false
AUTO_MIGRATE=true
BOOTSTRAP_PRODUCTION=false
DOMAIN_SEPARATION_ENABLED=true
ACADEMY_HOST=academy.sullamulhifz.or.id
ACADEMY_PORTAL_URL=https://academy.sullamulhifz.or.id
SESSION_DOMAIN=.sullamulhifz.or.id
SESSION_SECURE_COOKIE=true
```

Jika `SESSION_DOMAIN` sudah bekerja pada v2.2, tidak perlu mengubahnya.

## Setelah redeploy
Buka:
- `https://app.sullamulhifz.or.id`
- `https://academy.sullamulhifz.or.id`
- `https://academy.sullamulhifz.or.id/audio`
- Admin → Fondasi Platform
- Admin → Kelola Academy

Pada log startup cari keluaran `sullam:verify-ecosystem` dan `Menjalankan NGINX Unit...`.

## Catatan feature flag
Seeder v2.3 hanya membuat nilai default bila feature flag belum ada. Setelah admin mengubah ON/OFF di aplikasi, pilihan tersebut tidak akan ditimpa oleh restart/redeploy berikutnya.

## Fase 7–10
Fase 7–10 pada v2.3 adalah fondasi, bukan klaim fitur final. Tabel, permission, feature flag, draft community, dan koneksi integrasi sudah disiapkan agar implementasi selanjutnya tidak memerlukan perubahan arsitektur besar.
