# Resolved Issues Archive

> Issues that have been fully fixed and verified. Append new entries at the top.
> Last updated: 2026-05-11
> **Cross-references:** [`KNOWN_ISSUES.md`](KNOWN_ISSUES.md) (formerly open issues) · [`CURRENT_WORKING_ISSUES.md`](CURRENT_WORKING_ISSUES.md) (bug-fix sessions) · [`CURRENT_WORKING_ISSUES_WORK.md`](CURRENT_WORKING_ISSUES_WORK.md) (try/fail journal) · [`NEXT_STEPS.md`](NEXT_STEPS.md) (remaining tasks) · [`README.md`](README.md) (notes overview)

---

## [2026-05-11] Warehouse/products reliability slice: stock/product/transfer controls

Added the first warehouse/products adoption of the reliability layer without
treating every catalog or warehouse screen as quarantine-worthy:

- New warehouse services: `WarehouseOperationService`,
  `WarehouseReliabilityPolicy`, `WarehousePostWriteValidator`,
  `WarehouseOutboxDispatcher`, `WarehouseCompensationService`,
  `WarehouseOperationResult`, and `WarehouseReliabilityAssessment`.
- New command: `php artisan reliability:dispatch-warehouse-outbox`.
- Stock adjustments, decisive product/service catalog changes, product imports,
  warehouse transfers, guarded warehouse deletion, purchase stock
  commits/reversals including purchase-line deletion, and POS stock commits now
  commit a ledger, post-write validation step, and `warehouse.operations` outbox
  intent together.
- Warehouse outbox dispatch emits monolith-local stock projection,
  reconciliation, replica-sync, logistics, valuation, catalog replica, finance
  bridge, customer/supplier projection, and webhook signals.
- Warehouse quarantine remains manual-review only and requires persistent
  retry/circuit/dead-letter/failed-ledger or long-running instability in
  high-impact stock/product/warehouse rows.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/WarehouseReliabilityTest.php --no-coverage` — 5 tests, 31 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 35 tests, 203 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Http/Controllers/products/ProductStockControllerTest.php tests/Unit/app/Http/Controllers/products/ProductServiceControllerTest.php tests/Unit/app/Http/Controllers/activity/WarehouseTransferControllerTest.php tests/Unit/app/Http/Controllers/companies/WarehouseControllerTest.php tests/Unit/app/Http/Controllers/activity/PurchaseControllerTest.php tests/Unit/app/Http/Controllers/activity/PosControllerTest.php --no-coverage` — 410 tests, 486 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,618 tests, 20,591 assertions, OK.
- `php artisan list --raw` — confirms `reliability:dispatch-warehouse-outbox`.
- `composer phpstan` — no errors.

Broad verification surfaced two pre-existing factory uniqueness collisions and
closed them without touching migrations or seeders: `CustomerFactory` now emits
UUID-based emails, and `BranchFactory` now emits UUID-based names.

Guideline:
`_inc/laravel/.notes/.llms/.guidelines/backend/reliability-outbox-ledger.md`.

---

## [2026-05-10] HRM reliability slice: payroll/lifecycle/leave controls

Added the first HRM adoption of the reliability layer without broadening
quarantine to routine HR screens:

- New HRM services: `HrmOperationService`, `HrmReliabilityPolicy`,
  `HrmPostWriteValidator`, `HrmOutboxDispatcher`, `HrmCompensationService`,
  `HrmOperationResult`, and `HrmReliabilityAssessment`.
- New command: `php artisan reliability:dispatch-hrm-outbox`.
- Salary/payroll updates, termination lifecycle create/update/delete, and leave
  status decisions now commit a ledger, post-write validation step, and
  `hrm.operations` outbox intent together.
- Shared quarantine routing now emits domain-specific events such as
  `hrm.quarantine.manual_review`.
- HRM post-write validation treats employees without linked users as valid;
  only non-null linked-user/RBAC mismatches can fail the invariant, and
  quarantine requires persistent retry/circuit/dead-letter/failed-ledger
  instability.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/HrmReliabilityTest.php --no-coverage` — 6 tests, 30 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 30 tests, 172 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Http/Controllers/planning/SetSalaryControllerTest.php tests/Unit/app/Http/Controllers/planning/LeaveControllerTest.php tests/Unit/app/Http/Controllers/planning/TerminationControllerTest.php --no-coverage` — 136 tests, 163 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,613 tests, 20,560 assertions, OK.
- `composer phpstan` — no errors.

