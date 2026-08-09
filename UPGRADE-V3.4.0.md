# Upgrade v3.4.0 — Personal Enrollment Lifecycle

Rilis ini additive terhadap v3.3.0 dan **tidak menambah migration baru**.

Perubahan utama:

- pilihan modul awal pada form pendaftaran Personal;
- aktivasi dan nonaktivasi modul dari Program Saya;
- Home, sidebar, navigasi bawah ponsel, dan route guard memakai status enrollment yang sama;
- histori tetap tersimpan tetapi tidak membuka kembali modul berstatus inactive;
- enrollment Guided Quran/Qur’an Journey aktif tetap menjadi guardrail program terkait;
- Academy tidak menjadi modul self-service.

## Deploy

1. Pastikan production v3.3.0 sehat dan migration `002400` berstatus `Ran`.
2. Backup database dan persistent storage.
3. Deploy source v3.4.0 melalui GitHub → Coolify satu kali.
4. Pastikan `/up` HTTP 200 serta `RELEASE` dan `public/release.txt` menampilkan `v3.4.0`.
5. Jalankan `php artisan sullam:verify-personal-program-hub` bila perlu dari terminal aplikasi.

Jangan menjalankan `migrate:fresh`, `db:wipe`, DROP manual, atau rollback database.

## Smoke check setelah deploy

- daftar akun Personal baru sambil memilih satu atau dua program;
- pastikan hanya program terpilih muncul di Beranda, sidebar, dan navigasi bawah ponsel;
- aktifkan program lain dari Program Saya dan pastikan akses URL terbuka;
- nonaktifkan Latihan Qur’an dan pastikan histori tetap ada tetapi menu/URL tidak lagi terbuka;
- pastikan Guided Quran/Qur’an Journey yang masih aktif tidak bisa disembunyikan;
- pastikan Academy hanya muncul melalui program Guided Quran yang terhubung.
