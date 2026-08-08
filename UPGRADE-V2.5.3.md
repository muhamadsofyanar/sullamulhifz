# Sullamul Hifz v2.5.3 — Guardian Portal Closure

Hotfix penutupan validasi Fase 3 untuk portal wali.

## Diperbaiki

1. `/dashboard` pada akun wali tidak lagi menghasilkan HTTP 500.
   - Penyebab: `DashboardController` menggunakan `ContentAudienceService` melalui `$this->audience` tetapi dependency belum diinjeksi ke controller.
   - Solusi: constructor injection `ContentAudienceService`.

2. Menu `Laporan` tidak lagi mengarahkan akun wali ke `/admin/reports` yang memang khusus admin.
   - Link laporan admin sekarang hanya muncul untuk `superadmin` dan `institution_admin` yang memiliki `reports.view`.
   - Wali mendapat menu `Perkembangan Anak` yang menuju bagian anak di dashboard.

3. Bagian daftar anak pada dashboard wali mendapat anchor `#anak-saya` agar navigasi langsung dan konsisten.

## Validasi setelah deploy

- Login akun wali.
- Buka `/dashboard` → harus tampil dashboard wali, bukan 500.
- Klik `Perkembangan Anak` → menuju kartu anak.
- Klik anak → halaman perkembangan anak harus menampilkan ringkasan Tahfizh, target aktif, setoran, Muraja'ah, dan jadwal penjagaan milik anak tersebut.
- Wali tidak boleh dapat membuka `/admin/reports`; HTTP 403 untuk URL admin tetap benar.

Tidak ada migration database baru dan tidak perlu mengubah Environment Coolify.
