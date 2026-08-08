# Sullamul Hifz v2.5.4 — Guardian Final Closure

## Masalah yang diperbaiki

1. Dashboard wali (`/dashboard`) menghasilkan HTTP 500 dengan `ParseError: unexpected token ';'` pada compiled Blade.
   Penyebabnya adalah dua assignment PHP ditulis dalam satu inline `@php(...)`. Blade mengompilasinya menjadi PHP yang tidak valid.
2. `/guardian/children` sebelumnya 404 karena hanya route detail `/guardian/children/{student}` yang tersedia.

## Perbaikan

- Mengubah assignment dashboard wali menjadi block `@php ... @endphp` yang valid.
- Menambahkan route `guardian.children.index` di `/guardian/children`.
- Menambahkan halaman daftar anak milik wali.
- Sidebar dan bottom navigation `Perkembangan Anak / Anak` sekarang menuju route index yang nyata.
- Otorisasi detail anak tetap memakai relasi guardian-student; wali tidak dapat membuka anak lain.

## Database

Tidak ada migration baru.

## Environment

Tidak ada environment variable baru.
