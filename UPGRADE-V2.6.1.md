# Upgrade v2.6.1 — Mushaf Line Engine

v2.6.1 menyempurnakan Fase 4 khusus Marhalah **Tsalātsiyyah (3 baris)** dan **Khamsiyyah (5 baris)** agar porsi mengikuti slot fisik Mushaf Madinah, bukan perkiraan jumlah ayat.

## Perubahan inti

- Juz 29 / Tsalātsiyyah: blok tetap 1–3, 4–6, 7–9, 10–12, 13–15.
- Juz 28 / Khamsiyyah: blok tetap 1–5, 6–10, 11–15.
- Nama surah dan basmalah yang menempati slot halaman tetap dihormati sebagai posisi fisik layout.
- Batas porsi disimpan hingga `start_word_location` dan `end_word_location`, sehingga porsi boleh mulai/berakhir di tengah ayat tanpa kehilangan batas Mushaf.
- Target Tahfizh menyimpan halaman, baris, dan batas kata; tampilan Tahfizh tetap memakai ayat untuk navigasi tetapi tidak lagi menjadi satu-satunya sumber batas.
- Blok yang menyentuh batas Juz tidak dibuat otomatis.
- Satu target aktif diprioritaskan otomatis pada Perjalanan Tahfizh.
- Statistik membedakan `Hafalan selesai` dan `Terjaga`.

## Deployment

Tidak perlu menambah Environment baru bila memakai nilai default. Setelah upload source dan redeploy Coolify, migration otomatis membuat tabel/kolom v2.6.1. Sinkronisasi layout Mushaf berjalan di background dan halaman yang dibuka guru juga dicoba secara on-demand.

Variabel opsional tersedia di `.env.example`: `MUSHAF_LINE_AUTO_SYNC`, `MUSHAF_LINE_SYNC_DELAY`, `MUSHAF_LINE_LAYOUT`, serta URL adapter layout.

## Validasi setelah deploy

Buka Qur’an Journey santri yang berada di Juz 29. Halaman harus menampilkan selector halaman dan lima blok 3 baris. Pastikan satu blok yang mengandung header/basmalah tetap mempertahankan nomor slot fisiknya. Buat satu target, lalu cek Perjalanan Tahfizh: target harus menampilkan halaman/baris dan terhubung ke siklus/setoran.

Fase 4 belum 100% hanya karena deploy berhasil. Launch check `phase4_mushaf_line_blocks` tetap harus divalidasi pada produksi.
