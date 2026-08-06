# Calon Rilis v1.4.2 — Academic Foundation

## Tujuan

Membuat fondasi akademik yang konsisten sebelum Academy dan multi-lembaga.

## Scope

1. Profil lembaga yang dapat diedit admin.
2. Tahun ajaran aktif dan riwayat tahun ajaran.
3. Kelas utama dan kelompok pembelajaran lintas kelas.
4. Master delapan rubu' Juz 30.
5. Marhalah hafalan.
6. Target hafalan individual.
7. Riwayat setoran dan murāja'ah yang tidak ditimpa.
8. Placeholder STIFIn dengan default Belum Dites.
9. Bank metode dan observasi respons santri.
10. Community terbatas dan Pembinaan Jumat.
11. Dokumentasi produk serta privasi.

## Tidak termasuk

- LMS Academy penuh;
- pembayaran;
- sertifikat;
- multi-lembaga;
- ranking;
- rekomendasi otomatis tanpa persetujuan guru;
- forum komunitas bebas.

## Acceptance criteria

- Admin dapat mengatur profil TPA Al-Insyirah.
- Tahun Ajaran 2026/2027 dapat dipilih sebagai konteks aktif.
- Riwayat tahun ajaran lama tidak hilang.
- Tahfizh A dan B tetap terhubung ke kelas sumbernya.
- Guru dapat memberi target per santri menggunakan rubu', surat, ayat, dan marhalah.
- Setiap setoran disimpan sebagai riwayat baru.
- Rubu' ditampilkan sebagai milestone, bukan marhalah.
- STIFIn default Belum Dites dan tidak memengaruhi kelas/nilai.
- Tidak ada ranking.
- Community dimoderasi.
- Data pribadi tidak masuk repository publik.

## Strategi implementasi

- Migration additive.
- Tidak menggunakan `db:wipe` atau `migrate:fresh` pada production.
- Seeder hanya untuk master rubu' dan marhalah, bersifat idempotent.
- Backup sebelum upgrade production.
- Uji admin, guru, dan wali.
- Siapkan rollback dokumentasi dan migration.
