# Upload Manual ke GitHub — v4.5.0

`@phase 4.2` · `@phase 4.3` · `@phase 4.4` · `@phase 4.5`

Paket ini berisi perubahan runtime, migration additive, UI, dan konfigurasi deployment. Ikuti urutan berikut agar Coolify tidak perlu diredeploy berulang kali.

## 1. Siapkan repository lokal

1. Backup repository lokal atau pastikan seluruh perubahan lama sudah di-commit.
2. Ekstrak ZIP v4.5.0.
3. Salin **isi** folder `sullamulhifz-main` ke root repository—jangan membuat folder proyek bertingkat.
4. Saat Windows menanyakan konflik, pilih replace untuk file paket ini.
5. Jangan salin `.env` produksi ke repository.
6. Folder runtime `shared`/OPcache tidak termasuk paket dan tidak boleh diunggah. Jika sudah pernah tercatat di repository, hapus folder tersebut melalui commit terpisah tanpa menyentuh `storage` produksi.

## 2. Periksa sebelum push

```bash
git status
git diff --check
git diff --stat
```

Pastikan `APP_KEY`, password database, API key StarSender, token Mailketing, token webhook, dan data pribadi tidak muncul pada diff.

## 3. Commit dan push satu kali

```bash
git add -A
git commit -m "Release Sullamul Hifz v4.5.0 — Personal 2.0"
git push origin main
```

## 4. Tunggu release gate GitHub

Jangan langsung menekan Redeploy. Tunggu workflow berikut hijau:

- `Tests` — Composer, Blade compile/lint, test suite, dan Docker production build;
- `Release Documentation Check` — konsistensi versi dan panduan upgrade.

Jika workflow merah, perbaiki source terlebih dahulu agar kegagalan tidak berpindah ke Coolify.

## 5. Siapkan Environment Variables Coolify

Pertahankan provider yang sudah dipakai sesuai `UPGRADE-V4.1.0.md`, lalu ikuti `UPGRADE-V4.5.0.md`. Pertahankan `APP_KEY` lama dan gunakan:

```env
COMMUNICATION_DISPATCH_MODE=sync
```

Biarkan toggle WhatsApp/email pada aplikasi OFF sampai tes provider siap.

## 6. Redeploy satu kali

Setelah GitHub Actions hijau dan environment tersimpan, lakukan satu kali Redeploy. Biarkan Post-deployment Command kosong; startup container sudah menjalankan migration, seeder idempoten, prune, dan cache.

Sesudah aplikasi sehat, jalankan `php artisan sullam:verify-personal-v450`, lalu uji dashboard Guru Sofyan, satu akun Wali, satu akun Personal lama, dan satu akun Personal uji baru.
