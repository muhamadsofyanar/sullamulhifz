# Upgrade v1.5.0 — Satu Upload dan Satu Redeploy

## Prasyarat

- Production saat ini minimal v1.4.5.
- Domain publik dan portal sudah aktif.
- Database MySQL aktif.
- Backup database berhasil dibuat.

## Cara memasang

1. Backup database dengan nama yang mudah dikenali, misalnya `pre-v1.5.0-academic-core`.
2. Ekstrak paket upgrade patch v1.5.0.
3. Salin seluruh isi patch ke root repository GitHub.
4. Commit dan push ke branch yang dipakai Coolify.
5. Lakukan satu kali Redeploy.

Tidak perlu membuka Terminal. Container v1.5.0 akan:

1. menunggu database;
2. menjalankan migration additive;
3. memasukkan master delapan rubu' Juz 30 secara idempotent;
4. memverifikasi tabel Academic Core;
5. membangun cache konfigurasi dan view;
6. menjalankan aplikasi.

## Environment variable

Nilai berikut boleh ditambahkan. Default-nya sudah `true` bila tidak ditulis.

```env
AUTO_MIGRATE=true
DB_WAIT_ATTEMPTS=30
```

Jangan mengubah `APP_KEY`, `DB_URL`, password database, atau `INITIAL_TPA_DATA_KEY`.

## Tanda deployment berhasil

Log container baru harus memuat:

```text
Academic Core siap: 8 rubu Juz 30 dan tabel target/observasi tersedia.
Menjalankan NGINX Unit...
```

Log Coolify deployment harus berakhir dengan rolling update selesai.

## Pemeriksaan

- `/dashboard`
- `/admin/institution`
- `/admin/academic-core`
- `/teacher/learning-plan` menggunakan akun guru
- profil salah satu santri
- portal salah satu wali

## Larangan

Jangan menjalankan:

```text
php artisan db:wipe
php artisan migrate:fresh
php artisan db:seed --class=InitialTpaDataSeeder
scripts/first-install.sh
```
