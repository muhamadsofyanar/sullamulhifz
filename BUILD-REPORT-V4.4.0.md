# Build Report v4.4.0

`@phase 4.2` · `@phase 4.3` · `@phase 4.4`

## Implemented

- Universal public product positioning.
- Multi-workspace identity and context switching.
- Consent-based user relationships.
- Multi-tenant institution types, registration, branding, terminology, onboarding, and approval.
- Workspace-scoped role profiles and an operational status gate for non-active institutions.
- Per-file phase traceability and CI gate.

## Compatibility

- Additive schema.
- `users.institution_id` preserved as legacy fallback.
- v4.1.0 communication center and environment variables preserved.
- Existing institution data backfilled to active memberships.

## Verification gates

### Lulus di workspace ini

- `PHASE-MANIFEST.json`: 43 file terlacak pada tiga fase;
- PHP 8.4 `TOKEN_PARSE`: 463 file PHP;
- keseimbangan directive: 14 Blade view yang terlacak fase;
- keseimbangan brace CSS untuk aset v4.4 dan public CSS;
- parsing JSON dan seluruh workflow YAML;
- sintaks shell startup dan pemeriksaan dokumen rilis;
- pemeriksaan referensi route/view baru secara statis.

### Wajib lulus di GitHub Actions sebelum deploy

Runtime lokal tidak menyediakan PHP/Composer/Docker native dan paket tidak menyertakan `vendor`. Karena itu migration Laravel, kompilasi Blade sesungguhnya, seluruh feature test, dan production Docker build dijalankan oleh `.github/workflows/tests.yml`. Jangan redeploy sampai job `php-tests`, `docker-build`, dan `release-docs` hijau.
