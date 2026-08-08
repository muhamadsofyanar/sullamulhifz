# Upgrade v2.5.2 — Tahfizh Unified Workflow

## Tujuan
Menutup gap UX Fase 3: guru tidak perlu berpindah ke Operasional Hari Ini ketika sedang mendampingi satu santri.

## Perubahan utama
- `Perjalanan Tahfizh > Detail Santri` kini memiliki **Catat Setoran Tahfizh** dan **Catat Murāja‘ah**.
- Setoran individual membuat/menghubungkan siklus, memperbarui target, membuat jadwal review bila diisi, dan menyimpan fokus koreksi.
- Murāja‘ah individual dapat memakai jadwal penjagaan yang sudah ada, menyelesaikan jadwal tersebut, membuat review berikutnya, dan memperbarui siklus.
- Audit log tetap dibuat.
- Tidak ada migration database baru.

## Deploy
1. Backup database dan persistent storage.
2. Upload isi folder proyek ke root repository GitHub.
3. Push branch `main`.
4. Redeploy Coolify.
5. Tidak perlu mengubah Environment Variables.

## Validasi Fase 3 setelah deploy
1. Login sebagai guru.
2. Buka `Perjalanan Tahfizh > salah satu santri`.
3. Pastikan tombol **Catat Setoran** dan **Catat Murāja‘ah** muncul.
4. Catat satu setoran kecil. Pastikan masuk ke Riwayat Setoran.
5. Pilih satu fokus koreksi. Pastikan muncul di Fokus Koreksi.
6. Isi tanggal Murāja‘ah berikutnya. Pastikan jadwal muncul di Jadwal Penjagaan.
7. Catat Murāja‘ah dari jadwal tersebut. Pastikan jadwal selesai dan catatan masuk ke Riwayat Murāja‘ah.
8. Uji ulang dari ponsel.

Fase 3 baru ditutup 100% setelah alur guru dan wali lulus validasi produksi.
