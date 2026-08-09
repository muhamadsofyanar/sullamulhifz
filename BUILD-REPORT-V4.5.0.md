# Build Report v4.5.0

`@phase 4.5 Personal 2.0`

## Cakupan implementasi

- profil usia, minat, cita-cita, tujuan Qur’ani, dan jalur pendampingan;
- onboarding dan pendaftaran Personal;
- Beranda kontekstual dan arahan harian generik berbasis nilai;
- Perjalanan Saya dan portofolio privat;
- perlindungan pengguna di bawah 18 tahun;
- Home publik “Setiap Orang, Setiap Cita”;
- hotfix permanen dashboard Guru/Wali;
- migration additive, verifier, dan feature test.

## Guardrail

- tidak ada tanggal lahir baru pada profil Personal;
- tidak ada ranking cita-cita atau kemampuan;
- tidak ada profil atau portofolio Personal yang dibuat publik;
- cita-cita tidak menjadi materi profesi;
- tidak ada Community otomatis untuk anak;
- fitur lama dan fallback `users.institution_id` dipertahankan.

## Release gate

Paket baru boleh dideploy setelah `php-tests`, `docker-build`, dan `release-docs` hijau. Status fase tetap `in_progress` sampai smoke test produksi selesai.

## Hotfix kandidat

- directive Blade portofolio pada `resources/views/personal/journey.blade.php` dipisahkan agar seluruh kondisi dikompilasi;
- pemeriksaan adjacency directive Blade, sintaks PHP, dokumen rilis, dan manifest fase telah dijalankan ulang;
- GitHub Actions tetap menjadi gerbang akhir sebelum deployment Coolify.

## CI hardening

- seluruh suite dijalankan pada PHP 8.4: 91 tes dan 473 assertion lulus;
- konflik properti job komunikasi dengan trait Queueable Laravel diperbaiki tanpa menonaktifkan queue;
- validasi pendaftaran Personal hanya mewajibkan persetujuan wali untuk pengguna minor;
- pengujian host publik dan portal dibuat eksplisit agar pemisahan domain benar-benar diuji;
- urutan middleware diperbaiki agar route portal pada domain publik langsung diarahkan ke portal;
- `composer.lock` diwajibkan di GitHub Actions dan Docker agar dependency tidak berubah antar-build;
- instalasi produksi `--no-dev`, bootstrap Laravel, 309 route, kompilasi Blade, 337 file PHP, JSON, dan startup shell lulus.
