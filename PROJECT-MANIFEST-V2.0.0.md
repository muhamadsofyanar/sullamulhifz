# Project Manifest v2.0.0 — Family Learning & Academy Launch

## Release identity
- RELEASE: v2.0.0
- Status: Launch Candidate
- Public site: https://sullamulhifz.or.id
- Portal: https://app.sullamulhifz.or.id
- Deployment: GitHub main → Coolify
- Runtime: PHP 8.4 + NGINX Unit + MySQL

## New product areas
- Parent Academy
- Teacher Academy
- Academy progress
- Teacher-to-family lesson recommendations
- Mobile-first PWA navigation
- Senior-friendly Quran Player v2
- Child profile ↔ family Academy bridge

## Database
Migration v2.0.0 creates only Academy tables and does not delete existing operational tables.

## Startup
`scripts/container-start-v2.0.0.sh` runs additive migrations, idempotent launch/Academy seeders, verifications, cache, and the official NGINX Unit entrypoint.

## Operational documentation
- UPGRADE-V2.0.0.md
- docs/V2-LAUNCH-CHECKLIST.md
- docs/ACADEMY-GUIDE.md
- docs/PWA-MOBILE-GUIDE.md
- docs/QURAN-PLAYER-V2.md
- docs/ROLLBACK-v2.0.0.md
- docs/TEST-v2.0.0.md