Guideline:
`_inc/laravel/.notes/.llms/.guidelines/backend/reliability-outbox-ledger.md`.

---

## [2026-05-10] Reliability foundation: operation ledger + outbox/inbox

Added a generic reliability layer for high-impact business operations:

- New durable tables: `operation_ledgers`, `operation_steps`,
  `outbox_messages`, `inbox_messages`, `operational_events`.
- New models: `OperationLedger`, `OperationStep`, `OutboxMessage`,
  `InboxMessage`, `OperationalEvent`.
- New services under `app/Services/Reliability/` for critical operation
  wrapping, outbox/inbox tracking, operational events, criticality policy, and
  retention/compression.
- `LedgerActionService` now wraps client invoice, supplier bill, and client
  receipt postings as critical operations with step logs and outbox intent.

Verification:

- `php artisan migrate --path=database/migrations/2026_05_10_090000_create_reliability_outbox_and_operation_tables.php --force` — OK.
- `php artisan migrate --env=testing --path=database/migrations/2026_05_10_090000_create_reliability_outbox_and_operation_tables.php --force` — OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 7 tests, 48 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/Ledger/LedgerActionServiceTest.php --no-coverage` — 2 tests, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,586 tests, 20,432 assertions, OK.
- `composer phpstan` — no errors.

Guideline:
`_inc/laravel/.notes/.llms/.guidelines/backend/reliability-outbox-ledger.md`.

---

## [2026-05-09] Codex continuation: remaining Unit-suite failures resolved

Codex continued from Claude session `d9570c96-1845-40c3-b642-65229ebd51b9`
after the token-limit stop at `157145304`.

Resolved items:

- RT-007: `OrderTest::order_uses_uuid_for_primary_key` no longer reuses fixed
  unique fields; the test now generates per-run unique order/email/receipt
  values.
- RT-008: Discover upload-failure test now seeds the local storage driver,
  mime validation list, and max upload setting before asserting the intended
  `pdf` validation failure.
- RT-009: Features `featureStore` upload test now seeds the same local storage
  settings and ensures the local landing-page upload directory exists.

Verification:

- `php -l` on all 3 touched test files — clean.
- `tests/Unit/app/Models/activity/OrderTest.php` — 7 tests, OK.
- `tests/Unit/app/Http/Controllers/LandingPage/DiscoverControllerTest.php` —
  14 tests, OK.
- `tests/Unit/app/Http/Controllers/LandingPage/FeaturesControllerTest.php` —
  19 tests, OK.
- `tests/Unit/app/Models/activity` — 172 tests, OK.
- `tests/Unit/app/Http/Controllers/LandingPage` — 102 tests, OK.
- Full `tests/Unit --no-coverage` — 10,575 tests, 20,354 assertions,
  0 errors, 0 failures, 38 deprecations, 8 skipped, 4 incomplete.
- `composer phpstan` — no errors.
- `npx --no-install eslint . --max-warnings=50` — exit 0.

Handoff: `.tmp/codex/20260509/handsoff.md`.

## [2026-05-08 → 2026-05-09] Claude PHPUnit/static-analysis sweep through token limit

15 commits between `5a6c9afac` and `157145304`. The case study at
`.history/case-study/claude-main-agent/20260807-20260808/case-study.md`
is accurate through `a0243988d`; the updated token-limit handoff at
`.tmp/claude/20260808/HANDOFF.md` is authoritative for the final state.

**Final captured PHPUnit state:** `tests/Unit/` reached 10,575 tests,
20,348 assertions, 1 error, 2 failures, 8 skips, 4 incomplete. The remaining
open items are listed in `KNOWN_ISSUES.md` as RT-007..RT-011.

**Skip reduction:** 78 baseline skip sites were reduced to 8 fired skips in
the final full unit run. Most aliasMock/static-service skips were converted
to real fixture coverage.

**Post-case-study continuation:**

- `1e73c3e13` — PHPStan and ESLint blockers cleared. This supersedes the old
  RT-005 and ESLint-warning sections in the stale handoff.
- `2bc9199cd`, `002d2d518`, `157145304` — UtilityTest and settings leakage
  fixes. This supersedes the old RT-004 UtilityTest failure cluster.

**Production fixes** (each with a unique ID; see case-study `summary.yml`):

- **PR-001** — `warehouse_products` UNIQUE on `(warehouse_id, product_id)`
  pair instead of `product_id` alone. The original constraint silently
  blocked `warehouseTransferQty()` (which inserts a second row at the
  destination warehouse before deleting the source row). Migration
  patched + test DB re-aligned.
- **PR-002** — `ProjectsConstants::COL_STG` reconciled from `'stage'`
  → `'stages'` (the JSON column declared by the migration). 5 raw-SQL
  sites + 1 Eloquent site in `ProjectRequestService` switched to
  `JSON_CONTAINS(col, JSON_QUOTE(?))` / `whereJsonContains()`.
- **PR-004 + PR-005** — `Customer ↔ User` bridge. New migration
  `2026_05_08_120000_add_user_id_to_customers_table` adds nullable
  `customers.user_id` FK to `users.id` (nullOnDelete).
  `Customer::creatorId()` now defers role decisions to the linked User
  when present. Codifies the project rule "a Customer who logs in is a
  `users` row with `type = customer`" (see `App\Enums\UserType`).
- **PR-006** — `TimeTracker::projectName` accessor reads `'name'` (the
  real `projects` column) instead of nonexistent `'project_name'`.

**Patterns codified:**

- Static-Closure test seam (`?\Closure $override` + `resetTestSeams()`)
  for static methods that can't be `aliasMock`'d once the class is
  hot-loaded. Examples: `CalendarService::$saveEventOverride`,
  `NotificationService::$sendTwilioOverride`.
- `## ! MOCKING REAL PROD SECRET` sentinel on any test that substitutes
  a real production secret/key/token.
