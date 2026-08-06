# Sullamul Ḥifẓ v1.6.1 — Qari Tahfizh

**Bukan Sekadar Hafal, Tapi KUAT.**

Sullamul Ḥifẓ adalah platform web responsif/PWA untuk operasional TPA, pembelajaran Al-Qur'an, komunikasi guru–wali, dan pencatatan perjalanan santri. TPA Al-Insyirah menjadi implementasi pertama.

## Status saat ini

- **Website publik:** `https://sullamulhifz.or.id`
- **Portal aplikasi:** `https://app.sullamulhifz.or.id`
- **Domain lama/cadangan:** `https://taysriulqurani.id`
- **Rilis paket:** v1.6.1 — Qari Tahfizh
- **Data yang wajib dipertahankan:** 88 santri, 88 wali, 4 guru, 6 kelas utama, Tahfizh A dan Tahfizh B

## Mulai dari sini

1. [`START-HERE.md`](START-HERE.md)
2. [`docs/CURRENT-STATE.md`](docs/CURRENT-STATE.md)
3. [`docs/QARI-TAHFIZH-v1.6.1.md`](docs/QARI-TAHFIZH-v1.6.1.md)
4. [`UPGRADE-V1.6.1.md`](UPGRADE-V1.6.1.md)
5. [`docs/TEST-v1.6.1.md`](docs/TEST-v1.6.1.md)
6. [`docs/ROLLBACK-v1.6.1.md`](docs/ROLLBACK-v1.6.1.md)
7. [`docs/HANDOVER-NEXT-CHAT.md`](docs/HANDOVER-NEXT-CHAT.md)

## Fitur aktif

- website publik dan profil referensi TPA Al-Insyirah;
- portal admin, guru, dan wali;
- profil lembaga, tahun ajaran, kelas, kelompok, dan jadwal;
- absensi, Tahsīn, Tahfizh, murāja‘ah, tugas, dan rapor;
- target hafalan personal dan observasi metode belajar;
- buku penghubung, pengumuman, Pembinaan Jumat, dan Ikrar Santri;
- pemisahan domain publik dan portal;
- Quran Learning untuk ayat, rentang ayat, surah, halaman, rubu’, dan target santri;
- pengulangan per ayat atau seluruh pilihan, termasuk jumlah ulang, jeda, dan kecepatan;
- video bacaan yang diterbitkan setelah kurasi admin.

## Qari v1.6.1

### Mahmoud Khalil Al-Husary — pilihan utama tahfizh

Digunakan sebagai pilihan awal untuk talaqqi, ketelitian bacaan, hafalan baru, dan latihan berulang.

### Muhammad Siddiq Al-Minshawi — pilihan murāja‘ah

Disediakan sebagai alternatif untuk murāja‘ah, menyimak dengan tempo tenang, dan tadabbur.

Pemilih qari berlaku pada latihan manual, preset, dan target hafalan. Pustaka menargetkan **564 timing Juz 30 per qari**, atau **1.128 timing** untuk dua qari.

## Infrastruktur

- PHP 8.4+
- Laravel 13
- MySQL 8.0
- Blade + CSS/JavaScript mandiri
- Docker + NGINX Unit
- Coolify

## Aturan keselamatan produksi

Jangan menjalankan:

```text
php artisan db:wipe
php artisan migrate:fresh
scripts/first-install.sh
ProductionSeeder
```

Migration rilis bersifat additive. Backup database tetap wajib sebelum upgrade.

Jangan commit `.env`, `APP_KEY`, `DB_URL`, password, data key, daftar akun rahasia, atau dump database.
