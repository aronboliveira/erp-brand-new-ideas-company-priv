# NEXT STEPS

> Last updated: 2026-05-11
> **Cross-references:** [`KNOWN_ISSUES.md`](KNOWN_ISSUES.md) (open issues list) · [`CURRENT_WORKING_ISSUES.md`](CURRENT_WORKING_ISSUES.md) (completed sessions) · [`CURRENT_WORKING_ISSUES_WORK.md`](CURRENT_WORKING_ISSUES_WORK.md) (work journal) · [`RESOLVED_ISSUES.md`](RESOLVED_ISSUES.md) (resolved archive) · [`TODO_LATER.MD`](TODO_LATER.MD) (deferred items) · [`README.md`](README.md) (notes overview) · `_inc/laravel/.notes/.llms/.guidelines/` (coding patterns) · [`.tmp/claude/20260808/HANDOFF.md`](../../../.tmp/claude/20260808/HANDOFF.md) (latest agent handoff)

---

## REMAINING TEST-SUITE WORK (Codex continuation state)

Claude session `d9570c96-1845-40c3-b642-65229ebd51b9` continued past the
older `a0243988d` handoff and stopped at HEAD `157145304` because of
usage/token limits while waiting on PHPUnit verification. Codex continued from
that state on 2026-05-09 and fixed the remaining PHPUnit error/failures.

Latest local full unit result:

```text
Tests: 10575, Assertions: 20354, Errors: 0, Failures: 0,
Deprecations: 38, Skipped: 8, Incomplete: 4.
```

The up-to-date Codex handoff lives in
[`.tmp/codex/20260510/handsoff.md`](../../../.tmp/codex/20260510/handsoff.md).

| Cluster | Sites | Disposition |
|---|---:|---|
| Order UUID primary-key test | 0 current failures | RT-007 — resolved 2026-05-09 with unique per-test fields |
| LandingPage Discover upload-failure test | 0 current failures | RT-008 — resolved 2026-05-09 with explicit local storage settings fixture |
| LandingPage Features featureStore test | 0 current failures | RT-009 — resolved 2026-05-09 with explicit local storage settings fixture |
| ProjectTaskTest aliasMock(User/DB) | 2 skips | RT-010 — needs real-fixture refactor |
| ProjectReportTest aliasMock(User/Milestone/TaskStage) | 3 skips | RT-010 — needs real-fixture refactor |
| ProductServiceCategoryTest aliasMock(PSC/Bill/DB) | 3 skips | RT-010 — needs real-fixture refactor |
| `markTestIncomplete` placeholders | 4 incomplete | RT-011 — re-enumerate before changing |

Stale items now closed by later Claude commits:

- Old RT-004 (`UtilityTest` 6 failures) — closed; final
  `tests/Unit/app/Models/utils` tally is clean.
- Old RT-005 (PHPStan 12 stale `App\Models\Bills\BillProduct` errors) —
  closed by `1e73c3e13`.
- Old ESLint 34 warnings — closed by `1e73c3e13`.

---

## IMMEDIATE

1. ~~**Run Playwright E2E**~~ — ✅ DONE (9 passed, 3 skipped, 3.6 min)
2. ~~**Run curl timing**~~ — ✅ DONE (40+ routes tested, no 5xx, all security headers present)
3. ~~**Fix BillProduct class redeclaration**~~ — ✅ RESOLVED (commit `c28f9474e`)
4. ~~**Fix MessagesController missing**~~ — ✅ RESOLVED (stale flag — class loads correctly)
5. ~~**Test shared-link password flow**~~ — ✅ RESOLVED 2026-05-07 (legacy base64 fallback added at `ProjectController::projectLink` — strict base64-decode + bcrypt-prefix gate + on-the-fly rehash; only project copy-link had a gate, invoice/proposal share URLs have none)
6. ~~**Replace JS route files**~~ — ✅ RESOLVED (commits `1312ce60`, `cf8bf4f9`, `941ebb14`; verified 2026-05-07; `dist-iife/` is stale build artifact, do not re-rsync)
7. ~~**ESLint ignores**~~ — ✅ RESOLVED 2026-05-07 (added `frontend/**` and `.history/**`; the AGENTS.md-listed `ts/**`, `.backup/**`, `Modules/**` were already present; 14,867 errors → 0 errors at HEAD `ebda9acd7`)
8. ~~**Fix 7 PHPUnit failures**~~ — ✅ RESOLVED 2026-05-07 (verified at HEAD `ebda9acd7`; all 7 pass in isolation; archived in `RESOLVED_ISSUES.md`)
9. ~~**Finish Claude's remaining Unit-suite failures**~~ — ✅ RESOLVED 2026-05-09 (RT-007..RT-009 fixed; `tests/Unit` now 0 errors / 0 failures)
10. **3-way merge of 531 overlapping files** — PHPStan annotations + agent crash-prevention patterns. See `AGENT_BRANCH_MERGE_LOG.md`.
11. **Review and apply agent's 2,832 file deletions** — Mainly TS rollback from agent branch.

