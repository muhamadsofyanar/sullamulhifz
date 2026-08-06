# Upgrade v1.6.1 — Qari Tahfizh

## Tujuan

Mengganti sumber murattal utama dari Ahmad Al-Ajmi menjadi dua qari yang lebih sesuai untuk pembinaan tahfizh:

1. **Mahmoud Khalil Al-Husary** — qari utama untuk ketelitian tahfizh.
2. **Muhammad Siddiq Al-Minshawi** — pilihan untuk murajaah dan tadabbur.

## Perubahan

- Al-Husary menjadi sumber default.
- Al-Minshawi tersedia pada pemilih qari.
- Sumber Ahmad Al-Ajmi dinonaktifkan tanpa menghapus timing atau riwayat lama.
- Preset lama dipindahkan ke Al-Husary.
- Pemilihan qari sekarang berlaku juga saat membuka preset siap pakai.
- Sinkronisasi menargetkan 1.128 timing: 564 ayat untuk masing-masing qari.

## Instalasi

1. Backup database.
2. Salin seluruh isi patch ke root repository dan pilih **Replace files in the destination**.
3. Commit dan push ke branch `main`.
4. Redeploy Coolify satu kali.
5. Biarkan sinkronisasi berjalan di latar belakang.

## Log yang diharapkan

```text
=== Sullamul Hifz v1.6.1 container startup ===
Struktur Quran Learning v1.6.1 siap
Sinkronisasi dua qari Juz 30 dimulai
Menjalankan NGINX Unit...
```

## Verifikasi

```bash
php artisan sullam:verify-quran-learning
php artisan sullam:ensure-quran-audio
sh scripts/smoke-test-v1.6.1.sh
```

Target akhir:

```text
Mahmoud Khalil Al-Husary       564/564  Utama
Muhammad Siddiq Al-Minshawi    564/564  Tambahan
Total                         1128/1128
```

Jangan menjalankan `migrate:fresh`, `db:wipe`, atau seeder instalasi awal.
