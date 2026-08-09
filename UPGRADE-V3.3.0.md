# Upgrade v3.3.0 — Personal Program Hub

Rilis ini additive terhadap v3.2.1 dan menambah satu migration baru:

`2026_08_09_002400_personal_program_hub_v330.php`

Perubahan utama:

- membuat `personal_module_enrollments`;
- membackfill akses akun Personal yang sudah memiliki enrollment/aktivitas nyata;
- mengaktifkan feature infrastructure `quran_journey` pada workspace Personal, sementara akses user tetap dijaga oleh enrollment modul;
- mengubah Home/navigasi Personal menjadi dinamis;
- tidak menghapus atau mengubah tabel enrollment Guided Quran/Qur’an Journey yang lama;
- tidak mengubah konfigurasi rekening resmi v3.2.1.

## Deploy

1. Pastikan production v3.2.x sehat dan backup database tersedia.
2. Deploy source v3.3.0 melalui pipeline GitHub → Coolify satu kali.
3. Biarkan startup menjalankan migration normal saat `AUTO_MIGRATE=true`.
4. Pastikan `/up` HTTP 200 dan `RELEASE`/`public/release.txt` menampilkan `v3.3.0`.
5. Pastikan migration `002400` berstatus `Ran`.

Jangan menjalankan `migrate:fresh`, `db:wipe`, DROP manual, atau rollback migration Personal pada production yang sudah memiliki data pengguna.

## Smoke check setelah deploy

- akun Personal baru menampilkan jurnal/target tetapi tidak menampilkan modul yang belum diaktifkan;
- `Program Saya` dapat mengaktifkan Latihan Qur’an, Qur’an Journey, dan Program dengan Asatidz;
- modul yang aktif muncul di Home dan navigasi;
- Academy hanya muncul setelah ada Guided Quran enrollment aktif yang terhubung ke Academy;
- akun Personal lama yang sudah memiliki enrollment/aktivitas tetap mendapatkan modul terkait;
- Guru, Wali, dan Admin tetap dapat memakai route Qur’an sesuai izin lama.