## RECENTLY COMPLETED (2026-05-11)

### Warehouse/Products Reliability Slice

- Manual stock adjustments now use `WarehouseOperationService`, committing the
  stock mutation, operation ledger, validation step, and `warehouse.operations`
  outbox intent together.
- Product/service create/update/delete/import now validate decisive catalog
  fields such as SKU, quantity, sale/purchase price, tax, unit, category, type,
  and chart accounts before outbox dispatch.
- Warehouse transfer create/delete/update now use the warehouse wrapper. Quantity
  and source/destination/product changes are blocked in update because they need
  a new controlled stock movement.
- Warehouse deletion is guarded against existing stock rows or transfer
  references before the lifecycle operation is recorded.
- Purchase create/update/delete and individual purchase-line deletion now cover
  stock commits/reversals; POS finalization now covers stock consumption.
- `WarehouseOutboxDispatcher` drains `warehouse.operations` rows through
  monolith-local stock projection, reconciliation, replica-sync, logistics,
  valuation, catalog replica, finance bridge, customer/supplier projection, and
  webhook signals.
- New command: `php artisan reliability:dispatch-warehouse-outbox`.
- Warehouse quarantine stays narrow: only persistent retry/circuit/dead-letter/
  failed-ledger or long-running instability in high-impact stock/product/
  transfer/bulk-import/purchase/POS/warehouse lifecycle rows can route to manual
  review.
- Verification: focused warehouse reliability test is green (5 tests, 31
  assertions), the reliability service suite is green (35 tests, 203
  assertions), touched warehouse/product controller tests are green (410 tests,
  486 assertions), full `tests/Unit --no-coverage` is green (10,618 tests,
  20,591 assertions), `composer phpstan` has no errors, and `php artisan list
  --raw` registers `reliability:dispatch-warehouse-outbox`.
- Broad verification also closed two factory uniqueness fixture collisions:
  `CustomerFactory` now emits UUID-based emails, and `BranchFactory` now emits
  UUID-based names.

Next reliability work after the finance, HRM, and warehouse/products slices:

1. Add domain-specific journal-entry posting callbacks behind accepted
   finance journal-control signals.
2. Add banking adapter shells that can be toggled between no-op, sandbox, and
   real providers.
3. Add actual reversal/compensation handlers for deleted/failed payment flows.
4. Add HRM-specific compensation handlers for payroll/access-control failures
   instead of only marking `hrm.compensation.required`.
5. Add warehouse-specific reconciliation/compensation handlers behind stock
   projection, replica-sync, and catalog projection shells.
6. Extend the pattern to project closure/finalization and CRM decision workflows
   using the same overhead discipline.

---

## RECENTLY COMPLETED (2026-05-10)

### Finance Reliability Dispatcher Slice

- Finance invoice/bill payment create/delete now run through
  `FinanceOperationService`, committing the domain mutation, operation ledger,
  operation step, and finance outbox intent together.
- `FinanceOutboxDispatcher` drains `finance.ledger` rows through monolith-local
  signals for journal control, banking API shells, communication API shells,
  ledger reversal review, and webhook shells.
- Post-commit dispatch failures now use simple retry scheduling, then
  `dead_letter` plus `finance.compensation.required` / `compensating` ledger
  state when attempts are exhausted.
- Client redirects flash `reliability_operation`; the admin footer loads
  `public/assets/js/routes/reliability/operation-feedback.js` to show a
  SweetAlert/progress-bar status modal and poll the operation status route.
- New command: `php artisan reliability:dispatch-finance-outbox`.

### Retry and Circuit Breaker Slice

- Added Spring-like `Retry` and `CircuitBreaker` builder APIs under
  `app/Services/Reliability/`.
- Added durable `circuit_breaker_states` and `circuit_breaker_calls` tables for
  medium/high/critical guarded paths; trivial/low paths stay disabled by
  default.
- Retry now emits success, retrying, and final-failure operational events.
  Retry intervals default to capped exponential backoff through
  `ReliabilityPolicy::retryDelaySeconds()` unless a caller explicitly
  overrides the interval resolver. Circuit breaker emits state-change, opened,
  and rejected-call events.
- Finance outbox signal dispatch now uses retry plus circuit breaker guards
  before durable outbox retry/dead-letter/compensation handling.
- Retention now prunes expired circuit calls and closed/disabled circuit
  states.

### HRM Reliability Slice

- Salary/payroll updates now use `HrmOperationService`, committing the salary
  mutation, operation ledger, validation step, and HRM outbox intent together.
