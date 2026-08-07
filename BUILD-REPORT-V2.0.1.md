# Build Report — Sullamul Ḥifẓ v2.0.1

## Release

- Version: `v2.0.1`
- Name: **Premium Mobile UX & Academy Domain**
- Baseline: `v2.0.0 — Family Learning & Academy Launch`
- Database migration: **none**

## Problems addressed

1. PWA mobile still showed a green sidebar strip on the left when the drawer was closed.
2. Quran practice UI was visually dense and some users could receive stale CSS from the installed PWA/browser cache.
3. Target and preset cards were not sufficiently prioritized for older guardians.
4. Empty Quran player consumed too much vertical space.
5. `academy.sullamulhifz.or.id` returned Cloudflare 526 because the origin certificate/domain had not yet been attached to the Coolify resource.

## Implementation

- Strong off-canvas drawer rules for mobile (`left:-110vw`, hidden/pointer lock when closed).
- File-based cache busting for application/public CSS and JS.
- Service worker cache bumped to `sullam-static-v201`.
- Quran practice information architecture redesigned around: teacher target → quick presets → player → custom settings.
- Premium visual system: deep forest, warm ivory, restrained champagne accent, softer borders/shadows.
- Large touch targets and simpler action hierarchy.
- Academy host configuration and redirect behavior added.
- `.env.example` default account passwords were removed so the public repository does not advertise reusable initial credentials.
- Academy SSL deployment instructions documented; SSL certificate provisioning remains a Coolify/origin task, not a Laravel task.

## Static checks completed

- PHP syntax: **132 files passed** (`app`, `config`, `routes`, `database`).
- JavaScript syntax: `public/js/app.js`, `public/js/public.js`, `public/service-worker.js` passed.
- Shell syntax: startup and v2.0.1 smoke test passed.
- CSS brace balance: application and public CSS passed.
- Quran Blade structural directive/tag balance passed.

## Runtime checks still required after deployment

- PWA at 360 px, 390/430 px, and 588 px responsive viewport.
- Installed PWA update from v2.0.0 to v2.0.1.
- Quran target/preset playback with Al-Husary and Al-Minshawi.
- Academy origin SSL certificate on `academy.sullamulhifz.or.id`.
- Admin, teacher, and guardian navigation.

## Safety

No production data deletion, no fresh migration, no database wipe, and no new seeder are introduced by v2.0.1.
