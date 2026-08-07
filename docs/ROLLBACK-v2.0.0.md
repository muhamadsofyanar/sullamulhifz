# Rollback v2.0.0

1. Jangan menjalankan migration down pada production secara spontan.
2. Jika deployment gagal sebelum aplikasi sehat, gunakan rollback image/commit Coolify ke v1.9.x terakhir yang stabil.
3. Migration v2.0.0 bersifat additive; tabel Academy boleh tetap ada ketika code rollback sementara.
4. Jika rollback menyangkut data, pulihkan dari backup `pre-v2.0.0-family-academy` hanya setelah memastikan kehilangan data baru dapat diterima.
5. Catat incident dan penyebab sebelum mencoba deployment lagi.

**Dilarang:** `db:wipe`, `migrate:fresh`, atau menghapus volume database.
