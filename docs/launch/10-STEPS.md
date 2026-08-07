# Sepuluh Langkah Menuju Peluncuran

## 1. Bekukan dan verifikasi v1.9.0

**Tujuan:** memastikan source, container, database, dan dokumentasi berada pada versi yang sama.

Checklist:

- [ ] File `RELEASE` berisi `v1.9.0` atau versi stabilisasi terbaru.
- [ ] `public/release.txt` konsisten dengan `RELEASE`.
- [ ] Startup container menunjukkan versi yang sama.
- [ ] Semua migration berstatus `Ran`.
- [ ] Smoke test v1.9.0 lulus.
- [ ] Tidak ada secret atau data pribadi di commit.

Bukti minimum:

- commit SHA production;
- screenshot log startup;
- hasil smoke test;
- nama backup database.

## 2. Lengkapi identitas resmi lembaga

**Tujuan:** memastikan website, portal, laporan, dan rapor memakai identitas yang benar.

Wajib diperiksa:

- [ ] Nama lembaga: TPA Al-Insyirah.
- [ ] Master brand: Sullamul Ḥifẓ.
- [ ] Tagline: Bukan Sekadar Hafal, Tapi KUAT.
- [ ] Alamat resmi.
- [ ] Nomor kontak resmi.
- [ ] Email resmi.
- [ ] Penanggung jawab.
- [ ] Visi dan misi yang sudah disahkan.
- [ ] Logo berkualitas baik.
- [ ] Footer rapor.
- [ ] Kebijakan pendaftaran.

Data yang belum resmi harus tetap kosong atau diberi tanda “belum ditetapkan”.

## 3. Lengkapi data referensi operasional

**Tujuan:** menyiapkan seluruh master data tanpa memalsukan hasil belajar santri.

Master yang boleh diisi lengkap:

- [ ] Tahun Ajaran 2026/2027.
- [ ] Semester aktif.
- [ ] Enam kelas utama.
- [ ] Tahfizh A dan Tahfizh B.
- [ ] Penugasan Nurul, Jundi, Yanti, dan Sofyan.
- [ ] Jadwal pembelajaran.
- [ ] Delapan rubu’ Juz 30.
- [ ] Marhalah hafalan.
- [ ] Materi Tahsīn.
- [ ] Template tugas keluarga.
- [ ] Template pengumuman.
- [ ] Template Pembinaan Jumat.
- [ ] Ikrar Santri.
- [ ] Template komentar guru.
- [ ] Template rapor.

Tidak boleh dibuat fiktif:

- absensi;
- nilai;
- setoran;
- murāja‘ah;
- capaian hafalan;
- catatan individual;
- rapor resmi.

## 4. Uji alur admin secara penuh

**Tujuan:** memastikan admin dapat menjalankan seluruh operasional tanpa Terminal.

Alur minimum:

- [ ] Login admin.
- [ ] Dashboard menampilkan 88 santri, 88 wali, 4 guru, 6 kelas, dan 2 kelompok.
- [ ] Kelola santri, wali, guru, kelas, dan jadwal.
- [ ] Profil lembaga dapat disimpan.
- [ ] Academic Core dapat dibuka.
- [ ] Pustaka Qur’an dapat dibuka.
- [ ] Pengumuman dapat diterbitkan.
- [ ] Pembinaan Jumat dapat diterbitkan.
- [ ] Laporan dan rapor dapat dibuat.
- [ ] Checklist Siap Launch dapat disimpan.

## 5. Uji alur guru secara penuh

**Tujuan:** memastikan guru dapat bekerja cepat dari ponsel atau laptop.

Alur minimum:

- [ ] Login setiap akun guru.
- [ ] Guru hanya melihat kelas atau kelompok yang diampu.
- [ ] Membuka pertemuan.
- [ ] Menandai semua hadir lalu mengubah pengecualian.
- [ ] Mengisi Tahsīn.
- [ ] Mengisi Tahfizh.
- [ ] Mengisi murāja‘ah.
- [ ] Membuat target berikutnya.
- [ ] Memberikan tugas rumah.
- [ ] Mengirim catatan privat kepada wali.
- [ ] Menutup pertemuan.
- [ ] Ringkasan wali hanya terbit setelah dipublikasikan.