- Real DB seeding over `aliasMock` for hot-loaded classes.

**Guidelines added:**

- `AGENTS.md` — "Migrations are the source of truth" + "Mandatory
  constant aliases" (with full alias table).
- `_inc/laravel/.notes/.llms/.guidelines/database/migration-source-of-truth.md`
  — detailed doc with worked examples.
- `_inc/laravel/.notes/.llms/.guidelines/backend/constant-aliases.md`
  — full alias table + rationale + import-block style.

---

## [2026-05-07] PHPUnit skips triage (Task F from AGENTS.md)

Triage delivered at
`_inc/laravel/.notes/.llms/.guidelines/testing/skips-triage-2026-05-07.md`.

Findings:
- 93 static `markTestSkipped` sites; 78 fire at runtime.
- ~44 fixture-gap guards (no admin/SA user) — intentional, permanent.
- ~16 external-API guards (Google/Twilio/Pusher/Benefit) — permanent.
- ~5 architectural (aliasMock/final-class/overload) — permanent per
  `constraints.md` (project chose static-cache pattern).
- ~5 realistic fixable candidates (3 Blade snake_case mismatches + Project
  getProgressColor relation stub + InterviewSchedule boot hang). A future
  skip-reduction pass could realistically drop 78 → ~73.

Two AGENTS.md recipes are stale and should not be executed:
- `customers.type` 15-skips: zero current skips mention `customers.type`.
- `File::ensureDirectoryExists` 6-skip stub: the Chatify skips primarily need
  routes registered, not just a writable directory. Removing without wiring
  routes converts skip → error. `tests/TestCase.php` already stubs Chatify
  vendor files.

## [2026-05-07] Shared-link password base64→bcrypt migration (Task E from AGENTS.md)

`projectLink()` in `app/Http/Controllers/Planning/ProjectController.php` was
the only share-link password gate (invoice/proposal share URLs have no
password gate). Added a legacy fallback inline:

```php
$ok = Hash::check($entered, $stored);
if (!$ok && $stored !== '' && !preg_match('/^\$2[ayb]\$/', $stored)) {
    $decoded = base64_decode($stored, true); // strict
    if ($decoded !== false && hash_equals($decoded, $entered)) {
        $project->password = Hash::make($entered);
        $project->saveQuietly();
        $ok = true;
    }
}
```

Verified via `php -l` (clean), `vendor/bin/phpunit --filter='projectLink|PRJ_LNK'`
(7/7 OK), `vendor/bin/phpstan analyse ... --level=3` (clean), and a standalone
logic smoke covering bcrypt-prefix detection + strict base64 garbage rejection.

Open follow-up: a Feature test against a real DB Project with a base64
password would lock the migration path. Out of scope for the fix.

## [2026-05-07] JS Routes IIFE deployment (Task D from AGENTS.md)

Verified resolved before this session via git history:
`1312ce60 build(ts): deploy TS-compiled JS to production paths`,
`cf8bf4f9 fix(lint): ... strip @typescript-eslint inline comments from 210 JS files`,
`941ebb14 fix(build): strip TS-only eslint-disable directives in esm-to-iife.cjs`.

