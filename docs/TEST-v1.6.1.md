# Test Checklist v1.6.1 — Qari Tahfizh

## Deployment

- [ ] Log memuat `Sullamul Hifz v1.6.1 container startup`.
- [ ] Migration `2026_08_06_000310_qari_tahfizh_v161` berstatus `Ran`.
- [ ] NGINX Unit memuat konfigurasi melalui `docker-entrypoint.sh`.
- [ ] Website publik dan portal dapat dibuka.

## Pustaka Qur’an

- [ ] Halaman `/admin/quran-library` terbuka.
- [ ] Mahmoud Khalil Al-Husary tampil sebagai **Utama**.
- [ ] Muhammad Siddiq Al-Minshawi tampil sebagai **Tambahan**.
- [ ] Al-Husary mencapai 564/564 timing.
- [ ] Al-Minshawi mencapai 564/564 timing.
- [ ] Total pustaka mencapai 1.128/1.128 timing.
- [ ] Sumber Ahmad Al-Ajmi tidak ditawarkan kepada pengguna.

## Latihan

Uji masing-masing qari pada:

- [ ] An-Nās ayat 1, ulang 10×.
- [ ] Al-Qāri‘ah ayat 1–5, ulang per ayat.
- [ ] satu surah lengkap;
- [ ] satu halaman Mushaf;
- [ ] satu rubu’ Juz 30;
- [ ] target hafalan santri.

Periksa juga:

- [ ] berpindah qari benar-benar mengubah sumber audio;
- [ ] jeda dan kecepatan berfungsi;
- [ ] tombol berhenti, lanjut, sebelumnya, dan berikutnya berfungsi;
- [ ] sesi latihan tersimpan tanpa membuka data anak lain.

## Data lama

- [ ] 88 santri tetap tersedia.
- [ ] 88 wali tetap tersedia.
- [ ] 4 guru tetap tersedia.
- [ ] 6 kelas utama dan 2 kelompok Tahfizh tetap tersedia.
- [ ] target, observasi, absensi, setoran, dan rapor lama tidak berubah.

## Perintah verifikasi

```bash
php artisan sullam:about
php artisan migrate:status
php artisan sullam:verify-quran-learning
sh scripts/smoke-test-v1.6.1.sh
```