## 6. Uji alur wali secara penuh

**Tujuan:** memastikan wali memahami perkembangan anak tanpa melihat data keluarga lain.

Alur minimum:

- [ ] Login wali.
- [ ] Memilih anak bila memiliki lebih dari satu anak.
- [ ] Melihat kehadiran.
- [ ] Melihat materi terbaru.
- [ ] Melihat target dan setoran Tahfizh.
- [ ] Membuka Latihan Al-Qur’an dari target.
- [ ] Melihat tugas rumah.
- [ ] Mengirim checklist atau bukti sesuai izin.
- [ ] Membalas Buku Penghubung.
- [ ] Melihat rapor yang sudah diterbitkan.
- [ ] Tidak dapat membuka data anak lain.

## 7. Uji Latihan Al-Qur’an dan media

**Tujuan:** memastikan audio membantu hafalan dan tidak mengganggu pengalaman pengguna.

Wajib diuji pada Al-Husary dan Al-Minshawi:

- [ ] Satu ayat.
- [ ] Rentang ayat.
- [ ] Satu surat.
- [ ] Satu halaman.
- [ ] Satu rubu’.
- [ ] Target hafalan santri.
- [ ] Pengulangan 1×, 3×, 5×, 10×, 20×.
- [ ] Pola setiap ayat.
- [ ] Pola seluruh pilihan.
- [ ] Jeda.
- [ ] Kecepatan.
- [ ] Tombol berhenti, lanjut, dan ulang.
- [ ] Penggunaan melalui ponsel.

Periksa pula batas ukuran, privasi, dan akses untuk foto, audio, video, dan dokumen tugas.

## 8. Uji keamanan, backup, dan pemulihan

**Tujuan:** memastikan masalah akun atau server tidak menyebabkan kehilangan data.

Checklist:

- [ ] Backup database production berhasil.
- [ ] Backup file privat berhasil.
- [ ] Restore diuji pada database non-production.
- [ ] Reset password berfungsi.
- [ ] Logout semua perangkat berfungsi.
- [ ] Riwayat login terlihat.
- [ ] Rate limiting login aktif.
- [ ] Role tidak dapat membuka halaman role lain.
- [ ] File privat tidak dapat diakses tanpa izin.
- [ ] Cloudflare menggunakan Full (Strict).
- [ ] Domain lama tetap tersedia selama pilot.

## 9. Jalankan pilot terbatas

**Tujuan:** memperoleh bukti penggunaan nyata sebelum peluncuran penuh.

Peserta awal:

- 1 admin;
- 4 guru;
- 5–10 wali;
- beberapa santri Tahfizh A dan Tahfizh B.

Durasi yang disarankan: 7–14 hari.

Selama pilot, catat:

- tugas yang sulit dipahami;
- formulir yang terlalu panjang;
- tombol yang sulit ditemukan;
- audio yang tidak tepat;
- masalah login;
- laporan yang membingungkan;
- performa ponsel;
- kebutuhan panduan.

Setiap masalah dibuat sebagai GitHub Issue tanpa data pribadi.

## 10. Stabilisasi dan peluncuran

**Tujuan:** membekukan versi yang terbukti stabil.

Sebelum peluncuran:

- [ ] Semua blocker pilot ditutup.
- [ ] Semua critical dan high issue ditutup atau memiliki mitigasi tertulis.
- [ ] Smoke test terakhir lulus.
- [ ] Backup final berhasil.
- [ ] Panduan admin, guru, dan wali tersedia.
- [ ] Akun pengguna sudah diverifikasi.
- [ ] Git tag stabil dibuat.
- [ ] Release note diterbitkan.
- [ ] Rencana dukungan minggu pertama tersedia.

Format penamaan:

- Git tag: `v1.9.x-stable`
- Backup: `launch-stable-sullamul-hifz-YYYYMMDD`
- Milestone GitHub: `TPA Launch`

Setelah operasional TPA stabil, pengembangan besar berikutnya adalah v2.0.0 — Academy MVP.
