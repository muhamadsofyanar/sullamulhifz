# Upgrade v2.2.0 — Academy Portal

## Fokus rilis

v2.2.0 memisahkan Academy dari aplikasi operasional tanpa membuat aplikasi Laravel atau database baru.

Arsitektur yang digunakan:

- `sullamulhifz.or.id` — website/brand utama.
- `app.sullamulhifz.or.id` — operasional admin, guru, kepala, dan wali.
- `academy.sullamulhifz.or.id` — portal LMS Academy mandiri.
- `api.sullamulhifz.or.id` — metadata API dan fondasi integrasi.
- `staging.sullamulhifz.or.id` — ruang uji ketika `STAGING_ENABLED=true`.

## Academy portal

Menu portal:

1. Beranda Academy
2. Program
3. Kelas Saya
4. Modul
5. Materi
6. Video
7. Audio
8. Artikel
9. Progres Belajar
10. Rekomendasi Guru
11. Profil

Academy memakai akun, permission, lembaga, dan database yang sama dengan aplikasi TPA. Ketika `SESSION_DOMAIN` tidak ditentukan dan `DOMAIN_SEPARATION_ENABLED=true`, cookie session otomatis menggunakan domain induk sehingga login dapat dipakai lintas `app` dan `academy`.

## Konten contoh baru

Seeder `AcademyExpansionV220Seeder` menambahkan secara idempoten:

- **STIFIn sebagai Informasi Pendamping**
- **STIFIn Parenting — Mendampingi Tanpa Membatasi**
- **Hidup Bersama Al-Qur’an — Bukan Sekadar Hafal**
- **Pendidikan Anak — Adab, Keteladanan, dan Pertumbuhan**

Konten mengikuti prinsip dokumen Sullamul Hifz: STIFIn hanya informasi tambahan, observasi nyata didahulukan, tidak ada ranking, dan personalisasi tidak menghapus standar pembelajaran.

Video contoh menggunakan satu URL yang diberikan pengelola:
`https://www.youtube.com/watch?v=V_dovd7ezCA`

Video dapat diganti dari **Kelola Academy** tanpa mengubah struktur LMS.

## Deployment

Tidak ada migration baru pada v2.2.0. Startup akan menjalankan seeder baru secara otomatis ketika `AUTO_MIGRATE=true`.

Pastikan Coolify menerima domain:

- `https://sullamulhifz.or.id`
- `https://www.sullamulhifz.or.id`
- `https://app.sullamulhifz.or.id`
- `https://academy.sullamulhifz.or.id`
- `https://api.sullamulhifz.or.id`
- `https://staging.sullamulhifz.or.id` bila diperlukan

Environment utama:

```env
PUBLIC_SITE_URL=https://sullamulhifz.or.id
PORTAL_BASE_URL=https://app.sullamulhifz.or.id
PORTAL_HOST=app.sullamulhifz.or.id
ACADEMY_HOST=academy.sullamulhifz.or.id
ACADEMY_PORTAL_URL=https://academy.sullamulhifz.or.id
API_HOST=api.sullamulhifz.or.id
STAGING_HOST=staging.sullamulhifz.or.id
DOMAIN_SEPARATION_ENABLED=true
AUTO_MIGRATE=true
BOOTSTRAP_PRODUCTION=false
```

`STAGING_ENABLED=false` tetap direkomendasikan pada production sampai staging benar-benar diperlukan.
