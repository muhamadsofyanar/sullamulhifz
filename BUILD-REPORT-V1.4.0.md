# Build Report v1.4.0

## Baseline

Source v1.3.0 Public Website.

## Implemented

- additive operational migration;
- guardian management;
- student birthplace/photo fields;
- validated CSV import preview/commit;
- dynamic public pages and articles;
- public admission form and admin follow-up;
- targeted announcements, pinning, attachments, acknowledgement;
- Friday media and worksheet;
- private liaison attachments;
- report cards with browser print/PDF;
- expanded CSV exports;
- login history;
- upgrade, smoke test, rollback, database, and test documentation.

## Verification performed in build environment

- PHP 8.4 syntax lint passed for every `.php` file.
- Route declarations and referenced controller methods were reviewed statically.
- Original data seeder and encrypted initial data were preserved.
- No destructive production command was added to the v1.4.0 upgrade script.

## Verification still required on server

The build environment does not contain Composer dependencies or a live MySQL clone. Therefore run after database backup:

```bash
sh scripts/upgrade-v1.4.0.sh
sh scripts/smoke-test-v1.4.0.sh
```

Then complete `docs/TEST-v1.4.0.md` before operational use.
