# Environment Variables

Dokumen ini mencatat nama dan fungsi, bukan nilai.

| Variable | Rahasia | Fungsi |
|---|---:|---|
| `APP_NAME` | Tidak | Nama aplikasi |
| `APP_ENV` | Tidak | Lingkungan, produksi menggunakan `production` |
| `APP_KEY` | Ya | Kunci enkripsi Laravel |
| `APP_URL` | Tidak | URL canonical produksi |
| `DB_URL` | Ya | Koneksi internal MySQL |
| `INITIAL_ADMIN_NAME` | Terbatas | Nama admin pertama |
| `INITIAL_ADMIN_EMAIL` | Terbatas | Email admin pertama |
| `INITIAL_ADMIN_PHONE` | Terbatas | Nomor admin pertama |
| `INITIAL_ADMIN_PASSWORD` | Ya | Password awal admin |
| `SEED_INITIAL_TPA_DATA` | Tidak | Mengaktifkan data awal TPA |
| `INITIAL_TPA_DATA_KEY` | Ya | Membuka payload data awal terenkripsi |
| `INITIAL_TEACHER_PASSWORD` | Ya | Password awal guru |
| `INITIAL_GUARDIAN_PASSWORD` | Ya | Password awal wali |
| `SEED_DEMO_DATA` | Tidak | Data demo; harus `false` di produksi |

## Aturan

- jangan commit nilai produksi;
- jangan menaruh key di dokumentasi;
- perubahan variable harus disebut dalam panduan upgrade versi;
- setelah mengganti variable, Save lalu redeploy/restart sesuai kebutuhan Coolify;
- password awal wajib diganti pengguna pada login pertama;
- redeploy tidak boleh mengembalikan password yang sudah diganti.
