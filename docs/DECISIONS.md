# Decision Log

Catat keputusan arsitektur atau produk yang sulit dibatalkan di sini. Setiap perubahan keputusan diberi tanggal, alasan, dan dampaknya.

## D-001 — Modular monolith sebelum microservices

- **Tanggal:** 2026-08-06
- **Status:** diterima
- **Keputusan:** website, TPA, Academy, dan Community dibangun sebagai modul dalam satu aplikasi Laravel terlebih dahulu.
- **Alasan:** tim kecil, biaya rendah, deployment sederhana, identitas terpadu.
- **Tinjau ulang ketika:** jumlah lembaga, pengguna, atau tim teknis meningkat secara signifikan.

## D-002 — Website publik menjadi rilis berikutnya

- **Tanggal:** 2026-08-06
- **Status:** diterima
- **Keputusan:** v1.3.0 mengubah `/` menjadi website publik dan mempertahankan `/login` sebagai pintu aplikasi.
- **Alasan:** domain utama saat ini langsung menuju login dan belum menjelaskan brand/produk.

## D-003 — Satu akun dapat memiliki banyak peran

- **Tanggal:** 2026-08-06
- **Status:** diterima
- **Keputusan:** user yang sama dapat menjadi admin, guru, wali, instruktur, atau peserta Academy sesuai otorisasi.
- **Alasan:** mencegah akun ganda dan mendukung ekosistem terpadu.

## D-004 — Tidak ada database wipe pada upgrade produksi

- **Tanggal:** 2026-08-06
- **Status:** diterima
- **Keputusan:** `db:wipe`, `migrate:fresh`, dan `first-install.sh` hanya untuk database kosong/instalasi pertama.
- **Alasan:** melindungi data operasional dan password pengguna.

## D-005 — Data awal privat dienkripsi

- **Tanggal:** 2026-08-06
- **Status:** diterima
- **Keputusan:** daftar nama santri tidak disimpan sebagai teks terbuka di repository; key berada di environment.
- **Alasan:** repository dan riwayat commit bukan tempat aman untuk data anak.

## D-006 — Branding resmi menjadi sumber visual tunggal

- **Tanggal:** 2026-08-06
- **Status:** diterima
- **Keputusan:** semua produk memakai logo, palet, tipografi, dan icon system pada `BRAND-GUIDE.md`.
- **Alasan:** menjaga konsistensi ekosistem.

## D-007 — Logging produksi melalui stderr

- **Tanggal:** 2026-08-06
- **Status:** diterima
- **Keputusan:** Laravel production log dibaca dari Logs aplikasi Coolify.
- **Alasan:** sesuai pola container dan menghindari ketergantungan pada file log lokal container.
