# Rencana Penanganan Insiden

## Prioritas insiden

### P0 — Darurat

Contoh:

- data anak terlihat oleh akun yang salah;
- database rusak atau hilang;
- seluruh aplikasi tidak dapat diakses;
- kredensial atau secret bocor.

Tindakan:

1. Hentikan akses atau nonaktifkan fitur terdampak.
2. Simpan bukti log tanpa membagikan secret.
3. Beri tahu penanggung jawab.
4. Jalankan rollback atau restore yang sudah diuji.
5. Ganti secret yang diduga bocor.
6. Dokumentasikan akar masalah dan tindakan pencegahan.

### P1 — Tinggi

Contoh:

- guru tidak dapat mencatat pertemuan;
- wali tidak dapat login;
- audio target salah;
- upload gagal untuk banyak pengguna.

Tindakan:

- buat GitHub Issue berlabel `bug` dan `high`;
- tentukan pemilik;
- sediakan jalan alternatif operasional;
- perbaiki sebelum peluncuran penuh.

### P2 — Sedang/Rendah

Contoh:

- terjemahan tidak konsisten;
- tampilan ponsel kurang rapi;
- tombol kurang jelas.

Tindakan:

- masukkan backlog stabilisasi v1.9.x;
- tidak menghambat launch bila tidak mengganggu alur utama.

## Informasi yang tidak boleh ditempel ke issue

- password;
- `APP_KEY`;
- `DB_URL`;
- token;
- initial data key;
- nomor WhatsApp;
- email akun;
- nama dan catatan anak tanpa penyamaran.

## Rollback cepat

1. Identifikasi commit stabil terakhir.
2. Gunakan rollback Coolify atau redeploy commit stabil.
3. Jangan menjalankan migration down pada production tanpa pemeriksaan.
4. Restore database hanya bila ada kerusakan data dan backup sudah diverifikasi.
5. Pastikan domain dan healthcheck kembali normal.
