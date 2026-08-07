# Test Plan v2.0.0

## Smoke
`sh scripts/smoke-test-v2.0.0.sh`

## Skenario peran
- Admin: kelola Academy, Pustaka Qur’an, Launch Readiness.
- Guru: operasional harian, Quran Player, Academy Guru, rekomendasi ke wali.
- Wali: dashboard keluarga, anak, latihan Qur’an, Parent Academy, tugas, Buku Penghubung.

## Skenario isolasi data
- Wali A tidak dapat membuka anak Wali B.
- Guru hanya dapat merekomendasikan materi untuk santri pada kelas/kelompok yang diampu.
- Program lembaga lain tidak dapat diakses.

## Skenario UX
- Tidak ada overflow horizontal mobile.
- Menu dapat ditutup/dibuka.
- Bottom navigation tidak menutupi tombol submit.
- Quran Player dapat dioperasikan tanpa membuka pengaturan lanjutan.
