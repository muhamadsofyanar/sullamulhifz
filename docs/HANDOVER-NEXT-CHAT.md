# Handover untuk Chat atau Akun Baru

Gunakan dokumen ini ketika riwayat percakapan tidak tersedia.

## Prompt siap salin

```text
Saya melanjutkan proyek Sullamul Ḥifẓ dari repository GitHub.

Sebelum memberi saran atau membuat patch, pelajari:
1. START-HERE.md
2. docs/CURRENT-STATE.md
3. docs/ROADMAP.md
4. docs/ARCHITECTURE.md
5. docs/DECISIONS.md
6. docs/NEXT-RELEASE-v2.0.0.md
7. docs/RELEASE-STANDARD.md
8. docs/UPGRADE-STANDARD.md
9. docs/UPGRADE-v1.4.0.md
10. docs/TEST-v1.4.0.md
11. docs/ROLLBACK-v1.4.0.md
12. CHANGELOG.md
13. RELEASE

Fakta penting:
- Produksi aktif yang sudah berjalan lancar adalah v1.3.0 pada taysriulqurani.id.
- Paket kandidat repository adalah v1.4.1 Documentation Sync berbasis kandidat fitur v1.4.0 TPA Operational Complete.
- Kandidat v1.4.x belum boleh dianggap production-ready sebelum diuji pada aplikasi dan database terpisah serta diuji upgrade dari salinan database v1.3.0.
- Data yang harus dipertahankan: 88 santri, 88 wali, guru Nurul/Jundi/Yanti/Sofyan, 6 kelas utama, Tahfizh A 30 santri, Tahfizh B 27 santri.
- Target domain: sullamulhifz.or.id untuk website, app.sullamulhifz.or.id untuk portal TPA, academy.sullamulhifz.or.id untuk Academy mendatang.

Larangan:
- jangan menyarankan db:wipe, migrate:fresh, first-install.sh, atau ProductionSeeder untuk upgrade produksi;
- jangan meminta APP_KEY, DB_URL, password, dump database, atau INITIAL_TPA_DATA_KEY;
- jangan mengklaim suatu versi sudah production-ready tanpa hasil test dan deployment nyata.

Prioritas berikutnya adalah menstabilkan v1.4.x dan menyiapkan cutover domain. Setelah stabil, pengembangan besar berikutnya adalah v2.0.0 Academy MVP.
```

## Berkas minimum yang diberikan kepada asisten baru

- `START-HERE.md`;
- seluruh folder `docs/`;
- `README.md`;
- `CHANGELOG.md`;
- `RELEASE`;
- file kode yang hendak diubah;
- log error terbaru bila ada.

## Informasi yang tidak boleh dikirim

- `.env`;
- APP key;
- data key;
- password;
- DB URL;
- dump database;
- daftar akun rahasia.

## Format permintaan rilis

```text
Buat rilis vX.Y.Z untuk scope berikut: ...
Pertahankan seluruh data produksi.
Sertakan panduan upgrade, rollback, test, migration impact, changelog, release marker, dan handover.
Jangan memakai database wipe.
Bedakan fitur yang baru dibuat, yang sudah diuji, dan yang sudah benar-benar dideploy.
```
