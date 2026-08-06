# Handover untuk Chat/Akun Baru

Gunakan dokumen ini ketika riwayat percakapan tidak tersedia.

## Prompt siap pakai

Salin teks berikut ke chat baru:

```text
Saya melanjutkan proyek Sullamul Ḥifẓ dari repository GitHub.

Sebelum memberi saran atau membuat patch, pelajari berkas berikut dari repository:
1. START-HERE.md
2. docs/CURRENT-STATE.md
3. docs/ROADMAP.md
4. docs/ARCHITECTURE.md
5. docs/DECISIONS.md
6. docs/NEXT-RELEASE-v1.3.0.md
7. docs/RELEASE-STANDARD.md
8. docs/UPGRADE-STANDARD.md
9. CHANGELOG.md
10. RELEASE

Versi baseline saat ini adalah v1.2.1 documentation-governance dengan aplikasi fungsional berbasis v1.2.0. Produksi memakai Coolify, Dockerfile, NGINX Unit, PHP 8.4, Laravel 13, dan MySQL 8. Data TPA sudah berisi 88 santri, 88 wali, 4 guru, 6 kelas utama, Tahfizh A 30 santri, dan Tahfizh B 27 santri.

Larangan penting: jangan menyarankan db:wipe, migrate:fresh, atau first-install.sh untuk upgrade produksi. Jangan meminta saya mengirim APP_KEY, DB_URL, password, atau INITIAL_TPA_DATA_KEY.

Pekerjaan berikutnya adalah v1.3.0 Public Website & Route Separation, kecuali saya menyatakan prioritas lain.
```

## Berkas yang perlu diberikan ke asisten baru

Paling aman berikan link repository atau ZIP source terbaru. Jangan hanya mengirim screenshot. Jika akses repository tidak tersedia, unggah minimal:

- `START-HERE.md`;
- seluruh folder `docs/`;
- `README.md`;
- `CHANGELOG.md`;
- `RELEASE`;
- file kode yang hendak diubah.

## Informasi yang tidak boleh dikirim

- `.env`;
- data key;
- password;
- DB URL;
- APP KEY;
- dump database;
- daftar akun rahasia.

## Format permintaan upgrade

```text
Buat rilis vX.Y.Z untuk scope berikut: ...
Pertahankan seluruh data produksi.
Sertakan UPGRADE-VX.Y.Z.md, docs/releases/vX.Y.Z.md, CHANGELOG, RELEASE, public/release.txt, test, dan rollback.
Jangan memakai database wipe.
```
