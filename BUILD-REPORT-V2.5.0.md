# Build Report — Sullamul Hifz v2.5.0

Release: **v2.5.0 — Tahfizh Learning Engine**

## Scope

Fase 3 menghubungkan target hafalan, persiapan/talaqqi, setoran, penguatan, Muraja'ah, fokus koreksi, jadwal penjagaan, dashboard guru, ringkasan wali, serta laporan tanpa ranking atau skor total santri.

## Static verification completed

- 199 PHP files under `app/`, `bootstrap/`, `config/`, `database/`, `routes/`, and `tests/` passed `php -l`.
- 27 shell scripts passed `sh -n`.
- 5 JavaScript files passed `node --check`.
- 4 JSON files parsed successfully.
- Release documentation check reports v2.5.0 documentation complete.
- Dockerfile points to `scripts/container-start-v2.5.0.sh`.
- Startup runs the v2.5 Tahfizh seeder and `sullam:verify-tahfizh`.
- Service worker cache namespace is v250 and includes `app-v250.css`.
- Phase 3 migration, models, services, teacher controller/views and feature test are present.
- Teacher routes use `learning.manage` permission and retain assignment/institution scoping.

## Main database additions

- `tahfizh_learning_cycles`
- `memorization_review_plans`
- `quran_learning_error_items`
- additional Tahfizh/Muraja'ah linkage, prompt, self-correction, delivery mode and review-date fields.

## Runtime verification still required

This workspace does not contain `vendor/`, `composer.lock`, or `vendor/bin/phpunit`, therefore a full Laravel boot, migration against the production database, and PHPUnit suite cannot be executed locally here.

Coolify deployment must therefore verify:

1. `php artisan migrate --force` succeeds;
2. `php artisan sullam:verify-tahfizh` runs;
3. teacher can execute target -> preparation -> setoran -> strengthening -> Muraja'ah;
4. guardian sees only linked child and appropriate family guidance;
5. talaqqi/tasmi' workflows behave correctly;
6. review plan completion/rescheduling is idempotent;
7. mobile Tahfizh forms remain practical.

## Phase status rule

Source implementation may reach 100%, but Fase 3 remains **not complete** until production validation is also 100%. Previous phases also remain open if their validation gates have not passed.