- Termination lifecycle create/update/delete now use the HRM reliability wrapper
  and validate employee link, termination type, dates, and delete result before
  outbox creation.
- Leave status decisions now use the HRM wrapper and validate the leave row,
  employee link, dates, total days, and non-empty status.
- `HrmOutboxDispatcher` drains `hrm.operations` rows through monolith-local
  payroll, finance payroll bridge, access/RBAC, calendar, communication,
  employee-record, and webhook signals.
- New command: `php artisan reliability:dispatch-hrm-outbox`.
- HRM quarantine stays narrow: employees without linked users are valid;
  linked-user/RBAC mismatches or payroll/lifecycle corruption can route to
  manual review only after repeated retry/circuit/dead-letter/failed-ledger
  instability.

Next reliability work after the finance and first HRM slices:

1. Add domain-specific journal-entry posting callbacks behind the accepted
   journal-control signal.
2. Add banking adapter shells that can be toggled between no-op, sandbox, and
   real providers.
3. Add actual reversal/compensation handlers for deleted/failed payment flows.
4. Add HRM-specific compensation handlers for payroll/access-control failures
   instead of only marking `hrm.compensation.required`.
5. ~~Extend the pattern to products and warehouse/stock workflows~~ — resolved
   2026-05-11. Remaining: project closure/finalization and CRM decision
   workflows using the same overhead discipline.

---

## RECENTLY COMPLETED (2026-05-02)

### Documentation Synchronization

- **`where-to-update-and-read.yml`** — Updated tree to include all `_inc/laravel/utils/` sub-items (`cmds/`, `assets/`, `caches/`, `containers/`, file pattern docs), added root `notes/`, `.notes/`, `utils/` directories, fixed CSS reference (`.toml` → `.md`), fixed RESOLVED_ISSUES.md path, cleaned audit formatting.
- **Root `README.md`** — Fixed `_inc/utils/` tree (was showing non-existent files), removed non-existent files from `notes/` listing, updated utility scripts table (regexes.md→regexes.txt, added cli/grep/find/regex dirs), fixed the old upstream subpath to `origin/erp/`, added `.notes/` section.
- **`.notes/README.md`** — Removed references to non-existent `agents/` and `plans/` subdirectories. Added `.llms/.guidelines/` reference.
- **`notes/` files** — Added stale-copy warnings pointing to canonical `_inc/laravel/.notes/` versions.
- **`_inc/laravel/README.md`** — Updated test results to current baselines, expanded project structure with `Services/`, `Contracts/`, `Exceptions/`, `tests/e2e/`, `tests/python/`, `utils/`, `.notes/`.
- **`_inc/laravel/utils/README.md`** — Fixed regex reference (`regexes/`→`regex/` + `regexes.txt`), added `cmds/`, `caches/`, `js/`, `php/`, `ts-harness/` entries.
- **`_inc/laravel/.notes/*`** — Updated timestamps to 2026-05-02, marked BillProduct and MessagesController as resolved in CURRENT_WORKING_ISSUES.md and NEXT_STEPS.md.

## RECENTLY COMPLETED (2026-03-15)

### Utility Delegation + Problems Panel Cleanup + Import DRYing

- **68 methods** extracted from `Utility.php` into 6 service classes under `app/Services/Utility/`
- **Utility.php** reduced from 4,282 to 1,828 lines; all stubs preserved with `@see` references
- **Problems Panel**: 895+ errors → **0 errors** across all PHP files
- **30+ unused imports** removed from `Utility.php`, `FinanceBillingService.php`, `UtilityTest.php`
- **Type fixes**: `(int)$areaCode`, `(string) rand()` for `str_pad`, `@var` annotations for Mockery/Storage
- **IDE fixes**: 10+ files — missing imports, unused imports, wrong namespace references
- **mysql-schema.sql**: Suppressed 72 false-positive SQL linter errors via `.vscode/settings.json`
- **Chart of Account seeding**: `ChartOfAccountType` UUID-guarded ID fix (`$rec->id = $id; $rec->saveQuietly()`)
- **Test assertion fixes**: 13+ number format prefix mismatches (`#` → `INV-`, `BILL-`, etc.)
- **Cache/logs**: Full clear (composer, artisan, PHPStan, npm, view, bootstrap, debugbar, storage/tmp)
- **File archival**: 9 outdated scan files moved to `.notes/.history/` and `.notes/.llms/.history/reports/`

## RECENTLY COMPLETED (2026-03-14)

### Calendar Mock Infrastructure + Test Rewrites

