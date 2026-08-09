# Upgrade ke v4.0.0

## Basis

Upgrade harus dilakukan dari source v3.4.0 atau kandidat yang sudah memuat seluruh migration sampai `002400`. Jangan mengganti repository dengan ZIP v2.6.3.

## Perubahan database

Migration `002500` menambah:

- `personal_check_ins` untuk check-in privat harian;
- kolom rekonsiliasi pada `payment_transactions`;
- memastikan feature flag `community` dan `payments` tersedia dalam keadaan OFF.

Semua perubahan additive. Histori Personal, setoran, jurnal, target, dan pembayaran lama tidak dihapus.

## Satu deploy

1. Backup database dan persistent storage.
2. Salin source v4.0.0 ke repository utama.
3. Push satu commit ke `main`.
4. Redeploy Coolify satu kali.
5. Pastikan migration berjalan dan `RELEASE` serta `public/release.txt` menampilkan `v4.0.0`.

Setelah deploy, pengaturan program, feature flag, community, dan rekonsiliasi pembayaran dilakukan dari aplikasi tanpa redeploy.

## Guardrail aktivasi

- Jangan aktifkan Community sebelum ruang, moderator, dan kebijakan siap.
- Jangan aktifkan Payments sebelum operator rekonsiliasi ditetapkan.
- Setelah feature flag aktif, admin tetap harus memberikan enrollment kepada akun Personal yang berhak.
- AI Assist, multi-institution, dan integrasi eksternal tidak dinyalakan oleh rilis ini.
