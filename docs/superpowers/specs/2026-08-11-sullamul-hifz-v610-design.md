# Sullamul Hifz v6.1.0 Design

## Tujuan

v6.1.0 menggabungkan stabilisasi produksi, tata kelola infak transparan, dan peningkatan pengalaman operasional pada baseline v6.0.0. Fungsi inti tetap gratis, semua perubahan skema bersifat additive, dan fitur baru dapat dipilotkan per lembaga melalui feature flag `v610_pilot`.

## Batas rilis

- PHP 8.4 dan Laravel 13 tetap dipertahankan tanpa frontend framework baru.
- Migration v6.0.0 tidak diubah; v6.1.0 memakai migration baru.
- Infak tidak pernah mengubah entitlement pengguna.
- Infak khusus 100% mengikuti tujuan pemberi.
- Infak umum menggunakan kebijakan bawaan 40% ustadz, 30% yayasan, 20% teknologi, dan 10% beasiswa.
- Kebijakan dapat diubah admin lembaga dengan total tepat 100%; versi baru hanya berlaku prospektif.
- Realisasi tidak dapat membuat saldo kategori negatif.
- Admin mencatat realisasi dan Penanggung Jawab Lembaga memverifikasi; pembuat tidak boleh memverifikasi catatannya sendiri.
- Dokumen asli privat; versi publik harus disamarkan dan disetujui sebelum tampil.
- Identitas pemberi anonim secara bawaan dan nama publik tidak dihubungkan dengan nominal.
- Laporan bulanan terkunci; koreksi dilakukan melalui jurnal pada periode berikutnya.
- Restore produksi tidak dilakukan melalui request web.

## Arsitektur

Modular monolith tetap digunakan. Modul `Infaq` diperluas melalui service terfokus: kebijakan alokasi, ledger, realisasi, laporan, dan receipt. Semua mutasi finansial berjalan dalam transaksi database dengan row lock. Saldo bukan kolom yang dapat diedit, melainkan agregat ledger append-only.

UI memakai Blade dan CSS berlapis yang sudah ada. v6.1 menambahkan stylesheet tersendiri, komponen status dan navigasi berkelompok, tanpa mengubah aset historis. Halaman operasional menempatkan tugas mendesak dan aksi utama di atas, mengurangi kepadatan sidebar, dan mempertahankan bottom navigation khusus peran pada ponsel.

## Model data

- `infaq_allocation_policies` dan `infaq_allocation_policy_items`: versi persentase per lembaga dengan tanggal berlaku.
- `infaq_allocations`: snapshot alokasi setiap transaksi yang sudah diverifikasi.
- `infaq_ledger_entries`: kredit penerimaan, debit realisasi, transfer keluar/masuk, dan koreksi.
- `infaq_realisations`: draft, submitted, verified, rejected, dengan pemisahan pembuat dan pemeriksa.
- `infaq_evidences`: referensi MediaAsset asli dan publik serta status pemeriksaan penyamaran.
- `infaq_transfers`: pemindahan saldo antarkategori yang memerlukan persetujuan.
- `infaq_monthly_reports`: snapshot agregat yang dapat dikunci dan tidak dibuka kembali.
- `infaq_receipt_sequences`: nomor bukti unik per lembaga dan tahun.
- `backup_runs` dan `restore_requests`: manifest operasional dan approval, bukan eksekusi restore melalui web.
- Kolom tambahan transaksi: consent nama publik, bukti transfer privat, catatan pencocokan mutasi, dan waktu consent.

## Alur transaksi

Pemberi mencatat tujuan, nominal, pilihan penayangan nama, dan bukti transfer opsional. Admin memeriksa mutasi rekening. Saat diverifikasi, sistem membuat nomor bukti dan snapshot alokasi dalam satu transaksi. Infak khusus menghasilkan satu kredit pada kategori tujuannya; infak umum menghasilkan empat kredit berdasarkan kebijakan aktif dan penyesuaian pembulatan pada komponen terakhir.

