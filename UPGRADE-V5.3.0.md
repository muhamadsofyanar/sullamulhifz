# Upgrade v5.3.0 — Empat Fase, Satu Deploy

`@phase 5.0` · `@phase 5.1` · `@phase 5.2` · `@phase 5.3`

## Versi asal

Upgrade ini ditujukan untuk **v4.9.0** yang sudah menjalankan Ruang Belajar Terpadu. Data Personal, Ustadz Privat, Keluarga, Lembaga, Qur’an Engine, Academy, komunikasi, payment ledger lama, dan seluruh histori dipertahankan.

## Isi batch

### Fase 9 / v5.0 — Business, Payment & Integrations

- katalog paket untuk Personal, Ustadz, dan lembaga;
- hanya **Personal Gratis** aktif sebagai preset awal; paket berbayar preset tetap **nonaktif dengan harga 0** sampai superadmin menetapkan harga nyata dan mengaktifkannya;
- subscription, invoice, entitlement snapshot, serta lifecycle pembayaran;
- transfer manual memakai rekening resmi yang sudah dikonfigurasi;
- paket berbayar **tidak aktif** sebelum transaksi diverifikasi admin;
- Pusat Bisnis admin menyatukan paket, subscription, invoice, payment ledger, dan status integrasi;
- isolasi tenant diperiksa antara invoice, subscription, dan transaksi.

### Fase 10 / v5.1 — SaaS Production Readiness

- dashboard Operasional SaaS;
- pemeriksaan schema, database, storage, tenant membership, relationship integrity, queue, dan integrasi;
- histori hasil pemeriksaan operasional;
- health endpoint mengembalikan `503 degraded` bila database/storage gagal;
- backup, restore drill, dan load test **tidak dipalsukan menjadi PASS**. Bukti operator baru PASS setelah marker environment diisi sesudah pengujian nyata.

### Fase 11 / v5.2 — Pendamping Cerdas

- rekomendasi lokal berbasis data milik akun sendiri;
- tidak mengirim jurnal privat ke provider AI eksternal;
- draft dapat diminta untuk review Ustadz;
- review lintas workspace hanya melalui hubungan `mentor_learner` yang `accepted`;
- keputusan Ustadz dapat `accepted`, `modified`, atau `rejected` dan dicatat di audit;
- draft tidak boleh menjadi `approved` tanpa human review.

### Fase 12 / v5.3 — Mobile, Offline & Global

- PWA static shell yang lebih aman;
- halaman privat, media privat, API, dan navigasi autentikasi tidak dimasukkan ke offline cache;
- fallback offline hanya untuk shell/aset statis;
- preferensi bahasa, zona waktu, PWA, email, dan WhatsApp per akun;
- metadata API untuk capability mobile/global;
- preference bahasa merupakan fondasi lokalisasi; **seluruh copy UI belum diterjemahkan penuh ke semua bahasa**.

## Dampak database

Ada **satu migration additive**:

`2026_08_10_006000_business_saas_ai_mobile_v530.php`

Migration menambah tabel `billing_plans`, `billing_subscriptions`, `billing_invoices`, `operational_check_runs`, `user_preferences`, kolom `billing_invoice_id` pada `payment_transactions`, feature flag baru, dan paket default. Tidak ada `drop`, truncate, atau reset data pada `up()`.

## Sebelum deploy

1. Backup database MySQL dan volume persistent `storage`.
2. Pertahankan `.env`, `APP_KEY`, rekening resmi, serta kredensial komunikasi.
3. Upload isi paket ke root repository lalu commit/push **sekali**.
4. Tunggu GitHub Actions hijau sebelum Redeploy Coolify.

## Deploy

Redeploy Coolify **satu kali**. Dengan `AUTO_MIGRATE=true`, startup menjalankan migration secara additive. Jangan menjalankan `migrate:fresh` atau `db:wipe`.

## Verifikasi setelah deploy

Jalankan:

```sh
php artisan migrate:status
php artisan sullam:verify-release-v530
```

Perintah `verify-release-v530` menjalankan verifier penting v4.5–v5.3 secara berurutan. Jika batch berhenti, jalankan verifier fase yang disebut pada error untuk diagnosis.

Verifier SaaS boleh menghasilkan **WARNING** untuk backup/restore/load test bila bukti operator belum dilakukan; warning ini disengaja dan tidak boleh diubah menjadi PASS hanya untuk menaikkan persentase.

## Smoke test minimal

1. Personal: buka `Paket & Layanan`, aktifkan paket gratis, lalu buat satu tagihan berbayar test bila memang ingin menguji ledger.
2. Admin/Superadmin: buka `Pusat Bisnis`; pastikan tenant scope benar. Jika memakai transaksi test, verifikasi lalu pastikan subscription aktif.
3. Admin: buka `Operasional SaaS` dan simpan satu snapshot pemeriksaan.
4. Personal dengan Ustadz Privat aktif: buka `Pendamping Cerdas`, minta review.
5. Ustadz: buka `Review Pendamping Cerdas`, review draft; pastikan Personal melihat hasil final.
6. Buka `Preferensi`, simpan bahasa/zona waktu/PWA; uji install PWA dan mode offline.
7. Saat offline, pastikan halaman privat lama tidak muncul dari cache.

## Marker operator Fase 10

Isi **hanya setelah benar-benar diuji** di Coolify/infra:

```env
SULLAM_BACKUP_VERIFIED_AT=2026-08-10T...
SULLAM_RESTORE_DRILL_VERIFIED_AT=2026-08-10T...
SULLAM_LOAD_TEST_VERIFIED_AT=2026-08-10T...
```

Tanpa marker tersebut Fase 10 boleh dianggap implementasi selesai, tetapi belum `fully_verified` secara operasional.

## Rollback

Rollback kode ke v4.9.0 **tidak disarankan dengan rollback migration otomatis** setelah data bisnis mulai terisi. Jika rollback aplikasi diperlukan, pertahankan tabel v5.3 dan lakukan rollback kode terkontrol setelah backup. Jangan menjalankan `migrate:rollback` di produksi tanpa inspeksi data.
