# Build Report — Sullamul Hifz v2.5.1

Release: **v2.5.1 — Phase 3 Closure Hotfix**

## Production bug fixed
- Detail Perjalanan Tahfizh returned HTTP 500 because controller view data named `errors` shadowed Laravel's shared validation `$errors` MessageBag.
- The layout called `$errors->any()`, but received an Eloquent Collection instead.
- The Tahfizh correction collection is now named `correctionItems`.

## Verification
- PHP syntax lint completed for all PHP files across app/bootstrap/config/database/routes/tests.
- No `'errors' => QuranLearningErrorItem::query()` remains in application controllers.
- Student Tahfizh view references `$correctionItems` for correction items.
- Regression assertion added to `TahfizhLearningEngineV250Test`.
- No migration and no environment change.

## Runtime gate
After Coolify deployment, open a real student's Tahfizh journey. The page must return 200 and forms for cycle/review must be exercised before Fase 3 can be closed.
