# Handover untuk Chat atau Pengembang Baru

## Prompt siap salin

```text
Saya melanjutkan proyek Sullamul Ḥifẓ dari repository GitHub muhamadsofyanar/sullamulhifz.

Pelajari terlebih dahulu:
1. START-HERE.md
2. docs/CURRENT-STATE.md
3. docs/QARI-TAHFIZH-v1.6.1.md
4. UPGRADE-V1.6.1.md
5. docs/TEST-v1.6.1.md
6. docs/DATABASE-v1.6.1.md
7. docs/QURAN-LEARNING-v1.6.0.md
8. docs/ROLLBACK-v1.6.1.md
9. docs/ROADMAP.md
10. CHANGELOG.md
11. RELEASE

Fakta penting:
- website publik: sullamulhifz.or.id;
- portal: app.sullamulhifz.or.id;
- baseline sebelum paket: v1.6.0;
- paket saat ini: v1.6.1 Qari Tahfizh;
- data yang wajib dipertahankan: 88 santri, 88 wali, 4 guru, 6 kelas utama, Tahfizh A 30, Tahfizh B 27;
- target pustaka Quran Learning: 37 surah dan 564 timing per qari, total 1.128 untuk Al-Husary dan Al-Minshawi;
- angka 100% pada pustaka bukan progres santri;
- video harus dikurasi manual.

Larangan:
- jangan db:wipe, migrate:fresh, first-install.sh, atau ProductionSeeder pada produksi;
- jangan meminta atau membagikan APP_KEY, DB_URL, password, dump DB, atau data key;
- jangan mengubah startup NGINX Unit menjadi unitd langsung. Gunakan docker-entrypoint resmi;
- jangan mengklaim pengujian production yang belum dilakukan.
```

## Berkas minimum

- `START-HERE.md`;
- folder `docs/`;
- `README.md`;
- `CHANGELOG.md`;
- `RELEASE`;
- source yang akan diubah;
- log error terbaru.

## Format rilis

```text
Buat rilis vX.Y.Z untuk scope berikut: ...
Pertahankan seluruh data produksi.
Sertakan upgrade, rollback, test, dampak database, changelog, release marker, dan handover.
Gunakan migration additive dan entrypoint NGINX Unit resmi.
Bedakan source yang dibuat, hasil static test, hasil staging, dan hasil production.
```
