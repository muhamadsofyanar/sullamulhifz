# Build Report v3.4.0

## Scope

Personal Enrollment Lifecycle menyelaraskan pendaftaran publik, Program Saya, Beranda, navigasi desktop/mobile, dan route guard pada satu sumber enrollment.

## Implementasi

- pilihan modul awal pada pendaftaran Personal;
- aktivasi dan nonaktivasi modul mandiri dari Program Saya;
- status enrollment eksplisit mengalahkan fallback histori;
- histori tetap tersimpan ketika modul dinonaktifkan;
- navigasi bawah ponsel menampilkan maksimal dua modul aktif;
- Guided Quran/Qur’an Journey aktif menjaga modul terkait tetap tersedia;
- Academy tetap merupakan akses turunan program;
- regression test lifecycle enrollment ditambahkan.

## Safety

- tidak ada migration baru;
- tidak menghapus data lama;
- tidak mengubah akses Guru/Wali/Admin;
- konfigurasi rekening BSI v3.2.1 dipertahankan;
- payment, AI, community, dan integrasi eksternal tidak diaktifkan otomatis.

Full PHPUnit dan Laravel runtime tetap harus dijalankan pada environment PHP/Composer. Workspace penyusunan source tidak menyediakan executable PHP/Composer.
