# Backup dan Restore v1.9.0

## Waktu backup wajib

- sebelum upgrade;
- sebelum migration manual;
- sebelum impor data besar;
- sebelum pergantian tahun ajaran;
- sebelum peluncuran publik.

## Nama backup

```text
pre-v1.9.0-tpa-launch-complete
stable-v1.9.0-after-pilot
```

## Prinsip pemulihan

1. Hentikan input baru bila terjadi insiden data.
2. Catat waktu dan tindakan terakhir.
3. Rollback aplikasi terlebih dahulu bila masalah hanya pada kode.
4. Restore database hanya bila integritas data rusak.
5. Uji login, jumlah data, kelas, target, dan laporan setelah restore.
