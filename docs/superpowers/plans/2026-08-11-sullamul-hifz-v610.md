# Sullamul Hifz v6.1.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menghasilkan paket v6.1.0 yang menambah tata kelola infak transparan, pengalaman operasional dan mobile yang diperbarui, serta kontrol backup/restore di atas baseline v6.0.0.

**Architecture:** Modular monolith Laravel tetap dipertahankan. Mutasi infak memakai service transaksional dan ledger append-only; UI Blade memperoleh navigasi berkelompok serta stylesheet v6.1 terpisah.

**Tech Stack:** PHP 8.4, Laravel 13.8, Blade, CSS native, PHPUnit 12, SQLite/MySQL.

## Global Constraints

- Jangan mengubah migration lama; buat migration additive v6.1.0.
- Jangan menambah paywall atau hubungan infak-entitlement.
- Semua query lembaga wajib terisolasi dengan `institution_id`.
- Semua file bukti asli berada pada disk privat.
- Saldo ledger tidak boleh negatif dan verifikator tidak boleh sama dengan pembuat realisasi.
- UI tetap server-rendered dan usable pada viewport 360px tanpa framework baru.

---

### Task 1: Schema, permission, and models

**Files:**
- Create: `database/migrations/2026_08_11_008000_transparent_infaq_operations_v610.php`
- Create: `database/seeders/TransparentInfaqV610Seeder.php`
- Create: `app/Models/InfaqAllocationPolicy.php`, `InfaqAllocationPolicyItem.php`, `InfaqAllocation.php`, `InfaqLedgerEntry.php`, `InfaqRealisation.php`, `InfaqEvidence.php`, `InfaqFundTransfer.php`, `InfaqMonthlyReport.php`, `InfaqReceiptSequence.php`, `BackupRun.php`, `RestoreRequest.php`
- Modify: `app/Models/InfaqTransaction.php`, `app/Models/User.php`, `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/TransparentInfaqV610Test.php`

**Interfaces:**
- Produces: Eloquent relations and casts used by all v6.1 services.

- [ ] Write a schema test asserting every v6.1 table, index-critical column, permission, and `v610_pilot` flag exists.
- [ ] Run `php artisan test --filter=TransparentInfaqV610Test` and confirm failure before migration exists.
- [ ] Add the migration, explicit foreign keys/index names, permission rows, role assignments, and idempotent seeder.
- [ ] Add focused models with `$fillable`, casts, and relations.
- [ ] Run the test and confirm the schema test passes.

### Task 2: Policy, receipt, and ledger services

**Files:**
- Create: `app/Services/InfaqAllocationService.php`
- Create: `app/Services/InfaqLedgerService.php`
- Create: `app/Services/InfaqReceiptService.php`
- Modify: `app/Services/InfaqService.php`
- Test: `tests/Feature/TransparentInfaqV610Test.php`

**Interfaces:**
- Produces: `activePolicy(int $institutionId)`, `replacePolicy(User $actor, array $percentages, string $reason)`, `allocate(InfaqTransaction $transaction)`, `balance(int $institutionId, string $category)`, and `nextNumber(InfaqTransaction $transaction)`.

- [ ] Add failing tests for default 40/30/20/10 allocation, special-purpose 100%, prospective policy versions, exact rounding, and unique receipt sequences.
- [ ] Implement policy validation requiring exactly 10000 basis points.
- [ ] Implement transactional receipt sequencing per institution/year.
- [ ] Implement append-only credits and idempotent allocation snapshot creation.
- [ ] Update `InfaqService::verify()` to require mutation-match notes and call receipt/allocation in the same transaction.
- [ ] Run focused tests and verify old v6.0 idempotency tests remain compatible.

### Task 3: Evidence, realisation, transfer, and monthly reports

**Files:**
- Create: `app/Services/InfaqRealisationService.php`
- Create: `app/Services/InfaqReportService.php`
- Create: `app/Http/Controllers/Admin/InfaqPolicyController.php`
- Create: `app/Http/Controllers/Admin/InfaqRealisationController.php`
- Create: `app/Http/Controllers/Admin/InfaqTransferController.php`
- Create: `app/Http/Controllers/Admin/InfaqReportController.php`
- Modify: `app/Services/MediaStorageService.php`, `routes/web.php`
- Test: `tests/Feature/TransparentInfaqV610Test.php`

**Interfaces:**
- Produces: realisation draft/submit/review workflow, approved transfer double-entry, immutable monthly snapshots.

- [ ] Add failing tests for mandatory evidence, maker-checker, rejection reason, insufficient balance, balanced transfer entries, and immutable locked reports.
- [ ] Store original and redacted evidence as distinct private `MediaAsset` records.
- [ ] Implement submission and approval with row locks; create debit only on first approval.
- [ ] Implement transfer approval as equal debit/credit ledger entries.
- [ ] Implement monthly lock and correction-entry path without reopening snapshots.
- [ ] Run focused tests and confirm tenant mismatch returns 404/403 without data disclosure.

### Task 4: Routes, controllers, public transparency, and notifications

**Files:**
- Modify: `app/Http/Controllers/InfaqController.php`, `app/Http/Controllers/Admin/InfaqController.php`, `routes/web.php`
- Create: `app/Http/Controllers/PublicInfaqController.php`, `app/Http/Controllers/InfaqReceiptController.php`, `app/Http/Controllers/InfaqEvidenceController.php`
- Create: `app/Notifications/InfaqStatusNotification.php`
- Create: `resources/views/infaq/receipt.blade.php`, `resources/views/public/infaq/show.blade.php`
- Test: `tests/Feature/TransparentInfaqV610HttpTest.php`

