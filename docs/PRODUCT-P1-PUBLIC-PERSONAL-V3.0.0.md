# Product Track P1 — Public Personal Mode — v3.0.0

## Tujuan

Membuka manfaat Sullamul Hifz kepada individu yang mendaftar sendiri, tanpa mengharuskan pengguna masuk ke struktur TPQ/sekolah/lembaga.

## Model arsitektur

Pengguna melihat **Ruang Personal**. Backend membuat private workspace bertipe `personal` untuk mempertahankan tenant isolation yang sudah digunakan sistem lembaga. Akun Personal tidak diberi role Admin, Guru, Wali, atau Santri lembaga.

## Alur pengguna

1. Pengunjung memilih Personal pada halaman Daftar.
2. Pengguna membuat akun sendiri dan langsung mendapat ruang privat.
3. Onboarding menetapkan posisi perjalanan, fokus, ritme, serta target opsional.
4. Pengguna mencatat hafalan, Murāja‘ah, tilawah, dan refleksi sebagai self-record.
5. Dashboard menghitung aktivitas 7 hari, streak, dan progres target.
6. Arahan harian disusun dari ritme dan jurnal nyata pengguna.

## Evolusi v3.3.0 — Personal Program Hub

Ruang Personal bukan lagi sinonim dari Audio/Belajar. Jurnal, target, statistik, dan privasi adalah shell pribadi yang selalu ada. Jalur belajar berada di lapisan program dan mengikuti enrollment:

- Latihan Qur’an;
- Qur’an Journey;
- Program dengan Asatidz / Guided Quran;
- Academy yang diwariskan dari program Guided Quran terhubung.

Beranda dan navigasi hanya menampilkan program yang aktif untuk akun tersebut. Modul yang belum diikuti tetap berada di `Program Saya` sebagai pilihan dan tidak dianggap sebagai bagian dari perjalanan aktif pengguna.

## Guardrail

- self-record tidak disebut sebagai setoran/verifikasi ustadz;
- seluruh query Personal di-scope dengan `user_id` dan `institution_id`;
- workspace Personal harus `privacy_mode=private`;
- STIFIn tidak dibaca oleh `PersonalJourneyService`;
- pengguna Personal tidak mendapat menu komunikasi/administrasi lembaga;
- pendaftaran publik di-rate-limit dan password mengikuti standar akun aplikasi.

## Production gates

1. migration v3.0.0 `Ran`;
2. `sullam:verify-personal-mode` lulus;
3. pendaftaran publik berhasil;
4. onboarding, jurnal, target, dan statistik berhasil;
5. logout/login kembali mempertahankan data;
6. dua akun Personal tidak dapat saling melihat/mengubah data;
7. login dan dashboard role lembaga lama tidak regress.
8. modul yang belum di-enroll tidak muncul di Home/sidebar dan URL langsung ditolak untuk role Personal;
9. enrollment/histori Personal lama tetap mendapat akses setelah migration v3.3.0.

## Evolusi v3.4.0 — Personal Enrollment Lifecycle

1. pilihan program dapat ditetapkan saat pendaftaran publik atau dari Program Saya;
2. status enrollment eksplisit menjadi sumber visibilitas Home, sidebar, navigasi ponsel, dan akses URL;
3. modul mandiri dapat dinonaktifkan tanpa menghapus histori;
4. enrollment Guided Quran/Qur’an Journey aktif mencegah program terkait disembunyikan secara semu;
5. Academy tetap merupakan akses turunan dan tidak dapat diaktifkan bebas.

Family, Mentor, dan Enterprise menjadi track berikutnya setelah fondasi Personal stabil; roadmap fase inti tidak dinomori ulang.
