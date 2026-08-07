# Current State — Sullamul Ḥifẓ

## Current candidate: v2.0.1

Sullamul Ḥifẓ currently combines the TPA operational platform, Academic Core, Quran Learning, guardian communication, Parent Academy, Teacher Academy, and mobile/PWA experience.

### Domain structure

- `sullamulhifz.or.id` — public website
- `app.sullamulhifz.or.id` — authenticated TPA + Family Learning + Academy portal
- `academy.sullamulhifz.or.id` — Academy entry domain; it must be attached to the same Coolify application so the origin certificate is valid
- `taysriulqurani.id` — temporary legacy/fallback domain

### v2.0.1 focus

- eliminate the remaining green sidebar strip on mobile;
- prevent stale PWA CSS/JS after deploy;
- make Quran practice calmer, cleaner, and easier for older guardians;
- keep target-from-teacher as the primary one-tap action;
- keep advanced controls available without putting them in the main flow;
- provide clean routing for the Academy subdomain.

## Before public launch

The project remains a Launch Candidate until these pass on real devices:

1. guardian mobile flow;
2. teacher mobile flow;
3. Quran Player with Al-Husary and Al-Minshawi;
4. Parent Academy progress and teacher recommendations;
5. ownership/access isolation;
6. PWA update/install behavior;
7. backup and tested restore;
8. pilot feedback and final stabilization.
