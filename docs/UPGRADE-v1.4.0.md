# Upgrade v1.3.0 → v1.4.0

## Prinsip

Upgrade ini bersifat **additive**. Jangan menjalankan `db:wipe`, `migrate:fresh`, `first-install.sh`, atau seeder data awal.

## Sebelum redeploy

1. Buat backup database MySQL dari Coolify.
2. Pastikan aplikasi v1.3.0 berjalan normal.
3. Catat jumlah santri, guru, wali, kelas, dan kelompok.
4. Upload seluruh source v1.4.0 ke branch `main`.

## Setelah redeploy

Buka Terminal aplikasi dan jalankan:

```bash
cd /var/www/html
sh scripts/upgrade-v1.4.0.sh
sh scripts/smoke-test-v1.4.0.sh
```

## Verifikasi manual

- `/` membuka website publik.
- `/login` membuka portal login.
- Admin dapat membuka Santri, Guru, Wali, Akademik, Impor, Konten, Website, Rapor, dan Laporan.
- Guru dapat membuka kelas, pertemuan, absensi, Tahsin, Tahfizh, Murajaah, dan tugas.
- Wali dapat melihat anak, tugas, buku penghubung, pengumuman, Pembinaan Jumat, dan rapor terbit.

## Data yang tidak disentuh

Migration tidak menghapus atau menulis ulang 88 santri, 88 wali, empat guru, kelas utama, kelompok Tahfizh, jadwal, atau riwayat belajar.
