# Upgrade v1.6.0 — Quran Learning Complete

## Tujuan

Menambahkan pustaka murattal Juz 30, pemutar pengulangan ayat, latihan rentang/surah/halaman/rubu’, keterhubungan target santri, sesi latihan, dan video bacaan terkurasi.

## Keamanan upgrade

- Migration bersifat additive.
- Tidak menghapus santri, wali, guru, kelas, target, setoran, atau observasi lama.
- Jangan menjalankan `migrate:fresh`, `db:wipe`, `first-install.sh`, atau seeder data awal.
- Backup database tetap diwajibkan sebelum production redeploy.

## Satu upload dan satu redeploy

1. Salin seluruh isi upgrade patch ke root repository.
2. Pilih **Replace files in the destination** ketika Windows meminta konfirmasi.
3. Commit: `Release v1.6.0 — Quran Learning Complete`.
4. Push ke branch `main`.
5. Redeploy Coolify satu kali.

Startup otomatis menjalankan migration dan menyalakan NGINX Unit. Sinkronisasi timing audio dijalankan di latar belakang agar website tidak mengalami 502 selama pengisian pustaka.

## Environment Variables

Opsional, karena nilai bawaan sudah aman:

```env
QURAN_AUDIO_AUTO_SYNC=true
QURAN_AUDIO_SYNC_DELAY=8
```

## Verifikasi

```bash
cd /var/www/html
cat RELEASE
php artisan route:list --path=latihan-quran
php artisan route:list --path=admin/quran-library
php artisan sullam:verify-quran-learning
```

Target pustaka lengkap:

- 37 surah Juz 30;
- 564 timing ayat;
- 8 preset rubu’;
- 37 preset surah;
- preset halaman sesuai hasil timing;
- contoh An-Nās ayat 1 sebanyak 10 kali;
- contoh Al-Qāri‘ah ayat 1–5 sebanyak 10 kali per ayat.

Jika timing belum 564, buka **Pustaka Qur’an** dan klik **Sinkronkan Juz 30 sekarang**. Kegagalan API tidak menghentikan aplikasi.
