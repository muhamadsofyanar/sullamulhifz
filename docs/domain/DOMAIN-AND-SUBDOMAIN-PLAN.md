# Rencana Domain dan Subdomain

## Domain aktif

| Host | Fungsi | Resource Coolify |
|---|---|---|
| `sullamulhifz.or.id` | Website publik | aplikasi Sullamul Hifz v1.3.0 |
| `www.sullamulhifz.or.id` | Alias ke website publik | redirect ke apex |
| `app.sullamulhifz.or.id` | Portal admin, guru, wali | aplikasi Sullamul Hifz v1.3.0 |

## Domain yang dicadangkan

| Host | Fungsi | Waktu aktivasi |
|---|---|---|
| `academy.sullamulhifz.or.id` | LMS/Academy | v2.0.0 |
| `staging.sullamulhifz.or.id` | pengujian privat | saat pipeline staging tersedia |
| `api.sullamulhifz.or.id` | integrasi API | saat API publik dibutuhkan |

## IP server saat dokumen dibuat

`38.47.180.127`

Periksa kembali IP VPS sebelum mengimpor DNS.

## Cloudflare

Gunakan proxy Cloudflare untuk record website (`@`, `www`, `app`) setelah origin dan SSL telah diuji. Mode SSL/TLS: **Full (strict)** apabila sertifikat origin valid; sementara gunakan **Full**, jangan Flexible.
