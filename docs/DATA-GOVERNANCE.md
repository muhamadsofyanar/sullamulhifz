# Data Governance dan Privasi

Sistem menyimpan data anak, wali, guru, aktivitas belajar, dan komunikasi. Perlakukan seluruhnya sebagai data sensitif meskipun beberapa nilai awal masih placeholder.

## Klasifikasi

### Rahasia

- password;
- `APP_KEY`;
- `DB_URL` dan kredensial database;
- `INITIAL_TPA_DATA_KEY`;
- dump/backup database;
- daftar akun awal lengkap;
- bukti tugas privat.

Tidak boleh masuk GitHub, issue, screenshot publik, atau chat tanpa penyamaran.

### Data pribadi

- nama santri;
- identitas wali;
- nomor telepon dan email;
- catatan belajar;
- absensi;
- komunikasi guru–wali.

Akses dibatasi berdasarkan peran dan hubungan data.

### Publik

- logo dan brand assets;
- informasi program;
- artikel publik;
- kontak lembaga yang memang disetujui untuk publikasi.

## Aturan pengembangan

- gunakan data sintetis untuk test;
- jangan menyalin database produksi ke laptop/lingkungan test tanpa perlindungan;
- jangan menampilkan nama anak pada log;
- file privat tidak boleh berada di `public/`;
- export data hanya untuk pengguna berwenang;
- setiap fitur publik baru harus menjalani review privasi.

## Data awal terenkripsi

Payload data awal berada di repository dalam bentuk terenkripsi. Key berada di Environment Variables. Enkripsi ini mengurangi paparan di repository, tetapi bukan pengganti kontrol akses repository dan keamanan backup.

## Retensi dan koreksi

Kebijakan formal retensi, penghapusan, koreksi, dan persetujuan orang tua harus dibuat sebelum platform digunakan lebih luas atau menerima lembaga lain.
