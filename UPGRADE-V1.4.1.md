# Upgrade Dokumentasi ke v1.4.1

## Dari versi

Paket source kandidat v1.4.0.

## Dampak

- Tidak ada migration.
- Tidak ada perubahan database.
- Tidak ada seeder.
- Tidak ada perubahan fitur runtime yang disengaja.
- Perubahan hanya pada dokumentasi dan release marker.

## Langkah

1. Backup repository atau buat commit/tag sebelum perubahan.
2. Upload isi documentation patch ke root repository.
3. Commit dengan pesan:

```text
Release Sullamul Hifz v1.4.1 — documentation sync
```

4. Tidak perlu menjalankan Artisan command.
5. Bila Coolify melakukan redeploy otomatis, pastikan healthcheck tetap 200.

## Verifikasi

- `START-HERE.md` membedakan produksi v1.3.0 dan kandidat v1.4.x.
- `docs/HANDOVER-NEXT-CHAT.md` tidak lagi menyebut v1.3.0 sebagai pekerjaan berikutnya.
- `docs/NEXT-RELEASE-v2.0.0.md` tersedia.
- `RELEASE` dan `/release.txt` menunjukkan v1.4.1.

## Rollback

Revert commit v1.4.1. Tidak ada rollback database.