`ts/dist-iife/` is now a stale build artifact (1,102 files, 905 identical to
live, 197 cosmetic-only diffs). **Do not rsync it over `public/assets/js/routes/`** —
it would undo the post-process comment strip. Regenerate via
`node ts/scripts/esm-to-iife.cjs` if a fresh dist-iife snapshot is needed.

Core singletons (`erp-guard.js`, `erp-utils.js`, `erp-bootstrap.min.js`) are
loaded with `defer` in head section of 5 Blade layouts (`admin`, `auth`,
`contract_header`, `share_project`, `landing`); they execute before footer
route scripts by virtue of `defer`'s document-order execution guarantee.

**Open follow-up (not part of D):** 6 comma-typo orphan files in
`public/assets/js/routes/` (`store,js`, `note,js`, etc.) are git-tracked but
unreachable as URLs. Worth a small cleanup commit.

## [2026-05-07] ESLint scope too wide (Task C from AGENTS.md)

`eslint.config.mjs` already ignored `ts/**`, `.backup/**`, `Modules/**`. The
actual leak was the `frontend/` Next.js sub-app (own `eslint.config.js`) plus
gitignored `.history/` archives — together producing **14,867 errors**.

**Fix:** added `"frontend/**"` and `".history/**"` to the global `ignores`.
Did NOT add blanket `public/**` because the IIFE route layer at
`public/assets/js/routes/` is intentionally linted (matcher block lines 186-211).

**Verification:** `npx eslint .` reports `0 errors, 34 warnings` (warnings are
legitimate unused-var hints in 9 real source files, kept on purpose).

## [2026-05-07] 7 PHPUnit failures (Task B from AGENTS.md)

All 7 previously-failing tests verified passing in isolation at HEAD `ebda9acd7`:

| Test | Result |
|---|---|
| `MassAssignmentTest` | 186/186 OK |
| `BugTest` | 10/10 OK |
| `EmailTest` | 16/16 OK |
| `JobStageTest` | 2/2 OK |
| `ProductServiceUnitTest` | 2/2 OK |
| `LabelTest` | 3/3 OK |
| `GeneratedOfferLetterTest` | 6/6 OK (2 expected skips) |

Resolved during the OpenCode/DS sessions (see
`.tmp/ds/20260506-20260507/handsoff/handoff.md`); CI is green and PHPUnit shows
12,690 / 0 / 0 / 78-skip locally. Only residual noise is 38 PHP 8.4 deprecation
warnings (informational, Laravel 10 baseline).

## [2026-05-07] Bills Models Namespace Finalization (Task A from AGENTS.md)

`app/Models/Bills/` removed; `git status` clean of untracked Bills models;
zero references to `App\Models\Bills\` in `app/` or `tests/`.
Commit: `614408e9a refactor(models): migrate Bills namespace`

---

## [2026-05-02] Documentation files out of sync with project state

Canonical mappings (`where-to-update-and-read.yml`, root `README.md`,
`_inc/laravel/README.md`, `_inc/laravel/.notes/*`) updated to reflect current
directory structure, file paths, test results, and resolved issues.
All stale references corrected across trilingual documentation.

---

## [2026-05-01] MessagesController Missing (STALE FLAG)

Verified that `MessagesController` is correctly recognized by `php artisan route:list`.
The class exists at `app/Http/Controllers/Contact/MessagesController.php` and is loaded
correctly despite the PSR-4 subdirectory mismatch (likely via classmap).
Commit: `[current-session]`

## [2026-05-01] BillProduct wrong namespace (App\Models → App\Models\Bills)

Fixed namespace declaration in `app/Models/Bills/BillProduct.php` and updated callers
in `app/Models/Bills/Bill.php` and `app/Models/utils/Utility.php`.
Commit: `c28f9474e`

## [2026-04-26] CompetenciesTest fillable assertion stale

Expected fillable array in `tests/Unit/app/Models/individuals/CompetenciesTest.php`
was missing `'code'` after the model gained a unique-code booted() hook.
Commit: `ac0da3f03`

## [2026-04-26] Playwright CI — port 3847 mismatch, race conditions, serial skip bypass

Four independent root causes fixed: mock server port 3847→3000, waitForSelector race,
live-server guard on CI, describe.serial beforeAll skip placement.
Commits: `ccad86a08`, `57225a7d0`, `aebdd88ae`

## [2026-04-26] GitHub Actions Node 24 warnings — stale action versions

Bumped actions/checkout, actions/cache, actions/setup-node, setup-python,
docker/setup-buildx, docker/build-push to current major versions.
Commits: `99c867344`, `3957967e0`
