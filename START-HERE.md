# START HERE — Sullamul Ḥifẓ

## Current launch candidate

- Version: **v2.0.1 — Premium Mobile UX & Academy Domain**
- Public website: `https://sullamulhifz.or.id`
- Authenticated portal: `https://app.sullamulhifz.or.id`
- Academy entry: `https://academy.sullamulhifz.or.id` → landing Academy / portal Academy
- First implementation: **TPA Al-Insyirah**
- Product status: **Launch Candidate**, not stable until mobile, Academy, audio, access isolation, and backup/restore checks pass.

## What is included

- TPA operations and Academic Core
- Quran Learning with Al-Husary and Al-Minshawi
- Daily teacher workflow
- Guardian portal and reporting
- Parent Academy + Teacher Academy
- Child → Quran practice → Academy recommendation → family follow-up flow
- Mobile/PWA navigation
- Premium mobile refinement v2.0.1
- Launch-readiness checks and operational documentation

## Read these first

1. `UPGRADE-V2.0.1.md`
2. `docs/V2-LAUNCH-CHECKLIST.md`
3. `docs/ACADEMY-GUIDE.md`
4. `docs/PWA-MOBILE-GUIDE.md`
5. `docs/QURAN-PLAYER-V2.md`
6. `docs/TEST-v2.0.0.md`
7. `docs/ROLLBACK-v2.0.0.md`
8. `docs/HANDOVER-NEXT-CHAT.md`

## Production safety

Never run on production:

```text
php artisan migrate:fresh
php artisan db:wipe
scripts/first-install.sh
InitialTpaDataSeeder
ProductionSeeder
```

Do not place child data, password lists, `APP_KEY`, `DB_URL`, or private seed keys in the public repository.
