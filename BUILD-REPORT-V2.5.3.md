# Build Report — Sullamul Hifz v2.5.3

## Scope
Guardian Portal Closure untuk penutupan validasi Fase 3.

## Temuan produksi yang diperbaiki
- Akun wali dapat membuka `/guardian/tasks`, tetapi `/dashboard` menghasilkan HTTP 500.
- Menu `Laporan` pada akun wali mengarah ke `/admin/reports` dan menghasilkan HTTP 403.

## Root cause
- `DashboardController::guardian()` memanggil `$this->audience`, namun `ContentAudienceService` belum diinjeksi.
- Navigasi sidebar menampilkan route admin berdasarkan permission `reports.view` tanpa membatasi role admin.

## Perbaikan
- Constructor injection `ContentAudienceService` pada `DashboardController`.
- Link laporan admin dibatasi ke `superadmin`/`institution_admin`.
- Menu wali menjadi `Perkembangan Anak` dan mengarah ke `#anak-saya`.
- Anchor daftar anak ditambahkan pada dashboard wali.

## Pemeriksaan statis
- PHP syntax checked: 188 file ✅
- Dashboard dependency assertion ✅
- Guardian navigation assertion ✅
- Tidak ada migration baru ✅
- Tidak ada environment variable baru ✅

## Catatan
HTTP 403 jika wali mengetik `/admin/reports` secara manual tetap merupakan perilaku yang benar dan dipertahankan.
