# Build Report v2.0.3

Release: v2.0.3 — Academy Experience & Video

## Fokus
- memperbaiki halaman Kelola Academy yang masih menghasilkan 500;
- menyederhanakan query statistik admin Academy;
- menulis ulang Academy Studio dengan struktur Blade minimal;
- memperbaiki tampilan Academy desktop/mobile;
- menghilangkan horizontal overflow sidebar;
- menambahkan embed YouTube dan YouTube Shorts;
- menambahkan video contoh Academy dari URL yang diberikan pengelola;
- membuat materi Academy dapat diedit lebih lengkap dari admin;
- menaikkan cache PWA/service worker ke v203.

## Pemeriksaan statis
- PHP syntax: 135 file pada app/database/routes/config/bootstrap.
- Shell syntax: startup dan smoke-test v2.0.3.
- Route source dan struktur data Academy dipertahankan.
- Tidak ada migration destructive.
- Seeder v2.0.3 idempotent.

## Batas pengujian
Pengujian database production, sesi autentikasi nyata, dan pemutaran YouTube langsung dilakukan setelah redeploy di Coolify.
