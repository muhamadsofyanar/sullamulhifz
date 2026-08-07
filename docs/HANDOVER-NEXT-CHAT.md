# Handover — Sullamul Ḥifẓ

## Project

- Repository: `muhamadsofyanar/sullamulhifz`
- Branch: `main`
- Deployment: Coolify + Dockerfile + NGINX Unit + PHP 8.4 + MySQL 8.0-bookworm
- Public: `sullamulhifz.or.id`
- Portal: `app.sullamulhifz.or.id`
- Academy entry: `academy.sullamulhifz.or.id`

## Current package

**v2.0.1 — Premium Mobile UX & Academy Domain**

No database migration is added by v2.0.1. The release refines the v2.0.0 Family Learning & Academy launch candidate.

## Protected production baseline

- 88 santri
- 88 wali
- 4 guru
- 6 main classes
- 2 Tahfizh groups
- Tahfizh A = Mustawa Awal A + Mustawa Tsani A
- Tahfizh B = Mustawa Awal B + Mustawa Tsani B
- Nurul = Tamhidi A/B
- Jundi = Mustawa Awal A/B Tahsīn
- Yanti = Mustawa Tsani A/B Tahsīn
- Sofyan = Tahfizh A/B

Never expose account lists, passwords, `APP_KEY`, `DB_URL`, or private data keys.

## Current priority

Do **not** expand features before launch validation. First validate the current family-learning experience:

1. deploy v2.0.1;
2. attach `academy.sullamulhifz.or.id` to the existing Coolify app and resolve origin SSL;
3. test PWA on 360–430 px and a 588 px responsive viewport;
4. test one complete admin → teacher → guardian flow;
5. test Quran target → repeat audio → Academy recommendation → family follow-up;
6. run `scripts/smoke-test-v2.0.1.sh`;
7. complete backup/restore validation;
8. run a limited guardian/teacher pilot;
9. fix only launch-blocking issues;
10. tag the first stable v2 release after the launch gate is green.
