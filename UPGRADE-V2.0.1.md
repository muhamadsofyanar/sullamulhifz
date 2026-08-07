# Upgrade v2.0.1 — Premium Mobile UX & Academy Domain

## Tujuan

Memperbaiki pengalaman PWA pada ponsel, menyederhanakan Quran Player untuk wali, memastikan asset baru tidak tertahan cache PWA, dan menyiapkan `academy.sullamulhifz.or.id` sebagai pintu masuk Academy.

## Perubahan

- Tidak ada migration database baru.
- `.env.example` disanitasi agar password awal dan data key tidak memiliki nilai default publik; environment production yang sudah tersimpan di Coolify tidak diubah.
- Sidebar mobile benar-benar tersembunyi ketika ditutup.
- Bottom navigation dan topbar dibuat lebih modern serta memiliki area sentuh besar.
- Quran Player: target guru dan preset menjadi fokus utama; pengaturan manual menjadi bagian sekunder.
- CSS/JS diberi cache-busting berdasarkan `filemtime` dan cache service worker dinaikkan ke `v201`.
- Academy host dapat diarahkan ke landing Academy publik; `/belajar` diarahkan ke Academy di portal.

## Instalasi

1. Backup database tetap disarankan sebelum deploy.
2. Salin seluruh isi patch ke root repository dan pilih **Replace files in destination**.
3. Commit dan push ke `main`.
4. Di Coolify, kolom Domains harus memuat:

   `https://taysriulqurani.id,https://sullamulhifz.or.id,https://www.sullamulhifz.or.id,https://app.sullamulhifz.or.id,https://academy.sullamulhifz.or.id`

5. Tambahkan/pertahankan environment:

   `ACADEMY_HOST=academy.sullamulhifz.or.id`

   `ACADEMY_PUBLIC_URL=https://sullamulhifz.or.id/academy`

   `ACADEMY_PORTAL_URL=https://app.sullamulhifz.or.id/academy/belajar`

6. Redeploy satu kali.

## Error Cloudflare 526 pada academy

Error 526 berarti browser dan Cloudflare bekerja, tetapi origin belum memiliki sertifikat valid untuk `academy.sullamulhifz.or.id`. Kode aplikasi tidak dapat memperbaiki sertifikat sebelum request mencapai Laravel.

Setelah domain Academy ditambahkan ke resource Coolify, tunggu sertifikat origin terbit. Bila sertifikat belum terbentuk, ubah record Cloudflare `academy` sementara dari **Proxied** menjadi **DNS only**, redeploy/restart proxy Coolify sampai HTTPS origin valid, lalu kembalikan ke **Proxied**. Mode SSL/TLS Cloudflare tetap **Full (Strict)** setelah origin memiliki sertifikat valid.

## Verifikasi

- `/latihan-quran` tidak memiliki strip sidebar di kiri pada lebar 360–600 px.
- Target guru tampil sebagai satu kartu utuh dan dapat ditekan dengan satu tangan.
- Bottom nav ter-render sebagai nav modern, bukan tombol browser standar.
- CSS baru terlihat tanpa clear-cache manual.
- `https://academy.sullamulhifz.or.id` mengarah ke halaman Academy setelah sertifikat origin valid.
- `https://academy.sullamulhifz.or.id/belajar` mengarah ke Academy portal.