- **CalendarGateway pattern**: Interface + `GoogleCalendarGateway` + `MockCalendarGateway` + `CalendarService` with DI
- **14/14 calendar tests passing**: All rewritten to use `CalendarService::setGateway()`, `MockCalendarGateway` fixtures, `updateOrInsert()` + `resetSettingsCache()`
- **IDE error fixes**: AllowanceController, unused imports, DB imports
- **Test suite**: 395/422 passed (93.6%), 0 risky, 21 accounting failures (pre-existing)
- **Notes/docs**: 14 files moved to `.history/`, 4 files updated (KNOWN_ISSUES, CURRENT_WORKING_ISSUES, NEXT_STEPS, typescript-migration)

## RECENTLY COMPLETED (2026-03-11)

### TypeScript Migration — Gap Closure (7-point plan)

- **1,097/1,097 TS routes** — Full parity with JS routes, 0 tsc errors
- **Core singletons:** `erp-bootstrap.ts`, `erp-guard.ts`, `erp-utils.ts` + barrel `index.ts` (dedup 601 toast, 420 guard, 204 Window augmentation patterns)
- **ESM→IIFE script:** `ts/scripts/esm-to-iife.cjs` — converts 1,102 ESM files to IIFE; output in `ts/dist-iife/`
- **Guidelines:** `.notes/.llms/.guidelines/frontend/esm-iife-strategy.md`, `template-literal-testing.md`
- **Integration tests:** Mock API server (22 endpoints) + 20 Playwright specs — all passing
- **Rollback scripts:** `.backup/scripts/{bash,python,node,php}/20260309_014820/`
- **Harness + tests:** 204 new harness HTML pages, 204 Playwright specs, 204 Jest unit tests
- **Jest totals:** 319/322 suites, 1,458/1,458 tests (3 pre-existing failures unrelated to migration)
- **Build:** 1,127 JS files in `ts/dist/`, 1,102 IIFE files in `ts/dist-iife/`

---

## DEFERRED (monitoring only)

| Item                                        | Effort  | Notes                                                                                      |
| ------------------------------------------- | ------- | ------------------------------------------------------------------------------------------ |
| RoleController Permission scoping           | Trivial | Spatie permissions are global; not a true IDOR                                             |
| ProjectController::projectLink tenant scope | N/A     | Public endpoint by design — encrypted URL is access control                                |
| Individual seeder testing (184 seeders)     | Medium  | Originals in `.backup/database/seeders/`. Live copies can be edited for current test needs |
| CSP nonce-based implementation (D-1)        | Major   | Requires nonce injection in all Blade views                                                |

## RECENTLY COMPLETED (2026-03-07)

### Readonly Scan + Log Archival

- **PHP lint:** 0 errors / 1,417 files
- **ESLint:** 0 errors 0 warnings ✅
- **Jest:** 10 / 10 ✅
- **Pytest (bash):** 53 / 53 ✅
- **HTTP smoke (20 routes):** 0 × 500 ✅
- **Log archival:** `.notes/*.txt/log` → `.notes/.llms/.history/reports/`; `_inc/laravel/.notes/` all dated logs → `_inc/laravel/.notes/.history/` (created)
- **Codex report integrated:** see `.tmp/codex/report-20260305-2/` and CURRENT_WORKING_ISSUES.md

## RECENTLY COMPLETED (2026-03-07 — ESLint + RBAC + HTTP 500 fix batch)

- ESLint 758 warnings → 0; Playwright RBAC 5 tests fixed; HTTP 500s fixed (4 routes); `.gitignore` cleanup

## RECENTLY COMPLETED (2026-03-04)

### PHPUnit & PHPStan Stabilization

- **PHPUnit Feature (DashboardDataTest):** 26/26 tests, 88 assertions, 0 failures
- **PHPStan Level 2:** 0 errors on BillController (down from 80 — @property annotations on 8 models)
- **PHPStan Level 3:** Module-by-module runner created (`scripts/phpstan-modules.sh`)
- **Test DB:** `erp_brand_new_ideas_company_test` — 210 tables, 215 migrations, all passing
- **Factory files:** 7 created (Bill, Customer, Vendor, Employee, Invoice, Revenue, BankAccount)
- **Scripts:** Added PHPStan/PHPUnit/pytest/curl commands to `composer.json` and `package.json`

### Prior: Intelephense / VS Code (2026-03-06)

14 fixes across 12 files — import aliases, static properties, case fixes, types, test bugs.

---
## Reliability follow-ups (updated 2026-05-11)

- CRM: `DealController` user/client link sub-actions and `permissionStore()`
  can now be wired to the validator-supported
  `crm.deal.user_*`, `crm.deal.client_*`, and
  `crm.deal.permission_changed` events.
- CRM-adjacent relationship records: scan customer/vendor/client lifecycle
  controllers separately; do not treat transient communications or labels as
  quarantine candidates by default.
- Project/planning: next high-impact cluster should prioritize irreversible
  project closure/finalization/deletion and any approval/final state workflows.
