# Release Gates

Peluncuran hanya boleh dilanjutkan bila setiap gate berstatus **PASS**.

## Gate 1 — Build dan deployment

PASS bila:

- build berhasil;
- container sehat;
- healthcheck 200;
- tidak ada error startup;
- versi source dan runtime konsisten.

## Gate 2 — Integritas data

PASS bila:

- 88 santri, 88 wali, 4 guru, 6 kelas, dan 2 kelompok tetap utuh;
- tidak ada duplikasi massal;
- migration additive;
- backup tersedia.

## Gate 3 — Hak akses dan privasi

PASS bila:

- admin, guru, dan wali hanya melihat data sesuai haknya;
- wali tidak dapat membuka anak lain;
- file privat terlindungi;
- repository bebas secret dan data pribadi.

## Gate 4 — Alur guru

PASS bila seluruh guru pilot berhasil:

- membuka pertemuan;
- mengisi absensi;
- mencatat pembelajaran;
- memberi tugas;
- menutup pertemuan;
- mempublikasikan ringkasan.

## Gate 5 — Alur wali

PASS bila seluruh wali pilot berhasil:

- login;
- memilih anak;
- melihat perkembangan;
- membuka latihan audio;
- menyelesaikan tugas;
- menggunakan Buku Penghubung.

## Gate 6 — Latihan Al-Qur’an

PASS bila:

- Al-Husary dan Al-Minshawi dapat diputar;
- pengulangan ayat berjalan;
- satu surat, halaman, dan rubu’ berjalan;
- tidak ada audio yang jelas salah qari atau salah surat pada sampel uji.

## Gate 7 — Laporan dan rapor

PASS bila:

- ringkasan bulanan sesuai data;
- rapor dapat diterbitkan dan dicetak;
- tidak ada ranking;
- tidak ada data fiktif pada rapor resmi.

## Gate 8 — Backup dan recovery

PASS bila:

- backup database tersedia;
- storage privat tercadangkan;
- restore pernah diuji di non-production;
- prosedur rollback dapat dijalankan.

## Gate 9 — Pilot

PASS bila:

- pilot 7–14 hari selesai;
- tidak ada blocker atau critical terbuka;
- high issue memiliki penyelesaian atau mitigasi tertulis.

## Gate 10 — Keputusan peluncuran

Keputusan:

- **GO:** semua gate PASS.
- **CONDITIONAL GO:** hanya masalah medium/low, dengan pemilik dan tenggat jelas.
- **NO-GO:** ada blocker, critical, masalah akses, kehilangan data, backup belum teruji, atau alur utama gagal.
