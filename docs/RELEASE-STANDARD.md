# Standar Rilis

Dokumen ini wajib diikuti setiap kali versi berubah.

## 1. Penomoran versi

Gunakan Semantic Versioning:

- **PATCH** `x.y.Z`: perbaikan bug, dokumentasi, atau perubahan aman tanpa fitur besar.
- **MINOR** `x.Y.0`: fitur baru yang kompatibel dengan data lama.
- **MAJOR** `X.0.0`: perubahan besar, migrasi produk, atau perubahan yang membutuhkan rencana transisi khusus.

Contoh:

- v1.2.1 — dokumentasi dan governance;
- v1.3.0 — website publik;
- v2.0.0 — Academy MVP;
- v3.0.0 — multi-lembaga.

## 2. Berkas wajib setiap rilis

Sebelum commit rilis, pastikan tersedia:

1. `CHANGELOG.md` — ringkasan perubahan.
2. `RELEASE` — versi dan identitas rilis.
3. `public/release.txt` — versi yang dapat diperiksa dari browser.
4. `UPGRADE-VERSION.md` — instruksi upgrade produksi.
5. `docs/releases/VERSION.md` — catatan lengkap rilis.
6. build report atau hasil pengujian.
7. pembaruan roadmap/current state jika arah proyek berubah.

Contoh untuk v1.3.0:

- `UPGRADE-V1.3.0.md`
- `docs/releases/v1.3.0.md`

GitHub Action `Release Documentation Check` akan gagal jika berkas utama tidak lengkap.

## 3. Isi minimum panduan upgrade

Panduan upgrade wajib menyebutkan:

- versi asal yang didukung;
- ringkasan perubahan;
- dampak database;
- environment variable baru/berubah;
- kebutuhan backup;
- langkah deployment;
- command migration/deploy;
- verifikasi setelah deploy;
- rollback;
- larangan khusus.

## 4. Alur branch sederhana

- `main` selalu versi yang boleh dideploy.
- fitur dikerjakan pada branch `feature/nama-fitur` bila menggunakan Git CLI.
- bug fix pada `fix/nama-bug`.
- merge ke `main` hanya setelah test dan dokumentasi lengkap.

Jika upload melalui website GitHub, gunakan commit yang jelas dan jangan mencampur beberapa tujuan besar dalam satu commit.

## 5. Format commit rilis

```text
Release Sullamul Hifz vX.Y.Z — nama rilis
```

Tag Git:

```text
vX.Y.Z
```

## 6. Checklist sebelum rilis

- [ ] scope sesuai roadmap;
- [ ] tidak ada secret di commit;
- [ ] migration baru aman dan reversible bila memungkinkan;
- [ ] migration lama tidak diedit;
- [ ] `php artisan test` lulus;
- [ ] release documentation check lulus;
- [ ] smoke test admin, guru, dan wali lulus;
- [ ] backup produksi dibuat;
- [ ] rollback direncanakan;
- [ ] version files konsisten.

## 7. Checklist setelah rilis

- [ ] `/release.txt` menunjukkan versi baru;
- [ ] healthcheck 200;
- [ ] login berfungsi;
- [ ] dashboard semua role berfungsi;
- [ ] halaman yang diubah diuji;
- [ ] migration status benar;
- [ ] tidak ada error baru di Logs Coolify;
- [ ] catatan hasil deploy ditambahkan ke dokumen rilis.
