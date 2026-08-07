# Upgrade v2.5.1 — Phase 3 Closure Hotfix

## Masalah produksi
Halaman detail `teacher/tahfizh/students/{student}` menghasilkan HTTP 500. Runtime menunjukkan `BadMethodCallException: Method Illuminate\Database\Eloquent\Collection::any does not exist`.

## Akar masalah
Controller mengirim koleksi fokus koreksi dengan nama view variable `$errors`. Nama ini bertabrakan dengan `$errors` bawaan Laravel (validation MessageBag) yang dipakai layout `layouts/app.blade.php` melalui `$errors->any()`. Akibatnya layout menerima Eloquent Collection dan memanggil method `any()` yang tidak tersedia pada Collection tersebut.

## Perbaikan
- Rename view data `errors` menjadi `correctionItems`.
- Update seluruh referensi di halaman detail Tahfizh.
- Tambah regression test agar reserved validation error bag tidak tertimpa lagi.
- Tidak ada migration database.
- Tidak ada environment variable baru.

## Setelah deploy
1. Login sebagai guru.
2. Buka Perjalanan Tahfizh.
3. Buka salah satu santri, misalnya `/teacher/tahfizh/students/62`.
4. Pastikan halaman detail tampil tanpa HTTP 500.
5. Uji membuat siklus belajar dan jadwal Murajaah.

Fase 3 belum dianggap 100% sampai workflow guru dan wali selesai divalidasi.
