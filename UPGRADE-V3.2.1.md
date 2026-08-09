# Upgrade v3.2.1 — Official Bank Transfer Configuration

Patch ini additive terhadap v3.2.0 dan tidak menambah migration database.

## Perubahan

- Rekening tujuan resmi: **BSI (Bank Syariah Indonesia)**.
- Nomor rekening: **7350451147**.
- Nama rekening: **YYS INSAN QURAN MADANI**.
- `PaymentLedgerService` menyediakan alur transfer manual yang menyimpan snapshot rekening tujuan pada metadata transaksi.
- Feature flag `payments` tetap tidak diaktifkan otomatis.

## Deploy

1. Pastikan production saat ini sehat dan backup terakhir tersedia.
2. Deploy source v3.2.1 melalui pipeline yang sama.
3. Tidak perlu migration baru khusus patch ini; startup tetap boleh menjalankan mekanisme migration normal yang sudah ada.
4. Pastikan `/up` HTTP 200 dan `/release.txt` menampilkan `v3.2.1`.
5. Verifikasi tampilan/flow pembayaran hanya setelah feature flag pembayaran memang siap diuji.

## Environment opsional

Nilai resmi sudah menjadi default source. Bila rekening berubah di kemudian hari, override dengan:

```dotenv
PAYMENT_BANK_NAME="BSI (Bank Syariah Indonesia)"
PAYMENT_BANK_ACCOUNT_NAME="YYS INSAN QURAN MADANI"
PAYMENT_BANK_ACCOUNT_NUMBER=7350451147
```
