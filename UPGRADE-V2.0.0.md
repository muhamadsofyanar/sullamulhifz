# Upgrade v2.0.0 — Family Learning & Academy Launch

Status paket: **Launch Candidate**. Rilis baru dinyatakan stabil setelah smoke test, uji admin–guru–wali, audio, PWA, backup, dan pilot singkat selesai.

## Sebelum upgrade
1. Buat backup database `pre-v2.0.0-family-academy`.
2. Pastikan v1.9.x yang berjalan dapat diakses.
3. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau seeder instalasi awal.

## Instalasi satu kali
1. Salin isi upgrade patch ke root repository dan pilih **Replace files**.
2. Pastikan `RELEASE` berisi `v2.0.0`.
3. Commit dan push ke branch `main`.
4. Redeploy Coolify satu kali.

Startup v2.0.0 otomatis menjalankan migration additive, seeder template v1.9, seeder Academy v2.0, verifikasi, cache, dan NGINX Unit melalui entrypoint resmi.

## Environment opsional
```env
ACADEMY_ENABLED=true
ACADEMY_PUBLIC_URL=https://sullamulhifz.or.id/academy
ACADEMY_PORTAL_URL=https://app.sullamulhifz.or.id/academy/belajar
```

## Verifikasi
```bash
cd /var/www/html
cat RELEASE
sh scripts/smoke-test-v2.0.0.sh
```

Target release: `v2.0.0`. Data santri, wali, guru, kelas, target, setoran, dan riwayat lama tidak dihapus oleh migration v2.0.0.
