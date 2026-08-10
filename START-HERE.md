# MULAI DI SINI — v6.0.0

Rilis kandidat adalah **v6.0.0 — Gratis, Infak Sukarela & Setoran Tanpa Distraksi**.

Paket ini meneruskan v5.3.0 tanpa menghapus modul atau data yang sudah ada. Perubahan utama:

- seluruh fungsi inti gratis; pembuatan subscription baru ditutup, histori paket/invoice lama tetap terbaca;
- infak sukarela memiliki ledger, retry idempoten, verifikasi admin, dan ringkasan dana terverifikasi;
- pencatatan harian Tahfizh/Murāja‘ah disederhanakan menjadi Lanjut, Kuatkan, atau Ulang;
- Tangga Fokus memilih satu aspek pembinaan aktif; asesmen lima aspek tetap dilakukan berkala;
- ringkasan keluarga hanya ditampilkan sesuai consent `progress_summary`;
- isolasi workspace, API domain, dan katalog Academy publik diperketat;
- startup web replica tidak lagi menjalankan migration/seeder/sinkronisasi berat otomatis.

## Baca berurutan

1. `UPGRADE-V6.0.0.md`
2. `DEPLOY-QUICK-V6.0.0.txt`
3. `UPLOAD-TO-GITHUB.md`
4. `docs/releases/v6.0.0.md`
5. `docs/CURRENT-STATE.md`
6. `docs/ROADMAP.md`
7. `docs/PHASE-REGISTRY.md`

Alur paling aman: **backup → upload/push → tunggu CI hijau → jalankan migration additive sekali dengan `--isolated` → redeploy web replica → jalankan `sullam:verify-release-v600` → smoke test akun nyata**.

Jangan menjalankan `migrate:fresh`, `db:wipe`, atau seeder demo pada database produksi.


Catatan kompatibilitas: form rinci setoran/Murāja‘ah, tabel subscription, invoice, dan payment ledger lama tidak dihapus. Semuanya tetap tersedia sebagai alur khusus atau histori, tetapi langganan baru tidak dibuka pada mode v6.
