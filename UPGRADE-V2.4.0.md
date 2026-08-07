# Upgrade ke v2.4.0 — Full Qur’an & Mushaf Engine

## Ringkasan
Upgrade additive dari v2.3.0. Tidak menghapus catatan pembelajaran lama dan tetap mempertahankan delapan milestone Juz 30 Sullam sebagai fitur khusus, tetapi Quran Learning sekarang dibuka untuk seluruh 30 juz.

## Sebelum deploy
1. Backup database dan volume `storage`.
2. Pertahankan `APP_KEY` lama.
3. Pastikan `AUTO_MIGRATE=true`, `BOOTSTRAP_PRODUCTION=false`, dan `QURAN_AUDIO_AUTO_SYNC=true`.

## Saat startup
- migration membuat korpus ayat dan reading progress;
- seeder release gate v2.4 ditambahkan tanpa menimpa status lama;
- web server dinyalakan tanpa menunggu sinkronisasi Quran penuh;
- korpus 30 juz disinkron di latar belakang;
- setelah korpus tersedia, timing Al-Husary dan Al-Minshawi dilanjutkan secara resume-safe.

## Setelah deploy
Buka **Admin → Pustaka Qur’an** dan **Admin → Fondasi Platform**. Jangan menandai Fase 2 selesai sebelum data menunjukkan 114 surah / 6.236 ayat / 30 juz / 604 halaman / 240 rubu‘ serta dua qari penuh, kemudian lakukan empat validasi produksi Fase 2.

## Rollback
Rollback source ke v2.3.0 dapat dilakukan tanpa menghapus tabel baru. Jangan menjalankan `migrate:rollback` pada produksi hanya untuk kembali ke UI lama.
