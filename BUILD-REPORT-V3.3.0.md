# Build Report v3.3.0

## Scope

Personal Program Hub mengubah Ruang Personal menjadi shell privat dengan program modular berbasis enrollment, bukan shell Audio Belajar.

## Implementasi

- tabel `personal_module_enrollments` additive + backfill histori nyata;
- service akses tunggal untuk Home, navigasi, dan route guard;
- `Program Saya` sebagai katalog/aktivasi modul;
- Latihan Qur’an Personal menggunakan pustaka murattal bersama secara read-only;
- Qur’an Journey dan Guided Quran mengikuti module entitlement;
- Academy hanya muncul dari Guided Quran enrollment yang terhubung;
- copy Home publik, Program, dan pendaftaran diselaraskan;
- regression test v3.3.0 ditambahkan dan test Guided Quran disesuaikan dengan gate enrollment baru.

## Safety

- tidak menghapus data lama;
- tidak mengubah migration yang sudah pernah diterapkan;
- role Guru/Wali/Admin tidak dibatasi oleh middleware Personal;
- konfigurasi payment v3.2.1 tetap dipertahankan.

Full PHPUnit dan migration runtime harus dijalankan pada environment PHP/Composer (build/production); workspace penyusunan source tidak menyediakan executable PHP/Composer.
