# Handover for the Next Chat

## Project

- Repository: `muhamadsofyanar/sullamulhifz`
- Branch: `main`
- Hosting: Coolify, Dockerfile, NGINX Unit, PHP 8.4, MySQL 8.0-bookworm
- Public: `sullamulhifz.or.id`
- Portal: `app.sullamulhifz.or.id`

## Current package

`v1.9.0 — TPA Launch Complete`

It combines daily operations, guardian/reporting, and launch readiness. Migration is additive. Startup automatically migrates, seeds non-personal templates, verifies core systems, and starts Unit through the official entrypoint.

## Important protected facts

- 88 students, 88 guardians, 4 teachers
- Tahfizh A = Mustawa Awal A + Mustawa Tsani A
- Tahfizh B = Mustawa Awal B + Mustawa Tsani B
- Nurul: Tamhidi A/B
- Jundi: Mustawa Awal A/B Tahsīn
- Yanti: Mustawa Tsani A/B Tahsīn
- Sofyan: Tahfizh A/B
- Never expose initial account list or encrypted seed key

## Next work

1. Deploy v1.9.0 once.
2. Run `scripts/smoke-test-v1.9.0.sh`.
3. Test one complete admin → teacher → guardian workflow.
4. Complete the launch checklist.
5. Create stable backup and Git tag.
6. Only then begin v2.0.0 Academy.