**Interfaces:**
- Produces: authenticated receipt/evidence endpoints and anonymous aggregated transparency page.

- [ ] Add route and authorization tests for own receipt, auditor original evidence, approved redacted public evidence, and aggregate-only donor names.
- [ ] Replace `features.manage` middleware with dedicated infaq permissions.
- [ ] Validate optional transfer proof through MIME and size restrictions.
- [ ] Dispatch database/mail notifications after commit for verified/rejected/submitted/reviewed states.
- [ ] Render the public page without transaction IDs, individual amounts, contact data, or original evidence paths.
- [ ] Run HTTP tests and route listing checks.

### Task 5: Operational dashboards and family summary

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/Teacher/TahfizhController.php`, `app/Http/Controllers/Guardian/PortalController.php`
- Modify: `resources/views/dashboard/admin.blade.php`, `resources/views/dashboard/teacher.blade.php`, `resources/views/dashboard/guardian.blade.php`
- Modify: `resources/views/teacher/tahfizh/index.blade.php`, `resources/views/guardian/portal/index.blade.php`
- Test: `tests/Feature/OperationalExperienceV610Test.php`

**Interfaces:**
- Produces: prioritized queue collections and role-specific dashboard counters.

- [ ] Add tests proving overdue reviews precede correction work and stale students, search remains institution-scoped, and guardian summaries only include linked children.
- [ ] Add dashboard counters for pending infaq, realisations, submissions, and backup freshness.
- [ ] Implement deterministic queue ordering with query limits and no cross-tenant rows.
- [ ] Update Blade views to put primary actions and urgent states first.
- [ ] Run focused tests and inspect empty states.

### Task 6: UI system and responsive navigation

**Files:**
- Create: `public/css/app-v610.css`
- Modify: `resources/views/layouts/app.blade.php`, `public/js/app.js`
- Modify: `resources/views/infaq/index.blade.php`, `resources/views/admin/infaq/index.blade.php`
- Create: `resources/views/admin/infaq/realisations/index.blade.php`, `resources/views/admin/infaq/policy.blade.php`, `resources/views/admin/infaq/reports.blade.php`
- Test: `tests/Feature/UiUxV610Test.php`

**Interfaces:**
- Produces: `.v610-*` components, grouped navigation, consistent badges, responsive card tables, and 44px action targets.

- [ ] Add static view tests for stylesheet inclusion, landmarks, grouped navigation labels, field error associations, and mobile action classes.
- [ ] Add grouped navigation while preserving every existing route and role condition.
- [ ] Build responsive infak workspaces with summary cards, filters, status timeline, and clear maker-checker actions.
- [ ] Add CSS at 360/768/1280 breakpoints, reduced-motion support, visible focus, and WCAG-oriented contrast tokens.
- [ ] Run static UI tests and scan Blade compilation.

### Task 7: Backup/restore controls and release pilot

**Files:**
- Create: `app/Console/Commands/RecordBackupRun.php`, `app/Services/BackupManifestService.php`
- Create: `app/Http/Controllers/Admin/RecoveryController.php`
- Create: `resources/views/admin/recovery/index.blade.php`
- Modify: `routes/web.php`, `routes/console.php`, `config/sullam.php`, `.env.example`
- Test: `tests/Feature/ProductionReadinessV610Test.php`

**Interfaces:**
- Produces: recorded manifests, two-step restore approvals, and `v610_pilot` access guard.

- [ ] Add tests for checksum manifest validation, retention labels, superadmin-only restore requests, distinct second approver, and absence of an HTTP restore execution route.
- [ ] Implement the manifest recorder and database status updates without database deletion/restore commands.
- [ ] Implement two-step request and simulation-result recording with audit logs.
- [ ] Add feature-flag middleware/default config and pilot documentation.
- [ ] Run focused readiness tests.

### Task 8: Release verification and packaging

**Files:**
- Create: `app/Console/Commands/VerifyReleaseV610.php`
- Create: `UPGRADE-V6.1.0.md`, `DEPLOY-QUICK-V6.1.0.txt`, `BUILD-REPORT-V6.1.0.md`, `CHANGED-FILES-V6.1.0.txt`
- Modify: `CHANGELOG.md`, `README.md`, `RELEASE`, `.github/workflows/tests.yml`

**Interfaces:**
- Produces: operator-verifiable v6.1.0 release artifact.

- [ ] Add verifier checks for schema, permissions, feature flag, policy total, ledger consistency, private evidence, routes, and backup freshness.
- [ ] Run `php artisan test`, `php artisan migrate:fresh --seed`, `php artisan route:list`, `php artisan view:cache`, and `php artisan sullam:verify-release-v610`.
- [ ] Validate migration rollback on a disposable database and repeat migration.
- [ ] Record exact passed/blocked checks in the build report.
- [ ] Set `RELEASE` to `v6.1.0`, generate changed-files manifest, and create a ZIP excluding `.git`, `.env`, vendor, caches, and private uploads.

## Self-review

Semua keputusan desain memiliki task: alokasi dan ledger (2), maker-checker/bukti/transfer/laporan (3), privasi publik (4), dashboard/wali/antrean (5), UI mobile (6), backup/restore/pilot (7), serta gerbang rilis (8). Tidak ada placeholder implementasi. Nama model, service, route area, dan status konsisten dengan spesifikasi desain.
