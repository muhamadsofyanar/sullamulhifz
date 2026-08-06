# Rollback v1.4.0

Rollback kode dilakukan dengan redeploy commit v1.3.0. Jangan menjalankan `migrate:rollback` di production tanpa evaluasi, karena tabel baru dapat sudah berisi artikel, pendaftaran, impor, atau rapor.

Urutan aman:

1. Aktifkan maintenance mode bila aplikasi tidak stabil.
2. Kembalikan source ke commit v1.3.0.
3. Redeploy.
4. Biarkan tabel tambahan v1.4.0 tetap ada; kode v1.3.0 tidak menggunakannya.
5. Jika database harus benar-benar dikembalikan, restore backup MySQL yang dibuat sebelum upgrade.
