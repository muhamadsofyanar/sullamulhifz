# Build Report — Sullamul Hifz v1.0.0

Release date: 2026-08-06

Verified release requirements:

- Complete Laravel project source included.
- Docker runtime: `unit:1.34.2-php8.4`.
- Composer platform: PHP `8.4.1`.
- MySQL SSL PDO constant: `PDO::MYSQL_ATTR_SSL_CA`.
- MySQL-safe unique index name: `asgn_submission_recipient_attempt_uq`.
- First-install and regular-deploy scripts included.
- Public release marker included at `/release.txt`.

The first-install script intentionally requires `CONFIRM_DATABASE_WIPE=YES` before deleting tables.
