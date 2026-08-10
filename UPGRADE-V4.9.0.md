# Upgrade v4.9.0 — Ruang Belajar Terpadu

`@phase 4.9`

## Versi asal

Upgrade ini ditujukan untuk v4.8.0. Seluruh data Personal, Ustadz Privat, Portal Keluarga, Suite Lembaga, Qur’an Engine, Guided Quran, Qur’an Journey, dan Academy dipertahankan.

## Tujuan Fase 8

Fase 8 Product Expansion Track menyatukan pengalaman belajar yang sebelumnya tersebar tanpa menggandakan data dan tanpa mengubah batas akses yang sudah berlaku.

Hasil utama:

- halaman **Ruang Belajar** untuk akun Personal;
- ringkasan Latihan Qur’an, Qur’an Journey, Program Asatidz, dan Academy;
- target Personal tetap tampil sebagai target milik pengguna;
- sesi Ustadz Privat aktif/terjadwal diringkas tanpa membuka catatan privat lain;
- tugas lembaga hanya diringkas bila profil Personal terhubung ke santri dan workspace pengguna aktif pada lembaga tersebut;
- rekomendasi Academy hanya ditampilkan bila akses Academy memang aktif;
- daftar langkah berikutnya dibangun dari sumber yang sudah diizinkan, bukan dari salinan data baru;
- verifier produksi `sullam:verify-learning-hub-v490` menilai ownership dan scope integrasi.

## Dampak database

**Tidak ada migration baru.** v4.9.0 memakai tabel yang sudah tersedia dari fase sebelumnya. Deploy ini bersifat non-destruktif pada database.

## Sebelum deploy

1. Backup database MySQL dan persistent volume `storage`.
2. Pertahankan `.env`, `APP_KEY`, kredensial komunikasi, dan seluruh data produksi.
3. Salin isi paket ke root repository lalu commit/push satu kali.
4. Tunggu GitHub Actions hijau sebelum Redeploy Coolify.

## Deploy

Redeploy satu kali melalui Coolify. Startup container menjalankan verifier v4.9.0 setelah verifier v4.8.0. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau seeder demo.

## Verifikasi

Jalankan di Terminal Coolify:

```sh
php artisan migrate:status
php artisan sullam:verify-expansion-v480
php artisan sullam:verify-learning-hub-v490
```

Kemudian smoke test akun Personal:

1. buka **Ruang Belajar** dari sidebar atau Beranda Personal;
2. pastikan target Personal tampil dan target akun lain tidak tampil;
3. pastikan program yang aktif membuka mesin aslinya;
4. bila Ustadz Privat aktif, sesi terjadwal muncul tanpa membuka jurnal Personal;
5. bila Guided Quran aktif, status setoran/review tampil;
6. bila Academy aktif melalui program yang terhubung, materi dan rekomendasi bisa dibuka;
7. bila profil Personal terkait santri lembaga, hanya tugas dari workspace aktif pengguna yang diringkas.

## Rollback

Kode dapat dikembalikan ke v4.8.0 tanpa rollback database karena v4.9.0 tidak menambah atau mengubah schema.
