# Build Report v3.0.0

## Scope

Public Self-Registration + Personal Mode di atas baseline v2.9.0 yang telah lulus smoke test Fase 7 di produksi.

## Gate kandidat

- migration additive dan workspace lembaga lama default `institution`;
- pendaftaran Personal membuat workspace privat dan role khusus;
- ownership Personal dipisahkan per user + institution;
- jurnal mandiri tidak memakai tabel setoran guru;
- `PersonalJourneyService` tidak membaca STIFIn;
- regression test mencakup pendaftaran, permission, isolasi goal lintas akun, dan guardrail STIFIn;
- Docker build tetap menjalankan route registration, Blade compile, dan PHP lint sebelum image dapat dipakai.

## QA workspace

- static parse: **264 file PHP OK**;
- pemeriksaan pasangan directive: **110 file Blade OK**;
- `scripts/check-release-docs.sh`: **lulus untuk v3.0.0**;
- secret scan hanya menemukan password fixture pada test otomatis, bukan kredensial produksi;
- PHP runtime/vendor penuh tidak tersedia pada workspace ini, sehingga `php artisan test`, `route:list`, dan `view:cache` tetap menjadi gate GitHub Actions/Docker build sebelum deploy.

Promotion ke produksi tetap memerlukan GitHub Actions hijau dan smoke test pada `UPGRADE-V3.0.0.md`.
