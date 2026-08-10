# Upgrade v6.0.0 — Gratis, Infak Sukarela & Setoran Tanpa Distraksi

`@phase 6.0 Free, Infaq & Distraction-Free Tahfizh`

## Jaminan kompatibilitas

Upgrade ini additive terhadap v5.3.0. Data lembaga, pengguna, santri, pembelajaran, media, komunikasi, Academy, Personal, relasi keluarga/ustadz, subscription, invoice, dan payment ledger lama tidak dihapus. Form setoran dan Murāja‘ah rinci tetap tersedia di bagian **Pencatatan rinci** untuk asesmen atau kasus khusus.

## Perubahan utama

- fungsi inti tidak lagi bergantung pada subscription;
- `SUBSCRIPTIONS_ENABLED=false` menutup pembuatan invoice langganan baru tanpa menghapus histori;
- infak sukarela memakai ledger terpisah dan tidak mengubah entitlement;
- setoran harian memakai Lanjut, Kuatkan, atau Ulang, lalu membuat jadwal review otomatis;
- satu Tangga Fokus aktif membantu ustadz memilih prioritas pembinaan;
- asesmen lima aspek dilakukan berkala, bukan di setiap setoran;
- ringkasan keluarga mengikuti consent `progress_summary`;
- perubahan status wali hanya memengaruhi membership/role pada workspace terkait;
- `/api/*` hanya tersedia pada `API_HOST` ketika domain separation aktif;
- Academy Preview hanya membaca lembaga yang mengaktifkan `public_academy`;
- web replica tidak menjalankan migration, seeder, verifier, atau sinkronisasi korpus otomatis.

## Dampak database

Migration additive: `2026_08_10_007000_free_infaq_distraction_free_v600.php`.

Migration menambah tiga kolom nullable pada `memorization_records` dan `murajaah_records`, lalu membuat `student_memorization_focuses`, `student_memorization_assessments`, dan `infaq_transactions`. Migration `up()` tidak memiliki drop, truncate, atau reset data.

## Sebelum deploy

1. Backup database MySQL dan volume persistent `storage`.
2. Simpan bukti backup dan pastikan prosedur restore tersedia.
3. Pertahankan `.env`, `APP_KEY`, rekening resmi, dan credential komunikasi.
4. Set `SUBSCRIPTIONS_ENABLED=false`, `RUN_RELEASE_TASKS=false`, `QURAN_AUDIO_AUTO_SYNC=false`, dan `MUSHAF_LINE_AUTO_SYNC=false` pada web replica.
5. Tunggu seluruh GitHub Actions hijau sebelum mengganti container produksi.

## Migration satu kali

Jalankan dari satu release job atau satu terminal operator:

```sh
php artisan down --render="errors::503"
php artisan migrate --isolated --force
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan up
php artisan sullam:verify-release-v600
```

Jika deployment memakai rolling update dan migration telah dipastikan backward-compatible, maintenance mode dapat disesuaikan dengan prosedur infrastruktur. Jangan menyalakan `RUN_RELEASE_TASKS=true` pada lebih dari satu web replica.

## Smoke test wajib

1. Ustadz mencatat satu setoran cepat untuk masing-masing keputusan Lanjut, Kuatkan, dan Ulang; pastikan target anak dipilih otomatis, antrean berpindah ke anak berikutnya, dan jadwal Murāja‘ah terbentuk.
2. Retry request dengan kunci yang sama tidak membuat record ganda.
3. Buka pencatatan rinci lama dan pastikan masih dapat digunakan.
4. Ubah Tangga Fokus dan simpan satu asesmen berkala lima aspek.
5. Wali dengan consent `progress_summary` melihat ringkasan; wali tanpa consent tidak melihatnya.
6. Dua workspace pada akun wali: menonaktifkan satu membership tidak menonaktifkan akun/workspace lain.
7. Catat infak, retry, verifikasi admin, dan pastikan akses sebelum/sesudah infak identik.
8. `/api/v1/meta` gagal pada domain publik/portal dan berhasil pada domain API.
9. Academy Preview tidak menampilkan lembaga tanpa opt-in publik.

## Rollback

Utamakan rollback kode dengan tetap mempertahankan tabel/kolom v6 setelah backup. Jangan menjalankan `migrate:rollback`, `migrate:fresh`, atau `db:wipe` pada produksi tanpa inspeksi dan rencana pemulihan data. Menonaktifkan rute/UI v6 tidak mengharuskan penghapusan data v6.
