# Test dan Smoke Checklist

## Test otomatis

```sh
php artisan test
```

GitHub Actions wajib lulus sebelum rilis.

## Health dan versi

- [ ] `/up` menghasilkan 200.
- [ ] `/release.txt` sesuai versi rilis.
- [ ] tidak ada migration pending yang tidak dijelaskan.
- [ ] tidak ada error baru di Logs Coolify.

## Anonim/publik

- [ ] `/` berperilaku sesuai versi.
- [ ] `/login` dapat dibuka.
- [ ] route internal mengarahkan anonim ke login.
- [ ] tidak ada data pribadi di halaman publik.

## Admin

- [ ] login dan logout;
- [ ] dashboard;
- [ ] daftar santri;
- [ ] daftar guru;
- [ ] akademik dan kelompok Tahfizh;
- [ ] konten dan pembinaan;
- [ ] laporan;
- [ ] buku penghubung;
- [ ] pengumuman;
- [ ] Pembinaan Jumat;
- [ ] profil dan ganti password.

## Guru

- [ ] hanya melihat penugasan sendiri;
- [ ] pertemuan dapat dibuka;
- [ ] absensi dapat disimpan;
- [ ] Tahsin, hafalan, dan Murajaah terpisah;
- [ ] tugas dapat dibuat;
- [ ] buku penghubung hanya untuk pihak terkait.

## Wali

- [ ] hanya melihat anak terhubung;
- [ ] dapat melihat perkembangan;
- [ ] tugas dan bukti privat;
- [ ] buku penghubung;
- [ ] pengumuman dan Pembinaan Jumat;
- [ ] ganti password awal.

## Data baseline

Pada instalasi TPA Al-Insyirah:

- [ ] 88 santri aktif;
- [ ] 88 wali awal;
- [ ] 4 guru;
- [ ] 6 kelas utama;
- [ ] Tahfizh A = 30;
- [ ] Tahfizh B = 27.

## Tampilan

- [ ] desktop Chrome;
- [ ] ponsel lebar kecil;
- [ ] navigasi keyboard;
- [ ] teks tidak terpotong;
- [ ] logo tidak berubah proporsi;
- [ ] warna mengikuti brand guide.

## Regresi khusus

- [ ] nama indeks migration tidak melebihi batas MySQL;
- [ ] halaman Pembinaan Jumat tidak menghasilkan ParseError Blade;
- [ ] cache view dapat dibersihkan dan dibangun ulang;
- [ ] password yang sudah diganti tidak ditimpa seeder saat redeploy.
