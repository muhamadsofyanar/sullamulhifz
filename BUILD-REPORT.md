# Build Report — Sullamul Ḥifẓ MVP 0.1.0

Tanggal pemeriksaan: 6 Agustus 2026

## Cakupan yang selesai

- Fondasi Laravel 13 dan PHP 8.3.
- Autentikasi email/nomor telepon, pembatasan login, dan kewajiban mengganti kata sandi awal.
- Role admin lembaga, kepala TPA, guru, dan wali.
- Data santri, beberapa wali per santri, guru, tahun ajaran, jenjang, kelas, kelompok belajar, program, penugasan, dan jadwal.
- Pertemuan, absensi, tahsīn, setoran hafalan, dan murāja‘ah.
- Tugas kelas/kelompok, bukti privat, pemeriksaan guru, dan kirim ulang terkontrol.
- Buku penghubung privat.
- Pengumuman dan Pembinaan Jumat.
- Riwayat anak untuk wali dan laporan CSV dasar.
- PWA dasar, Dockerfile, NGINX Unit, seeder produksi, seeder demo, GitHub Actions, dan panduan Coolify.

## Pemeriksaan yang lulus di lingkungan pembuatan

- Seluruh file PHP lulus `php -l`.
- `composer.json`, `unit.json`, dan `manifest.webmanifest` valid sebagai JSON.
- Workflow GitHub Actions dan Docker Compose lokal valid sebagai YAML.
- `scripts/deploy.sh` lulus pemeriksaan sintaks shell.
- Seluruh referensi view eksplisit ditemukan.
- Directive Blade utama seimbang.
- Tidak ditemukan `dd()`, `dump()`, `var_dump()`, `TODO`, atau `FIXME` pada source utama.
- Berkas unggahan disimpan privat dan diakses melalui pemeriksaan hak akses.
- Hak kepala TPA dipisahkan dari hak perubahan master akademik.

## Pemeriksaan yang belum dapat dijalankan di lingkungan pembuatan

Lingkungan pembuatan tidak menyediakan Composer, Docker daemon, atau akses jaringan paket. Karena itu, langkah berikut belum dieksekusi langsung di sini:

- `composer install`;
- `php artisan migrate:fresh --seed`;
- `php artisan test`;
- `docker build`;
- uji browser end-to-end.

Repository menyertakan GitHub Actions untuk menjalankan instalasi Composer dan test suite setelah source diunggah. Build pertama di GitHub/Coolify menjadi verifikasi runtime final. Gunakan deployment staging atau domain sementara terlebih dahulu sebelum memasukkan data asli.

## Batas versi 0.1.0

- Master surah awal berfokus pada Juz 30.
- Belum ada import massal Excel.
- Belum ada reset sandi melalui email/WhatsApp; reset dilakukan admin.
- Belum ada Parent Academy/LMS, pembayaran, community terbuka, AI penilaian bacaan, atau rekomendasi otomatis STIFIn.
- Belum ada mesin milestone/rubu‘ otomatis dan quality assurance tingkat lanjut.
- Belum ada aplikasi Android/iOS native; aplikasi berupa web responsif/PWA.

## Status kesiapan

**Siap diunggah ke GitHub dan dicoba melalui Coolify sebagai MVP/staging.** Data nyata sebaiknya dimasukkan setelah build, migration, login, hak akses, unggahan, dan backup berhasil diuji pada server tujuan.
