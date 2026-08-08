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

Family, Mentor, dan Enterprise menjadi track berikutnya setelah fondasi Personal stabil; roadmap fase inti tidak dinomori ulang.
