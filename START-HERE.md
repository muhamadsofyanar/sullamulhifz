# START HERE — Sullamul Ḥifẓ

## Current release candidate

- Application package: **v1.9.0 — TPA Launch Complete**
- Production baseline before upgrade: v1.6.1
- Public site: `https://sullamulhifz.or.id`
- Portal: `https://app.sullamulhifz.or.id`
- First institution: TPA Al-Insyirah
- Active data baseline: 88 santri, 88 wali, 4 guru, 6 main classes, 2 Tahfizh groups

## What v1.9.0 contains

- Academic Core and Quran Learning
- Al-Husary and Al-Minshawi
- Daily teacher operations
- Bulk attendance
- Detailed Tahsīn, Tahfizh, and Murāja‘ah records
- Audio-linked homework
- Guardian daily and monthly summaries
- Report cards and CSV reports
- Launch-readiness checklist
- PWA/offline and friendly error pages
- Security headers, login history, and activity log

## First files to read

1. `UPGRADE-V1.9.0.md`
2. `docs/PHASES-v1.9.0.md`
3. `docs/TEST-v1.9.0.md`
4. `docs/ROLLBACK-v1.9.0.md`
5. `docs/HANDOVER-NEXT-CHAT.md`

## Production safety

Never run on production:

```text
php artisan migrate:fresh
php artisan db:wipe
scripts/first-install.sh
InitialTpaDataSeeder
ProductionSeeder
```

Private keys, account lists, APP_KEY, DB_URL, and child data must never enter the public repository.