## Alur realisasi

Admin membuat draft realisasi pada satu kategori dan wajib mengunggah kuitansi/faktur atau surat pertanggungjawaban. Versi publik yang sudah disamarkan diunggah terpisah. Setelah diajukan, Penanggung Jawab dapat menyetujui atau menolak dengan alasan. Persetujuan membuat debit ledger; penolakan mengembalikan catatan untuk diperbaiki. Bukti publik hanya tersedia untuk realisasi terverifikasi.

## Hak akses

- `infaq.view_own`: riwayat dan receipt milik sendiri.
- `infaq.verify`: pencocokan mutasi dan verifikasi penerimaan.
- `infaq.policy.manage`: kebijakan alokasi.
- `infaq.realisation.manage`: pencatatan realisasi.
- `infaq.realisation.approve`: persetujuan realisasi dan transfer.
- `infaq.audit.view`: akses auditor read-only ke transaksi dan bukti asli.
- `infaq.report.manage`: penguncian laporan.
- `backup.manage`: permintaan serta pencatatan backup/restore untuk superadmin.

Superadmin memiliki seluruh izin. Admin lembaga mengelola penerimaan, kebijakan, dan realisasi. Peran `head` menyetujui realisasi/transfer dan mengelola laporan. Peran `auditor` hanya membaca.

## Transparansi publik

Halaman publik per lembaga menampilkan total diterima, dialokasikan, direalisasikan, saldo, jumlah program, jumlah penerima manfaat, daftar program terverifikasi, dan nama pemberi yang memberi consent. Nama tidak pernah ditautkan ke transaksi atau nominal. Dokumen publik disajikan melalui controller yang hanya menerima bukti tersamarkan berstatus approved.

## UI/UX

- Sidebar dikelompokkan menjadi Utama, Pembelajaran, Relasi, Tata Kelola, dan Sistem.
- Header halaman memiliki judul ringkas, konteks lembaga, dan satu aksi utama.
- Status menggunakan badge konsisten dengan label Bahasa Indonesia.
- Dashboard admin menunjukkan antrean verifikasi, realisasi menunggu, setoran tertunda, dan kesiapan backup.
- Antrean Tahfizh mengurutkan Murajaah lewat jatuh tempo, kebutuhan koreksi, lalu santri terlama tanpa setoran; pencarian tetap tersedia.
- Ringkasan wali mengutamakan perkembangan hafalan, setoran terakhir, kehadiran, catatan pengajar, dan notifikasi penting.
- Ponsel memakai satu kolom, target sentuh minimal 44px, area aksi tetap mudah dijangkau, tabel berubah menjadi kartu, dan bottom navigation menampilkan fungsi inti per peran.
- Empty, loading, success, rejection, dan validation state memakai copy yang menjelaskan langkah berikutnya.

## Backup dan peluncuran

Command backup mencatat manifest, checksum, ukuran, status, serta retensi 14 harian/8 mingguan/12 bulanan. Restore request memerlukan dua tahap persetujuan, alasan, hasil simulasi, dan audit; eksekusi produksi tetap dilakukan operator di luar HTTP. `v610_pilot` mengendalikan UI/route baru per lembaga sebelum aktivasi penuh.

## Pengujian dan gerbang NO-GO

Regression test mencakup isolasi lembaga, pembagian 40/30/20/10, snapshot kebijakan, idempotensi verifikasi, saldo non-negatif, maker-checker, bukti privat/publik, consent, nomor receipt unik, laporan terkunci, hak auditor read-only, prioritas antrean, dan kontrol restore. Rilis NO-GO jika tes gagal, lintas tenant bocor, saldo bisa negatif, receipt ganda, rollback tidak tervalidasi, backup/restore drill gagal, atau alur kritis mobile tidak dapat digunakan.
