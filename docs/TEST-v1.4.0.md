# Checklist Pengujian v1.4.0

## Otomatis

- `php -l` seluruh file PHP.
- `php artisan migrate --force` berhasil.
- `php artisan migrate:status` seluruh migration `Ran`.
- `sh scripts/smoke-test-v1.4.0.sh` berhasil.

## Admin

- Dashboard menampilkan statistik.
- CRUD santri lama tetap dapat dibuka.
- Wali dapat dicari, diperbarui, dan password direset.
- Template CSV dapat diunduh.
- CSV salah menghasilkan preview gagal dan tidak dapat diimpor.
- CSV valid dapat dikonfirmasi.
- Pengumuman dapat ditargetkan dan meminta konfirmasi baca.
- Artikel dapat dibuat dan muncul di website.
- Form pendaftaran publik masuk ke CMS.
- Rapor dapat dibuat, disunting, diterbitkan, dan dicetak.
- Semua ekspor CSV dapat diunduh.

## Guru

- Kelas yang ditugaskan saja yang terlihat.
- Pertemuan, absensi, Tahsin, Tahfizh, Murajaah, dan tugas dapat disimpan.
- Lampiran buku penghubung hanya dapat dibuka pihak berwenang.

## Wali

- Hanya anak terhubung yang terlihat.
- Pengumuman terarah sesuai kelas/kelompok.
- Konfirmasi baca tersimpan.
- Rapor hanya terlihat setelah diterbitkan.
