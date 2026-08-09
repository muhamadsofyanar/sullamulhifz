# Upgrade v4.5.0 — Personal 2.0: Setiap Orang, Setiap Cita

`@phase 4.5`

## Versi asal

Upgrade ini ditujukan untuk v4.4.4. Seluruh fitur dan data lama dipertahankan.

## Perubahan

- menambah konteks usia, minat, cita-cita, tujuan Qur’ani, dan jalur pendampingan pada Ruang Personal;
- menambah perlindungan pengguna di bawah 18 tahun;
- menambah portofolio privat memakai fondasi portofolio yang sudah ada;
- mempermanenkan perbaikan dashboard Guru dan Wali pada multi-workspace.

## Dampak database

Satu migration additive menambah enam kolom pada `personal_profiles`. Tidak ada tabel atau kolom lama yang dihapus. Tidak ada Environment Variable baru.

## Sebelum deploy

1. Backup database MySQL dan persistent volume `storage`.
2. Pertahankan `.env`, `APP_KEY`, kredensial komunikasi, dan data produksi.
3. Salin isi paket ke root repository lalu commit dan push satu kali.
4. Tunggu workflow `Tests`, `docker-build`, dan `Release Documentation Check` hijau.

## Deploy

Redeploy satu kali melalui Coolify. Startup container menjalankan migration otomatis. Jangan menjalankan seeder yang menghapus data dan jangan memakai `migrate:fresh`.

## Verifikasi

Jalankan di Terminal Coolify:

```sh
php artisan migrate:status
php artisan sullam:verify-personal-v450
```

Kemudian uji:

1. dashboard Guru Sofyan membuka `dashboard.teacher` tanpa 403;
2. satu akun Wali membuka dashboard tanpa 403;
3. akun Personal lama tetap dapat masuk;
4. akun Personal baru dapat menyimpan cita-cita dan tujuan Qur’ani;
5. pilihan usia di bawah 18 tahun ditolak tanpa persetujuan pendamping;
6. portofolio hanya terlihat oleh pemilik akun;
7. Home publik menampilkan “Setiap Orang, Setiap Cita”.

## Rollback

Kode dapat dikembalikan ke v4.4.4 karena migration hanya menambah kolom nullable/default. Biarkan kolom v4.5.0 tetap berada di database agar data profil tidak hilang. Jangan menjalankan rollback migration di produksi hanya untuk mematikan UI baru.
