# Upgrade v6.1.0 — Transparansi Infak, Operasional Cepat & UI/UX Mobile

## Kompatibilitas

Upgrade additive terhadap v6.0.0. Migration lama tidak diedit dan transaksi infak historis tetap tersimpan. Fitur baru dikendalikan oleh `v610_pilot`; aktifkan hanya untuk satu lembaga setelah migration dan verifier lulus.

## Sebelum deploy

1. Buat backup database dan volume privat `storage/app` yang terenkripsi.
2. Verifikasi artefak dengan `php artisan sullam:record-backup database /path/absolut/backup.sql.enc` dan command yang sama untuk `private-files`.
3. Pastikan SMTP tersedia bila notifikasi email infak akan diaktifkan.
4. Pertahankan `RUN_RELEASE_TASKS=false` pada seluruh web replica.
5. Jalankan test dan build image dari commit yang sama dengan paket deploy.

## Migration satu kali

```sh
php artisan down --render="errors::503"
php artisan migrate --isolated --force
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan sullam:verify-release-v610
php artisan up
```

## Aktivasi pilot

Aktifkan `v610_pilot` hanya pada lembaga pilot melalui Fondasi Platform. Verifikasi role: admin mencatat realisasi, akun `head` berbeda menyetujui, dan auditor hanya membaca. Setelah pilot lulus, feature flag dapat diaktifkan pada lembaga lain tanpa deploy ulang.

## Backfill transaksi v6.0

Migration membuat snapshot alokasi dan jurnal saldo pembuka untuk transaksi v6.0 yang sudah terverifikasi. Infak khusus tetap 100% pada tujuan tersimpan; Infak Umum memakai kebijakan awal 40/30/20/10. Sebelum membuka laporan publik, operator wajib mencocokkan total hasil backfill dengan transaksi dan mutasi bank. Jika ada selisih, gunakan jurnal koreksi dengan alasan dan periode sumber; jangan mengubah transaksi atau jurnal historis.

## Smoke test wajib

1. Infak umum Rp100.000 menghasilkan alokasi 40/30/20/10 dan receipt unik.
2. Infak khusus menghasilkan satu alokasi 100%.
3. Bukti transfer opsional tetap privat.
4. Maker realisasi gagal menyetujui catatannya sendiri; akun `head` dapat menyetujui.
5. Realisasi melebihi saldo ditolak dan transfer kategori menghasilkan debit-kredit seimbang.
6. Bukti asli hanya dapat dilihat pihak berwenang; publik hanya melihat versi tersamarkan yang approved.
7. Laporan publik tidak menghubungkan nama dengan nominal.
8. Arsip bulanan tidak dapat diedit.
9. Dashboard dan setoran dapat digunakan pada viewport 360px serta fokus keyboard terlihat.
10. Restore drill terpisah lulus checksum, schema, jumlah tenant, dan smoke test.

## Rollback

Matikan `v610_pilot` terlebih dahulu. Rollback kode ke v6.0.0 sambil mempertahankan tabel v6.1 agar data ledger/realisasi tidak hilang. Jangan menjalankan `migrate:rollback`, `migrate:fresh`, atau `db:wipe` pada produksi. Restore produksi hanya dilakukan operator setelah approval dan simulasi terpisah lulus.
