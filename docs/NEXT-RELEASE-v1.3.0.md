# Next Release Plan — v1.3.0 Public Website & Route Separation

Dokumen ini adalah spesifikasi kerja rilis terdekat. Jangan menambahkan Academy, pembayaran, atau pendaftaran dinamis ke rilis ini.

## Tujuan

Mengubah domain utama dari pintu login langsung menjadi website publik yang menjelaskan Sullamul Ḥifẓ, sementara aplikasi internal tetap aman di balik autentikasi.

## Route target

### Publik

- `/` — beranda publik
- `/tentang` — identitas, filosofi, dan perjalanan Qur’ani
- `/program` — ringkasan program
- `/tpa` — Sullamul Ḥifẓ untuk operasional TPA
- `/academy` — halaman pengenalan Academy, belum LMS
- `/artikel` — halaman placeholder/daftar artikel statis awal
- `/kontak` — informasi kontak
- `/login` — login aplikasi

### Internal

- `/dashboard`
- `/admin/*`
- `/guru/*`
- `/wali/*`
- `/buku-penghubung`
- `/pengumuman`
- `/pembinaan-jumat`

Semua route internal tetap memakai middleware autentikasi dan otorisasi.

## Pekerjaan kode

1. Tambahkan `resources/views/layouts/public.blade.php`.
2. Tambahkan halaman publik pada `resources/views/public/`.
3. Ubah route `/` dari redirect login menjadi controller/view publik.
4. Tambahkan navigasi publik dan tombol `Masuk Aplikasi`.
5. Pertahankan redirect setelah login ke dashboard berdasarkan peran.
6. Tambahkan metadata title, description, Open Graph, canonical URL, dan favicon.
7. Tambahkan sitemap statis dan perbarui robots.
8. Gunakan aset brand yang sudah ada di `public/brand/`.
9. Pastikan service worker tidak menyajikan halaman login lama untuk `/`.
10. Tambahkan feature tests untuk route publik dan proteksi route internal.

## Konten minimum beranda

- hero: nama, tagline, dan pernyataan manfaat;
- masalah yang dibantu diselesaikan;
- tiga jalur ekosistem: TPA, Keluarga/Orang Tua, Academy;
- pendekatan KUAT dan perjalanan Qur’ani;
- ajakan melihat program;
- tombol masuk aplikasi;
- footer kontak, privasi, dan hak cipta.

## Batas privasi

- jangan menampilkan nama santri;
- jangan menampilkan akun wali/guru;
- jangan menggunakan foto anak tanpa persetujuan tertulis;
- jangan memasukkan kunci data atau environment variable ke HTML/JavaScript;
- form kontak pada v1.3.0 dapat berupa tautan atau informasi statis; penyimpanan form ditunda ke v1.8.0.

## Dampak database

Target rilis ini **tanpa perubahan database**. Jika kebutuhan baru ternyata memerlukan tabel, buat migration baru dan perbarui panduan upgrade sebelum merge.

## Environment Variables

Tidak ada variabel wajib baru pada target awal. `APP_URL` harus tetap sesuai domain produksi.

## Pengujian wajib

- `GET /` menghasilkan 200 tanpa login;
- `GET /login` menghasilkan 200;
- pengguna anonim yang membuka `/dashboard` diarahkan ke `/login`;
- pengguna login dapat membuka dashboard;
- logout kembali ke website atau login sesuai keputusan UI;
- menu publik bekerja di desktop dan ponsel;
- logo dan warna mengikuti `BRAND-GUIDE.md`;
- tidak ada data privat di source HTML publik;
- `php artisan test` lulus.

## Panduan upgrade yang harus dibuat

Sebelum rilis, buat:

- `UPGRADE-V1.3.0.md`;
- `docs/releases/v1.3.0.md`;
- entri `CHANGELOG.md`;
- pembaruan `RELEASE` dan `public/release.txt`;
- hasil test pada build report.

Upgrade produksi tidak boleh memakai `first-install.sh`, `db:wipe`, atau `migrate:fresh`.

## Kriteria penerimaan

Rilis diterima ketika orang membuka domain utama dan melihat halaman publik, tombol Masuk membuka login, seluruh data dan fungsi aplikasi lama tetap utuh, serta tidak ada error 500 pada smoke test.
