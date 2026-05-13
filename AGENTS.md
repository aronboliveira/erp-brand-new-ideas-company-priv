# AGENTS.md — Task Fork Document
# Branch: main | Generated: 2026-05-07
# Claude session reset — forking unfinished work to fresh agent(s).
#
# READ FIRST:
#   .tmp/codex/20260509/handsoff.md — Codex continuation (remaining Unit failures closed)
#   .tmp/claude/20260504/handoff.md   — Claude context handoff (models migration, BillProduct, test suite)
#   .tmp/opencode/ds/20260507_handsoff-update.md  — DS agent final state (CI green)
#   .tmp/opencode/bp/20260507_handsoff-update.md  — BP agent confirmation
#   where-to-update-and-read.yml      — Canonical filesystem map
#   _inc/laravel/.notes/NEXT_STEPS.md — Task backlog (updated 2026-05-02)
#   _inc/laravel/.notes/KNOWN_ISSUES.md — Open bugs

---

## ORIENTATION

- **App root:** `_inc/laravel/`
- **Branch:** `main` (stay here — never develop in origin/ or _old/)
- **DB:** `erp_brand_new_ideas_company_db` (MySQL 8.4)
- **SA UUID:** `a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7` (DC::DEFAULT_UUID, `created_by` in all seeders)
- **PHP:** 8.4 | **PHPUnit:** 10.x | **Jest:** CJS + TS | **Playwright:** chromium

### Hard constraints (never violate)

```
✗ NEVER modify existing database/migrations/ files
✓ Add a new migration only when the current task explicitly requires schema expansion
✗ NEVER modify _inc/.seeders/
✗ NEVER use php artisan test  (destroys seeded DB data — use vendor/bin/phpunit)
✗ NEVER run migrate:fresh unless explicitly asked
```

### Safe test commands

```bash
cd _inc/laravel
php vendor/bin/phpunit --testsuite=Unit --no-coverage          # full unit
php vendor/bin/phpunit tests/Unit/app/Models/bills --no-coverage  # bills only
php vendor/bin/phpunit --filter=BillProductTest --no-coverage   # single class
```

### Migrations are the source of truth

For ANY DB-touching code (Controllers, Models, Services, Tests), the
`database/migrations/*` files define the canonical schema. If a model
constant or production query reads a column that doesn't exist in the
migration, **the constant/query is the bug**, not the schema. Reconcile
by adjusting the constant or the query — never by adding the missing
column on the fly. Detailed rationale and worked examples live in
[`_inc/laravel/.notes/.llms/.guidelines/database/migration-source-of-truth.md`](_inc/laravel/.notes/.llms/.guidelines/database/migration-source-of-truth.md).

### Mandatory constant aliases

Imports of `App\Config\Constants\*` MUST use the project-wide aliases
below. Detailed rationale + import-block style live at
[`_inc/laravel/.notes/.llms/.guidelines/backend/constant-aliases.md`](_inc/laravel/.notes/.llms/.guidelines/backend/constant-aliases.md).

```
DatabaseConstants        as DC      |  ProjectsConstants    as PJC
BanksConstants           as BKC     |  PlansConstants       as PLC
BillsConstants           as BLC     |  PermissionsConstants as PMC
ActivitiesConstants      as AC      |  BaseRoutesConstants  as BRC
ChartsConstants          as CHTC    |  CompaniesConstants   as CPC
EmailsConstants          as EMC     |  ExtendingLayoutConstants as ELC
FormsConstants           as FMC     |  LandingPageConstants as LPC
LangsConstants           as LGC     |  MessagesConstants    as MGC
MiddlewaresConstants     as MWC     |  NotificationsConstants as NTC
RoutesKeysConstants      as RKC     |  ServicesConstants    as SVC
SettingsConstants        as SGC     |  StacksConstants      as SCC
SupportsConstants        as SPC     |  TemplatesConstants   as TPC
UsersConstants           as UC      |  ViewClassNamesConstants as VC
ViewsConstants           as VW      |  YieldingConstants    as YC
```

---

## CI STATUS (as of 2026-05-07, commit ebda9acd7)

All jobs green. CI run: 25510279854.

| Job | Result |
|---|---|
| Lint & Static Analysis | success |
| PHPUnit (0 errors, 1 failure via continue-on-error) | success |
| Jest CJS 693 + TS 949 | success |
| Python | success |
| Docker Build | success |

PHPUnit local: 12,690 tests, 0 errors, 0 failures, 78 skips.
PHPUnit CI: 12,678 tests, 0 errors, 1 failure (env-diff, continue-on-error), 78 skips.

Latest local continuation check (2026-05-09, Codex, HEAD `157145304` plus
working-tree test/doc fixes): `tests/Unit --no-coverage` is green:
10,575 tests, 20,354 assertions, 0 errors, 0 failures, 38 deprecations,
8 skipped, 4 incomplete. `composer phpstan` and
`npx --no-install eslint . --max-warnings=50` are also clean.

Latest reliability foundation check (2026-05-10, Codex): new migration applied
to app and test MySQL DBs; `tests/Unit/app/Services/Reliability` is green
(7 tests, 48 assertions), `tests/Unit/Ledger/LedgerActionServiceTest.php` is
green (2 tests), full `tests/Unit --no-coverage` is green
(10,586 tests, 20,432 assertions), and `composer phpstan` is clean.

Latest finance reliability dispatcher check (2026-05-10, Codex): invoice/bill
payment create/delete now use `FinanceOperationService`, local finance outbox
dispatch, durable retry/dead-letter, compensation-required state, and client
status feedback. `tests/Unit/app/Services/Reliability` is green
(11 tests, 73 assertions), `tests/Feature/FinancialRouteHardeningTest.php` is
green (275 tests, 288 assertions), full `tests/Unit --no-coverage` is green
(10,590 tests, 20,457 assertions), `composer phpstan` is clean, and
`npx --no-install eslint . --max-warnings=50` is clean.

Latest retry/circuit breaker reliability check (2026-05-10, Codex): added
Spring-like `Retry` and `CircuitBreaker` builders, durable
`circuit_breaker_states` / `circuit_breaker_calls`, retry/circuit operational
events, and finance outbox guard integration. Retry delays now default to capped
exponential backoff (30s, 60s, 120s, 240s, 300s cap), including bare retry
builders without `intervalUsing()`. `tests/Unit/app/Services/Reliability` is
green (19 tests, 115 assertions), invoice/bill controller reliability paths are
green (290 tests, 348 assertions), `composer phpstan` is clean, and
`npx --no-install eslint . --max-warnings=50` is clean. Full
`tests/Unit --no-coverage` previous broad baseline is green (10,598 tests,
20,492 assertions) before the retry-backoff test additions.

Latest finance quarantine check (2026-05-10, Codex): added finance-only
post-write quarantine overlay/audit tables, policy-gated payment post-write
validation starting at amount `3,200`, amount/metadata/user-risk-scaled finance
retry attempts, strict invalid rollback before outbox dispatch, and dispatcher
blocking for quarantined operation ledgers. Quarantine now requires persistent
corrupted-state/instability signals rather than a single validation failure.
Migration applied to app and test MySQL DBs.
`tests/Unit/app/Services/Reliability` is green (24 tests, 142 assertions),
full `tests/Unit --no-coverage` is green (10,607 tests, 20,530 assertions),
and `composer phpstan` is clean.

Latest HRM reliability slice check (2026-05-10, Codex): salary/payroll update,
termination lifecycle create/update/delete, and leave status decisions now use
`HrmOperationService`, `hrm.operations` outbox rows, post-write validation, and
HRM retry/circuit dispatcher support. Employee records without linked users are
valid; only persisted linked-user/RBAC mismatches can fail validation, and HRM
quarantine requires persistent retry/circuit/dead-letter/failed-ledger
instability before manual review. `tests/Unit/app/Services/Reliability` is
green (30 tests, 172 assertions), touched HRM controller tests are green
(136 tests, 163 assertions), full `tests/Unit --no-coverage` is green
(10,613 tests, 20,560 assertions), and `composer phpstan` is clean.

Latest warehouse/products reliability slice check (2026-05-11, Codex): manual
stock adjustments, decisive product/service catalog changes, product imports,
warehouse transfer create/update/delete, guarded warehouse deletion, purchase
stock commit/reversal including purchase-line deletion, and POS stock commit now
use `WarehouseOperationService`, `warehouse.operations` outbox rows, post-write
validation, and retry/circuit dispatcher support. Warehouse outbox dispatch emits
monolith-local stock projection, reconciliation, replica-sync, logistics,
valuation, catalog replica, finance bridge, customer/supplier projection, and
webhook signals. Quarantine remains rare: only persistent retry/circuit/
dead-letter/failed-ledger or long-running instability in high-impact stock,
transfer, bulk import, purchase/POS, or warehouse lifecycle rows can route to
manual review. `tests/Unit/app/Services/Reliability` is green (35 tests, 203
assertions), touched warehouse/product controller tests are green (410 tests,
486 assertions), full `tests/Unit --no-coverage` is green (10,618 tests,
20,591 assertions), and `composer phpstan` is clean.

Latest CRM reliability slice check (2026-05-11, Codex): lead create/update/
delete, lead stage movement, lead-to-deal conversion, deal create/update/delete,
deal stage movement, deal status changes, customer/vendor/client relationship
lifecycle rows, and deal user/client/permission sub-actions now use
`CrmOperationService`, `crm.operations` outbox rows, post-write validation where
the policy requires it, and retry/circuit dispatcher support. CRM outbox
dispatch emits local projection, client projection, relationship projection,
pipeline reconciliation, forecasting, access, project bridge, finance
opportunity/relationship bridge, communication, catalog-context, and webhook
shells. Quarantine remains narrow: only persistent instability plus core
corruption in conversion, final status, deal lifecycle, CRM access state, or
customer/vendor/client identity/link state can route to manual review.
`CrmReliabilityTest` is green (9 tests, 42 assertions), touched Customer/Client/
Vendor/Deal controller tests are green (594 tests, 703 assertions), and all
reliability service tests are green (44 tests, 245 assertions). Full
`tests/Unit --no-coverage` previous broad baseline is green (10,623 tests,
20,619 assertions). Post-relationship full Unit attempt reached 10,627 tests
and 20,632 assertions with one unrelated timing threshold failure in
`ContractControllerTest::test_noteStore_performance_114`; isolated rerun of
that test passed. `composer phpstan` is clean.

Latest project planning reliability slice check (2026-05-11, Codex): final
project status updates, irreversible project deletion, milestone final/delete
paths, task completion/final progress, and completed/final task deletion now use
`PlanningOperationService`, `planning.operations` outbox rows, post-write
validation where the policy requires it, and retry/circuit dispatcher support.
Planning outbox dispatch emits local projection, progress, schedule/calendar,
archive/access, CRM/client bridge, finance project-context bridge, reporting,
communication, and webhook shells. Quarantine remains manual-review only and
requires persistent instability plus core final project/milestone/task
corruption. `PlanningReliabilityTest` is green (6 tests, 30 assertions),
touched planning controller tests plus planning reliability are green (354
tests, 450 assertions), all reliability service tests are green (50 tests, 275
assertions), `php artisan list --raw` confirms
`reliability:dispatch-planning-outbox`, and `composer phpstan` is clean.

Latest heavy I/O reliability slice check (2026-05-11, Codex): shared Python
import delegation, shared Python export delegation, and shared webhook delivery
now use `HeavyIoOperationService`, `heavy_io.operations` outbox rows,
post-execution validation, and retry/circuit guard support while preserving
legacy array/string/bool return shapes. Heavy I/O outbox dispatch emits
integration health, audit, import reconciliation, replica-sync, data quality,
report archive, webhook audit, dead-letter monitor, and frontend progress
shells. Quarantine remains manual-review only and requires persistent
instability plus invalid high-impact import/export/webhook/callback results.
`HeavyIoReliabilityTest` is green (4 tests, 22 assertions), all reliability
service tests are green (54 tests, 297 assertions), Python delegation trait
tests are green (26 tests, 30 assertions), webhook utility tests are green (7
tests, 25 assertions), `php artisan list --raw` confirms
`reliability:dispatch-heavy-io-outbox`, full `tests/Unit --no-coverage` is
green (10,637 tests, 20,685 assertions), and `composer phpstan` is clean.

Latest finance extended-flow check (2026-05-11, Codex): revenue create/update/
delete, generic vendor payment create/update/delete, bank transfer create/
update/delete, purchase payment create/delete, credit/debit note create/update/
delete/custom-create, and journal entry/item create/update/delete paths now use
`FinanceOperationService`, `finance.ledger` outbox rows, post-write validation,
retry/circuit dispatcher support, and client feedback. Finance policy/validator
now understands revenue/payment/transfer/purchase-payment/note/journal amount,
subject, transaction mirror, and corruption keys. `FinanceExtendedFlowsTest` is
green (3 tests, 12 assertions), all reliability service tests are green
(57 tests, 309 assertions), touched finance controller tests are green
(344 tests, 411 assertions), full `tests/Unit --no-coverage` is green
(10,640 tests, 20,697 assertions), `composer phpstan` is clean, and
`git diff --check` is clean.

Latest timesheet/expense approval-finalization check (2026-05-11, Codex):
timesheet create/update/delete and submit/approve/reject decisions now use
`PlanningOperationService`, `planning.operations` outbox rows, post-write
validation, retry/circuit dispatcher support, and client feedback. Expense
create/update/delete and expense-line deletion now use `FinanceOperationService`,
`finance.ledger` outbox rows, post-write validation, retry/circuit dispatcher
support, and client feedback. Timesheets are hard-deleted because the canonical
migration has no `deleted_at`; expense bill rows use canonical lowercase
`expense`. `TimesheetExpenseReliabilityTest` is green (4 tests, 12 assertions),
all reliability service tests are green (61 tests, 321 assertions), touched
timesheet/expense controller tests are green (173 tests, 203 assertions),
full `tests/Unit --no-coverage` is green (10,644 tests, 20,709 assertions),
`projects.timesheets.approval` is registered, and `composer phpstan` is clean.

Latest domain signal-handler check (2026-05-12, Codex): finance, HRM,
warehouse, CRM, planning, and heavy-I/O outbox dispatchers now route default
signals through `DomainSignalHandlerService` before dispatch completion. Each
signal gets an idempotent `inbox_messages` consume record, handled/failed
operational events, handler results in the dispatch report, and short-lived
cache projection metadata for projection/reporting/replica/progress/health
signals. `DomainSignalHandlerServiceTest` is green (3 tests, 17 assertions),
all reliability service tests are green (64 tests, 338 assertions), full
`tests/Unit --no-coverage` is green (10,647 tests, 20,726 assertions), and
`composer phpstan` is clean. That remaining resilience work was completed later
by the external gateway callback and dispatch-orchestration slices.

Latest compensation-executor check (2026-05-12, Codex): added
`CompensationExecutorService` and `php artisan reliability:execute-compensation`
to execute second-phase remediation for ledgers already marked
`compensating`. The executor requires a `compensation.required:*` step, a
related `dead_letter` outbox row, and no unresolved quarantine; success marks
required/execute steps `compensated`, stores domain remediation actions in the
ledger result, moves the ledger to `compensated`, and emits
`<domain>.compensation.completed`. Invalid executor input leaves the ledger
`compensating`, records a failed execute step, and emits
`<domain>.compensation.failed`. `CompensationExecutorServiceTest` is green
(4 tests, 20 assertions), all reliability service tests are green
(68 tests, 358 assertions), full `tests/Unit --no-coverage` is green
(10,651 tests, 20,746 assertions), `php artisan list --raw` registers the
command, and `composer phpstan` is clean.

Latest external payment gateway callback check (2026-05-12, Codex): added
`ExternalPaymentGatewayCallbackService` and wired Benefit plan/invoice returns,
Cashfree plan/invoice returns, and PayTabs `paymentIPN` to inbox-backed
idempotency before local finance mutation. Processed duplicates return safe
no-op/success responses, unprocessed payload replay mismatches fail before
business mutation, accepted callbacks run through retry/circuit guards and write
operation ledgers/steps plus `finance.ledger` outbox rows, and PayTabs missing
configuration records failed inbox rows for provider retry. `ExternalPaymentGatewayCallbackServiceTest`
is green (3 tests, 15 assertions), Benefit/Cashfree callback controller tests
are green (25 tests, 33 assertions), all reliability service tests are green
(71 tests, 373 assertions), full `tests/Unit --no-coverage` is green
(10,654 tests, 20,761 assertions), `payment_ipn` is registered, and
`composer phpstan` is clean.

Latest dispatch-orchestration check (2026-05-12, Codex): added
`DispatchOrchestrationService`, `php artisan reliability:orchestrate-dispatch`,
`config/reliability.php`, and opt-in scheduler wiring for monolith-local drains.
The orchestrator drains finance, HRM, warehouse, CRM, planning, and heavy-I/O
outbox dispatchers in a stable order, aggregates attention/dead-letter/failure
state, then runs compensation as a second phase unless skipped. The scheduler is
disabled by default through `RELIABILITY_DISPATCH_ORCHESTRATION_ENABLED` and
does not require Redis, database queues, Kafka, or another broker.
`DispatchOrchestrationServiceTest` is green (3 tests, 18 assertions), all
reliability service tests are green (74 tests, 391 assertions),
`php artisan list --raw` registers `reliability:orchestrate-dispatch`, full
`tests/Unit --no-coverage` is green (10,657 tests, 20,779 assertions), and
`composer phpstan` is clean. A random `JobStageTest` UUID/tinyint coercion
fixture was made deterministic during the broad verification rerun.

Latest final resilience readiness scan (2026-05-12, Codex): dev-mode generic
resilience is at the responsible stopping point without real external secrets.
The project has durable ledgers/steps, outbox/inbox, operational events,
retry/circuit guards, rare quarantine, compensation execution, inbox-backed
domain signal handlers, active gateway callback idempotency, and monolith-local
dispatch orchestration. Remaining work is integration/deployment-specific:
provider signature enforcement, real gateway/banking/payroll/inventory/archive/
webhook adapters, production scheduler cadence, alert routing, and operator
runbooks. Scan note: `.tmp/codex/20260512/final-resilience-readiness-scan.md`.

---

## TASK A — Bills Models Namespace Finalization ✅ DONE

**Status:** Completed at HEAD `ebda9acd7` (commit `614408e9a refactor(models): migrate Bills namespace`).
Verified 2026-05-07 by Claude Opus 4.7:

- `app/Models/Bills/` directory removed.
- `git status` clean of untracked Bills models (only `phpunit.xml` + audit report dirty).
- Zero references to `App\Models\Bills\` namespace in `app/` or `tests/`.
- All 157/157 Bills unit tests passing.

Backstory preserved in `.tmp/claude/20260504/handoff.md` and
`.tmp/ds/20260506-20260507/handsoff/handoff.md` (FIX-01).

---

## TASK B — Fix 7 PHPUnit Failures ✅ DONE

**Status:** Verified resolved at HEAD `ebda9acd7` (2026-05-07 by Claude Opus 4.7).
All 7 tests pass in isolation:

| Test | Result |
|---|---|
| `MassAssignmentTest` | 186/186 OK |
| `BugTest` | 10/10 OK |
| `EmailTest` | 16/16 OK |
| `JobStageTest` | 2/2 OK |
| `ProductServiceUnitTest` | 2/2 OK |
| `LabelTest` | 3/3 OK |
| `GeneratedOfferLetterTest` | 6/6 OK (2 expected skips) |

PHPUnit local: 12,690 / 0 errors / 0 failures / 78 skips. Archived in
`_inc/laravel/.notes/RESOLVED_ISSUES.md` under [2026-05-07].

---

## TASK C — ESLint Scope Fix ✅ DONE

**Status:** Resolved 2026-05-07 by Claude Opus 4.7.

Reality check: `ts/**`, `.backup/**`, `Modules/**` were **already** in
`eslint.config.mjs`'s ignores (lines 355-358). The actual leak was a
now-deprecated `frontend/` Next.js sub-app (since removed from disk) plus
gitignored-but-on-disk `.history/` archives — together producing 14,867 errors.
The original "77,576" figure in this doc was stale.

**Fix applied:** added `"frontend/**"` (since removed — the Next.js sub-app
was deprecated) and `".history/**"` to the global `ignores` array.
Did **not** add a blanket `"public/**"` because the IIFE route layer at
`public/assets/js/routes/` is exactly what the codebase wants linted
(matcher block at lines 186-211).

**Result:** 14,867 errors → 0 errors. 34 legitimate warnings remain across
9 real source files (unused-var warnings) — kept on purpose so the codebase
keeps tracking them.

---

## TASK D — JS Routes Deployment ✅ DONE

**Status:** Completed before this session. Verified 2026-05-07 by Claude Opus 4.7
via git log:

| Commit | Effect |
|---|---|
| `1312ce60` | `build(ts): deploy TS-compiled JS to production paths` — the actual deploy |
| `cf8bf4f9` | `fix(lint): ... strip @typescript-eslint inline comments from 210 JS files` — post-processing pass |
| `941ebb14` | `fix(build): strip TS-only eslint-disable directives in esm-to-iife.cjs` — fixed the IIFE generator so future regenerations are idempotent |

Diff today between `ts/dist-iife/public/assets/js/routes/` (1,102 files) and
`public/assets/js/routes/` (1,108 files): 905 identical, 197 cosmetic-only
(re-introduces stripped eslint-disable comments — `dist-iife/` is stale).
**Do not rsync `dist-iife/` over the live tree** — it would undo the comment
strip. To refresh `dist-iife/`, re-run `node ts/scripts/esm-to-iife.cjs`.

Core singletons load in 5 Blade layouts (`admin`, `auth`, `contract_header`,
`share_project`, `landing`) via `defer` in head — guaranteed to execute before
footer-defer route scripts.

### Side observations (out of scope for D)
- 6 comma-typo orphan files in `public/assets/js/routes/` are git-tracked but
  unreachable (no URL resolves `store,js` / `note,js` etc.):
  `companyPolicies/store,js`, `contracts/note,js`, `departments/store,js`,
  `employees/edit,js`, `estimations/show,js`, `payments/store,js`. Worth a
  follow-up cleanup commit.

---

## TASK E — Shared-Link Password Flow ✅ DONE

**Status:** Implemented 2026-05-07 by Claude Opus 4.7.

### Investigation
- Only one share-link password gate exists in the codebase — `projectLink()`
  at `app/Http/Controllers/Planning/ProjectController.php:1823-1834`.
- The "invoice/proposal share URLs" in the original task description don't
  have password gates (`invoiceLink` in BillController/InvoiceController/
  ProposalController don't call `Hash::check` or read a password column).

### Fix
Inline base64 fallback before the `Hash::check` rejection in `projectLink()`:
1. Try `Hash::check($entered, $stored)` first (current behaviour).
2. If that fails AND `$stored` doesn't match `/^\$2[ayb]\$/` (i.e. not bcrypt),
   strict-mode `base64_decode($stored, true)` and `hash_equals($decoded,
   $entered)`. If equal: `$project->password = Hash::make($entered);
   $project->saveQuietly();` and accept the request.
3. Update session value to use the (possibly newly rehashed) `$stored`.

### Verification
- `php -l` — no syntax errors.
- 7/7 existing `projectLink` tests pass via
  `vendor/bin/phpunit --filter='projectLink|PRJ_LNK'`.
- PHPStan level 3 — no errors.
- Standalone logic smoke confirms strict base64-decode rejects garbage and
  bcrypt-prefix detection avoids re-decoding hashes.

Audit log: `_inc/laravel/utils/cli/20260507/share-link-password-migration.md`.

### Open follow-up
A focused Feature test against a real DB Project record with a base64
password would lock the migration path against regressions. Out of scope for
this fix.

---

## TASK F — 78 Test Skips Triage ✅ DONE

**Status:** Triage delivered 2026-05-07 by Claude Opus 4.7 →
[`_inc/laravel/.notes/.llms/.guidelines/testing/skips-triage-2026-05-07.md`](_inc/laravel/.notes/.llms/.guidelines/testing/skips-triage-2026-05-07.md).

**Headline findings:**
- Of 93 static `markTestSkipped` call-sites, 78 fire at runtime.
- ~44 are fixture-gap guards (no admin/SA user seeded) — intentional, permanent.
- ~16 are external-API guards (Google, Twilio, Pusher, Benefit) — permanent.
- ~5 architectural (aliasMock/final/overload) — permanent per constraints.md.
- ~5 are realistic fixable candidates: 3 Blade snake_case mismatches +
  Project getProgressColor relation stub + InterviewSchedule boot hang.
  Future "skip-reduction" pass could drop 78 → ~73.

**Stale recipes in this doc (do NOT execute):**
1. **`customers.type` 15-skips fix:** zero current skips reference
   `customers.type`. The model declares the property, but no test currently
   skips because of it. Adding casts speculatively is wrong.
2. **`File::ensureDirectoryExists` 6-skip fix:** the Chatify skips primarily
   need Chatify routes registered, not just a writable directory. Removing
   the skip without wiring routes would convert skip → error.
   `tests/TestCase.php` already creates Chatify vendor stubs.

---

## TASK G — serve-k8s--hard (Infrastructure — BLOCKED)

**Priority:** Low — infrastructure issue, not code.
**Status:** BLOCKED — Docker Hub TLS handshake timeout. IPv6 disabled, ICMP blocked network-wide.
**Do not attempt** until network admin resolves Docker Hub connectivity.
**Notes:** `.tmp/opencode/ds/20260430_fix-status.md`

---

## TASK H — Codex Continuation: Remaining Unit Failures ✅ DONE

**Status:** Completed 2026-05-09 by Codex. Handoff:
`.tmp/codex/20260509/handsoff.md`.

Fixed:

- `OrderTest::order_uses_uuid_for_primary_key` duplicate unique fields.
- Discover upload-failure test fixture missing complete local storage settings.
- Features `featureStore` upload fixture missing complete local storage settings
  and upload directory setup.

Verified:

- Full `tests/Unit --no-coverage`: 10,575 tests, 20,354 assertions,
  0 errors, 0 failures, 8 skipped, 4 incomplete.
- `composer phpstan`: no errors.
- `npx --no-install eslint . --max-warnings=50`: exit 0.

---

## TASK I — Reliability Foundation: Outbox/Inbox + Operation Ledger ✅ FINANCE SLICE DONE

**Status:** Implemented 2026-05-10 by Codex.

Added:

- New migration:
  `database/migrations/2026_05_10_090000_create_reliability_outbox_and_operation_tables.php`.
- Durable tables: `operation_ledgers`, `operation_steps`, `outbox_messages`,
  `inbox_messages`, `operational_events`.
- Models: `OperationLedger`, `OperationStep`, `OutboxMessage`, `InboxMessage`,
  `OperationalEvent`.
- Services under `app/Services/Reliability/`: `CriticalOperationService`,
  `ReliabilityPolicy`, `OutboxService`, `InboxService`,
  `OperationalEventService`, `ReliabilityRetentionService`,
  `FinanceOperationService`, `FinanceOutboxDispatcher`,
  `FinanceCompensationService`, and client payload helpers.
- First production integration: `LedgerActionService` wraps IFRS client invoice,
  supplier bill, and client receipt posting as critical operations with step
  logs and outbox intent.
- Finance controller integration: invoice/bill payment create/delete now commit
  operation ledger + step + outbox intent with the payment mutation, then drain
  local finance outbox signals after commit.
- Client feedback: admin footer reads flashed `reliability_operation`, loads
  `assets/js/routes/reliability/operation-feedback.js`, shows SweetAlert
  progress, and polls `reliability.operations.show` (URI appears as
  `reliabilities/operations/{operation}` after route pluralization).
- Command: `php artisan reliability:dispatch-finance-outbox`.
- Tests: `tests/Unit/app/Services/Reliability/ReliabilityFoundationTest.php`
  and `tests/Unit/app/Services/Reliability/FinanceOutboxDispatcherTest.php`.

Guideline:
`_inc/laravel/.notes/.llms/.guidelines/backend/reliability-outbox-ledger.md`.

Next reliability work should stay finance-first until journal callbacks,
banking adapter shells, and real reversal/compensation handlers are hardened.
Only then broaden to high-impact non-finance workflows: employee status
decisions, final project closure/deletion, warehouse/stock commits, products,
payroll, CRM, external imports, and long-running/heavy I/O jobs.

---

## TASK J — Retry + Circuit Breaker Reliability Guards ✅ DONE

**Status:** Implemented 2026-05-10 by Codex.

Added:

- New migration:
  `database/migrations/2026_05_10_120000_create_reliability_circuit_breaker_tables.php`.
- Durable tables: `circuit_breaker_states`, `circuit_breaker_calls`.
- Models: `CircuitBreakerState`, `CircuitBreakerCall`.
- Guard abstraction: `AbstractReliabilityGuard` and
  `Concerns\EmitsReliabilityEvents`.
- Services: `Retry`, `RetryBuilder`, `CircuitBreaker`, `CircuitBreakerBuilder`.
- Exception: `CircuitBreakerOpenException`.
- Policy updates for circuit states/statuses, retention, half-open defaults,
  capped exponential retry backoff, and low/trivial no-overhead defaults.
- Finance adoption: `FinanceOutboxDispatcher` wraps signal resolution in retry
  and circuit breaker guards before durable outbox retry/dead-letter handling.

Retry events:

- `reliability.retry.success`
- `reliability.retry.retrying`
- `reliability.retry.failed`

Circuit breaker events:

- `reliability.circuit.state_changed`
- `reliability.circuit.opened`
- `reliability.circuit.rejected`

Use builders only where the business impact justifies the overhead. By
default, circuit breakers persist for `medium`, `high`, and `critical`
criticality, while `trivial` and `low` work stays unguarded unless a caller
explicitly enables the breaker.

---

## TASK K — Finance Post-Write Quarantine ✅ DONE

**Status:** Implemented 2026-05-10 by Codex.

Added:

- New migration:
  `database/migrations/2026_05_10_150000_create_operation_quarantine_tables.php`.
- Durable overlay tables: `operation_quarantines` and
  `operation_quarantine_audits`.
- Models: `OperationQuarantine` and `OperationQuarantineAudit`.
- Services: `FinanceReliabilityPolicy`, `FinancePostWriteValidator`,
  `QuarantineService`, `QuarantineRemediationJudge`,
  `PostWriteValidationResult`, and `QuarantineDecision`.
- Exception: `QuarantineRollbackRequiredException`.
- Finance adoption: invoice/bill payment create/delete opt into
  `post_write_validation => true`; the policy starts post-write validation at
  amount `3,200`, keeps every finance transaction retry-eligible, scales
  attempts by amount/risk, and writes quarantine/audit records only after
  persistent corrupted-state or instability signals. Non-persistent invalid
  state rolls back without quarantine or outbox creation.
- Dispatcher gate: `FinanceOutboxDispatcher` refuses to dispatch finance
  outbox messages for ledgers with unresolved quarantine overlays.

Keep quarantine narrow. It is not a generic CRUD guard; use it only for extreme
critical procedures where invalid business state persists after retries,
circuit-breaker instability, rollback/recovery attempts, or long unresolved
processing.

---

## TASK L — HRM Reliability Slice ✅ DONE

**Status:** Implemented 2026-05-10 by Codex.

Added:

- Services: `HrmOperationService`, `HrmReliabilityPolicy`,
  `HrmPostWriteValidator`, `HrmOutboxDispatcher`, `HrmCompensationService`,
  `HrmOperationResult`, and `HrmReliabilityAssessment`.
- Exception: `HrmPostWriteValidationFailedException`.
- Command: `php artisan reliability:dispatch-hrm-outbox`.
- Adoption: `SetSalaryController::employeeSalaryUpdate`,
  `TerminationController::store/update/destroy`, and
  `LeaveController::changeAction`.
- Shared quarantine routing now emits domain-specific events such as
  `hrm.quarantine.manual_review`.

Policy notes:

- Employees may legitimately have `employees.user_id = null`; do not treat a
  missing login account as corruption.
- A non-null linked user can fail validation when the link, user type, creator,
  email, or known `Employee` role/RBAC state is mismatched.
- HRM quarantine is manual-review only for now and requires persistent
  instability signals in critical payroll, lifecycle, or identity/access work.
  A single failed validation rolls back without quarantine.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/HrmReliabilityTest.php --no-coverage` — 6 tests, 30 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 30 tests, 172 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Http/Controllers/planning/SetSalaryControllerTest.php tests/Unit/app/Http/Controllers/planning/LeaveControllerTest.php tests/Unit/app/Http/Controllers/planning/TerminationControllerTest.php --no-coverage` — 136 tests, 163 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,613 tests, 20,560 assertions, OK.
- `composer phpstan` — no errors.

Next: add real HRM compensation handlers for payroll/access-control failures,
then move to project closure, warehouse/stock/product commits, and CRM
decision workflows.

---

## TASK M — Warehouse/Products Reliability Slice ✅ DONE

**Status:** Implemented 2026-05-11 by Codex.

Added:

- Services: `WarehouseOperationService`, `WarehouseReliabilityPolicy`,
  `WarehousePostWriteValidator`, `WarehouseOutboxDispatcher`,
  `WarehouseCompensationService`, `WarehouseOperationResult`, and
  `WarehouseReliabilityAssessment`.
- Exception: `WarehousePostWriteValidationFailedException`.
- Command: `php artisan reliability:dispatch-warehouse-outbox`.
- Adoption: `ProductStockController::store/update/destroy`,
  `ProductServiceController::store/update/destroy/import`,
  `WarehouseTransferController::store/update/destroy`,
  `WarehouseController::destroy`, `PurchaseController::store/update/destroy`,
  `PurchaseController::productDestroy`, and `PosController::store`.
- Shared quarantine routing now emits warehouse-domain manual-review events for
  persistent high-impact stock/product/warehouse corruption signals.

Policy notes:

- Most durable stock/product/warehouse state changes get retry/circuit plus
  outbox/ledger coverage, but routine metadata remains low overhead.
- Warehouse transfer quantity/source/destination edits are blocked through the
  update path; they need a new controlled stock movement.
- Replica-sync and eventual-consistency-sensitive flags are recorded for stock
  projection/reconciliation shells, but do not alone qualify a row for
  quarantine.
- Warehouse quarantine requires persistent instability signals after normal
  rollback, validation, retry, and circuit-breaker paths fail.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/WarehouseReliabilityTest.php --no-coverage` — 5 tests, 31 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 35 tests, 203 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Http/Controllers/products/ProductStockControllerTest.php tests/Unit/app/Http/Controllers/products/ProductServiceControllerTest.php tests/Unit/app/Http/Controllers/activity/WarehouseTransferControllerTest.php tests/Unit/app/Http/Controllers/companies/WarehouseControllerTest.php tests/Unit/app/Http/Controllers/activity/PurchaseControllerTest.php tests/Unit/app/Http/Controllers/activity/PosControllerTest.php --no-coverage` — 410 tests, 486 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,618 tests, 20,591 assertions, OK.
- `php artisan list --raw` — confirms `reliability:dispatch-warehouse-outbox`.
- `composer phpstan` — no errors.

Broad verification also closed two pre-existing factory uniqueness collisions
without touching migrations or seeders: `CustomerFactory` now emits UUID-based
emails, and `BranchFactory` now emits UUID-based names.

Next: add domain-specific compensation/reconciliation handlers behind the
accepted stock projection and replica-sync shells, then continue to project
closure/finalization and CRM decision workflows.

---

## TASK N — CRM Reliability Slice ✅ DONE

**Status:** Implemented 2026-05-11 by Codex.

Added:

- Services: `CrmOperationService`, `CrmReliabilityPolicy`,
  `CrmPostWriteValidator`, `CrmOutboxDispatcher`, `CrmCompensationService`,
  `CrmOperationResult`, and `CrmReliabilityAssessment`.
- Exception: `CrmPostWriteValidationFailedException`.
- Command: `php artisan reliability:dispatch-crm-outbox`.
- Adoption: `LeadController::store/update/destroy/order/convertToDeal()` and
  `DealController::store/update/destroy/order/changeStatus()`.
- Relationship-record adoption:
  `CustomerController::store/update/destroy()`,
  `VendorController::store/update/destroy()`,
  `ClientController::store/update/destroy()`, and `DealController` user/client/
  permission sub-actions.
- Client feedback: `ReliabilityClientPayloadService::fromCrmResult()`.
- Shared quarantine routing now emits CRM-domain manual-review events for
  persistent lead/deal/access/relationship corruption signals.
- `ClientPermission` now resolves client-like permission names through the
  canonical `App\Config\Constants\SeedersTemplating` list before validation.

Policy notes:

- CRM has many transient payloads. Calls, emails, discussions, files, notes,
  dashboards, labels, and lightweight source/configuration work should not pay
  heavy guard costs unless tied to durable lead/deal lifecycle state.
- Lead conversion, deal final/status decisions, deal lifecycle changes, stage
  movement, and CRM access assignment are the first durable CRM clusters.
- Customer/vendor/client lifecycle rows use the `relationship_record` cluster
  because they feed finance, purchases, invoices, projects, stock reports, and
  access decisions.
- CRM quarantine requires persistent instability after normal rollback,
  validation, retry, and circuit-breaker paths fail; a single bad CRM write
  rolls back/fails without quarantine.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/CrmReliabilityTest.php --no-coverage` — 9 tests, 42 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Http/Controllers/activity/LeadControllerTest.php tests/Unit/app/Http/Controllers/activity/DealControllerTest.php --no-coverage` — 609 tests, 747 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Http/Controllers/individuals/CustomerControllerTest.php tests/Unit/app/Http/Controllers/individuals/ClientControllerTest.php tests/Unit/app/Http/Controllers/companies/VendorControllerTest.php tests/Unit/app/Http/Controllers/activity/DealControllerTest.php --no-coverage` — 594 tests, 703 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 44 tests, 245 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,623 tests, 20,619 assertions, OK.
- Post-relationship full Unit attempt: 10,627 tests, 20,632 assertions, 1 unrelated timing failure in `ContractControllerTest::test_noteStore_performance_114`; isolated rerun OK.
- `composer phpstan` — no errors.

Next: project closure/finalization workflows, heavy business-impacting
imports/exports, and provider-specific CRM relationship/project/finance/webhook
workers behind the inbox-backed domain signal handlers.

---

## TASK O — Project Planning Reliability Slice ✅ DONE

**Status:** Implemented 2026-05-11 by Codex.

Added:

- Services: `PlanningOperationService`, `PlanningReliabilityPolicy`,
  `PlanningPostWriteValidator`, `PlanningOutboxDispatcher`,
  `PlanningCompensationService`, `PlanningOperationResult`, and
  `PlanningReliabilityAssessment`.
- Exception: `PlanningPostWriteValidationFailedException`.
- Command: `php artisan reliability:dispatch-planning-outbox`.
- Adoption: `ProjectController::update()` for final project status only,
  `ProjectController::destroy()`, `ProjectController::milestoneUpdate()` for
  final/elevated-cost milestone paths, `ProjectController::milestoneDestroy()`,
  `ProjectTaskController::changeCom()`, final `changeProg()`, and deletion of
  completed/final tasks.
- Client feedback: `ReliabilityClientPayloadService::fromPlanningResult()`.
- Shared quarantine routing now emits planning-domain manual-review events for
  persistent final project/milestone/task corruption signals.

Policy notes:

- Project planning is the lightest slice so far. Routine project metadata,
  comments, files, checklist toggles, board ordering, filters, and non-final
  task edits/deletes stay low-overhead.
- Project deletion is critical; project final status is high/critical by budget
  and instability; milestone/task final state is medium/high by cost, progress,
  status, or priority.
- Timesheet and expense approval/finalization is now covered in Task R because
  it overlaps planning, payroll, and finance semantics.
- Planning quarantine requires persistent instability after normal rollback,
  validation, retry, and circuit-breaker paths fail; a single bad planning write
  rolls back/fails without quarantine.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/PlanningReliabilityTest.php --no-coverage` — 6 tests, 30 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/PlanningReliabilityTest.php tests/Unit/app/Http/Controllers/planning/ProjectControllerTest.php tests/Unit/app/Http/Controllers/planning/ProjectTaskControllerTest.php --no-coverage` — 354 tests, 450 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 50 tests, 275 assertions, OK.
- `php artisan list --raw` — confirms `reliability:dispatch-planning-outbox`.
- `composer phpstan` — no errors.

Next: provider-specific planning projection/bridge/webhook workers behind the
inbox-backed domain signal handlers.

---

## TASK P — Heavy I/O / Integrations Reliability Slice ✅ DONE

**Status:** Implemented 2026-05-11 by Codex.

Added:

- Services: `HeavyIoOperationService`, `HeavyIoReliabilityPolicy`,
  `HeavyIoPostWriteValidator`, `HeavyIoOutboxDispatcher`,
  `HeavyIoCompensationService`, and `HeavyIoReliabilityAssessment`.
- Command: `php artisan reliability:dispatch-heavy-io-outbox`.
- Adoption: shared `DelegatesPythonImport`, shared `DelegatesPythonExport`, and
  `NotificationService::webhookCall()`.
- Shared quarantine routing now emits heavy-I/O manual-review events for
  persistent invalid high-impact import/export/webhook/callback results.

Policy notes:

- The wrapper is boundary-based, not controller-scattered. It covers
  subprocess imports/exports and configured webhook delivery without forcing
  routine file reads, UI metadata, small previews, or transient notifications
  into durable reliability overhead.
- Python subprocess calls are not executed inside SQL transactions. Ledgers,
  steps, outbox rows, retry/circuit events, and validation happen around the
  external boundary.
- Legacy return shapes are preserved: imports return arrays, exports return
  strings, and webhooks return booleans.
- Heavy-I/O quarantine requires persistent instability after normal retry,
  circuit, validation, and failure handling; a single failed subprocess or
  webhook delivery should not quarantine.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/HeavyIoReliabilityTest.php --no-coverage` — 4 tests, 22 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 54 tests, 297 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Traits/DelegatesPythonImportTest.php tests/Unit/app/Traits/DelegatesPythonExportTest.php --no-coverage` — 26 tests, 30 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Models/utils/UtilityTest.php --filter=webhook --no-coverage` — 7 tests, 25 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Imports/ImportSupplementaryTest.php --filter=customer_import_within_resource_limits --no-coverage` — 1 test, 2 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,637 tests, 20,685 assertions, OK.
- `php artisan list --raw` — confirms `reliability:dispatch-heavy-io-outbox`.
- `composer phpstan` — no errors.

Next: real domain compensation/reconciliation executors behind the inbox-backed
domain signal handlers.

---

## TASK Q — Finance Extended Flows Reliability Slice ✅ DONE

**Status:** Implemented 2026-05-11 by Codex.

Added:

- Shared controller trait:
  `App\Http\Controllers\Concerns\HandlesFinanceReliability`.
- Finance policy/validator support for revenue, generic vendor payments, bank
  transfers, purchase payments, credit notes, debit notes, journal entries, and
  journal items.
- Finance dispatcher default signals for banking reconciliation, finance
  reporting, credit/debit note reconciliation, accounting reconciliation,
  communication, reversal review, and webhook shells.
- Adoption: `RevenueController::store/update/destroy`,
  `PaymentController::store/update/destroy`,
  `BankTransferController::store/update/destroy`,
  `PurchaseController::createPayment/paymentDestroy`,
  `CreditNoteController::store/update/destroy/customStore`,
  `DebitNoteController::store/update/destroy/customStore`, and
  `JournalEntryController::store/update/destroy/accountDestroy/journalDestroy`.

Policy notes:

- Every money-moving finance flow remains retry-eligible. Attempts still scale
  by amount, reversal-like direction, metadata risk, and persistent instability.
- Post-write validation remains policy-gated from amount `3,200` unless a caller
  explicitly forces it; low-value finance operations still get retry/outbox
  coverage without unnecessary validation overhead.
- Quarantine remains rare and requires persistent retry/circuit/dead-letter/
  failed-ledger/long-running instability plus core finance corruption.
- Gateway callback/payment-provider flows were left for a separate pass because
  their idempotency and external-origin semantics need a targeted scan.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/FinanceExtendedFlowsTest.php --no-coverage` — 3 tests, 12 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 57 tests, 309 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Http/Controllers/bills/RevenueControllerTest.php tests/Unit/app/Http/Controllers/bills/PaymentControllerTest.php tests/Unit/app/Http/Controllers/bills/BankTransferControllerTest.php tests/Unit/app/Http/Controllers/bills/CreditNoteControllerTest.php tests/Unit/app/Http/Controllers/bills/DebitNoteControllerTest.php tests/Unit/app/Http/Controllers/activity/PurchaseControllerTest.php tests/Unit/app/Http/Controllers/shapes/JournalEntryControllerTest.php --no-coverage` — 344 tests, 411 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,640 tests, 20,697 assertions, OK.
- `composer phpstan` — no errors.
- `git diff --check` — clean.

Next: external gateway callback idempotency and finance banking/journal/
reconciliation/compensation executors behind the inbox-backed domain signal
handlers.

---

## TASK R — Timesheet / Expense Approval-Finalization Slice ✅ DONE

**Status:** Implemented 2026-05-11 by Codex.

Added:

- Shared controller trait:
  `App\Http\Controllers\Concerns\HandlesPlanningReliability`.
- Planning policy/validator/dispatcher support for timesheet create/update/
  delete and approval submit/approve/reject decisions.
- Finance policy/validator/dispatcher support for expense create/update/delete
  and expense-line deletion.
- Adoption: `TimesheetController::timesheetStore/timesheetUpdate/
  timesheetDestroy/timesheetApprovalAction` and
  `ExpenseController::store/update/productDestroy/destroy`.

Policy notes:

- Timesheets remain planning records. Approved timesheets emit payroll and
  finance-context signals, but money-moving work must be posted later by a
  payroll/billing/finance worker with its own ledger.
- Timesheet deletion is hard delete because the canonical migration has no
  `deleted_at`; do not re-add `SoftDeletes` without a schema change.
- Expenses are finance records in this app because the controller writes
  `Bill`, `BillPayment`, `BillProduct`, and `BillAccount` state.
- Quarantine remains rare for both paths: persistent retry/circuit/dead-letter/
  failed-ledger instability plus core approval/expense corruption only.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/TimesheetExpenseReliabilityTest.php --no-coverage` — 4 tests, 12 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 61 tests, 321 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Http/Controllers/shapes/TimesheetControllerTest.php tests/Unit/app/Http/Controllers/bills/ExpenseControllerTest.php --no-coverage` — 173 tests, 203 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,644 tests, 20,709 assertions, OK.
- `php artisan route:list --name=projects.timesheets.approval` — route registered.
- `composer phpstan` — no errors.

Next at that point was external gateway callback idempotency and scheduled
dispatch orchestration; both are now complete in Tasks U and V.

---

## TASK S — Domain Signal Handlers ✅ DONE

**Status:** Implemented 2026-05-12 by Codex.

Added:

- `DomainSignalHandlerService`.
- Inbox-backed default signal execution in `FinanceOutboxDispatcher`,
  `HrmOutboxDispatcher`, `WarehouseOutboxDispatcher`, `CrmOutboxDispatcher`,
  `PlanningOutboxDispatcher`, and `HeavyIoOutboxDispatcher`.
- Focused `DomainSignalHandlerServiceTest`.

Policy notes:

- Default outbox signals are no longer pure descriptor acceptance. Each default
  signal now records a durable `inbox_messages` consume boundary, handles
  idempotency, emits `*.signal.handled` or `*.signal.failed`, and returns the
  handler result in the dispatch report.
- Projection/reporting/replica/progress/health/archive/frontend-style signals
  write short-lived cache projection metadata. Reconciliation/bridge handlers
  run lightweight canonical-table checks, but strict corruption detection still
  belongs to post-write validators and quarantine policies.
- This is still monolith-local. It does not mean external provider callbacks,
  banking adapters, payroll posting, or webhook subscribers have completed
  their own business operation.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/DomainSignalHandlerServiceTest.php --no-coverage` — 3 tests, 17 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 64 tests, 338 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,647 tests, 20,726 assertions, OK.
- `composer phpstan` — no errors.

Final scan at that point:

- Broad in-repo module resilience adoption is complete for current finance,
  HRM, warehouse/products, CRM, planning, heavy-I/O, timesheet, and expense
  paths.
- Suggested remaining clusters at that point were external payment gateway
  callback idempotency and dispatch orchestration; both are now complete in
  Tasks U and V.

---

## TASK T — Domain Compensation Executors ✅ DONE

**Status:** Implemented 2026-05-12 by Codex.

Added:

- `CompensationExecutorService`.
- Command: `php artisan reliability:execute-compensation`.
- Focused `CompensationExecutorServiceTest`.

Policy notes:

- This is the second phase after dispatcher dead letters. Dispatchers still only
  mark `compensation.required:*`; the executor requires that marker and a
  related `dead_letter` outbox row before closing a ledger.
- Unresolved quarantine blocks execution. A failed executor attempt leaves the
  ledger `compensating`, records a failed `compensation.execute:*` step, and
  emits `<domain>.compensation.failed`.
- Successful execution marks the required and execute steps `compensated`,
  records domain-specific remediation actions in the ledger result, moves the
  ledger to `compensated`, and emits `<domain>.compensation.completed`.
- The first slice records conservative local remediation checkpoints for
  finance, warehouse, HRM, CRM, planning, and heavy-I/O. It does not blindly
  rewrite source business rows or call provider/payroll/inventory APIs.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/CompensationExecutorServiceTest.php --no-coverage` — 4 tests, 20 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 68 tests, 358 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,651 tests, 20,746 assertions, OK.
- `php artisan list --raw` — confirms `reliability:execute-compensation`.
- `composer phpstan` — no errors.

---

## TASK U — External Payment Gateway Callback Idempotency ✅ DONE

**Status:** Implemented 2026-05-12 by Codex.

Added:

- `ExternalPaymentGatewayCallbackService`.
- Focused `ExternalPaymentGatewayCallbackServiceTest`.
- Active adoption for Benefit plan/invoice returns, Cashfree plan/invoice
  returns, and PayTabs `paymentIPN`.

Policy notes:

- Gateway callbacks are externally originated and replayable, so they use
  `inbox_messages` idempotency before local finance mutation.
- The idempotency key uses provider reference plus stable subject. Amount and
  request payload stay in the payload hash so unprocessed replay mismatches fail
  before plan activation, invoice payment, or vendor IPN mutation.
- Accepted callbacks run through retry and circuit breaker guards, then write
  operation ledgers/steps, `finance.ledger` outbox rows, and operational events.
- PayTabs missing configuration records a failed inbox row so provider retries
  can process after configuration is restored.
- Stripe remains a direct charge command in this slice, not a server/return
  callback route. Dormant provider blocks should opt in only if re-enabled.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/ExternalPaymentGatewayCallbackServiceTest.php --no-coverage` — 3 tests, 15 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Http/Controllers/bills/BenefitPaymentControllerTest.php tests/Unit/app/Http/Controllers/bills/CashfreeControllerTest.php --no-coverage` — 25 tests, 33 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 71 tests, 373 assertions, OK.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,654 tests, 20,761 assertions, OK.
- `php artisan route:list --name=payment_ipn` — route registered.
- `composer phpstan` — no errors.

---

## TASK V — Dispatch Orchestration ✅ DONE

**Status:** Implemented 2026-05-12 by Codex.

Added:

- `DispatchOrchestrationService`.
- Command: `php artisan reliability:orchestrate-dispatch`.
- Config: `config/reliability.php` with opt-in scheduler controls.
- Focused `DispatchOrchestrationServiceTest`.

Policy notes:

- This keeps dispatch monolith-local. It coordinates existing domain
  dispatchers and compensation execution; it does not require Redis, database
  queues, Kafka, or another broker.
- Default order is finance, HRM, warehouse, CRM, planning, and heavy-I/O.
  Compensation runs second unless `--skip-compensation` is provided.
- The command supports `--domain`, `--limit`, `--compensation-limit`,
  `--stop-on-failure`, and `--fail-on-attention` for manual or scheduled
  operation.
- Scheduler wiring is disabled by default. Enable it only after choosing a
  production cadence and limits with
  `RELIABILITY_DISPATCH_ORCHESTRATION_ENABLED=true`.
- Provider-specific gateway signature enforcement and production runbooks for
  alerts/cadence remain follow-up work.

Verification:

- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability/DispatchOrchestrationServiceTest.php --no-coverage` — 3 tests, 18 assertions, OK.
- `php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage` — 74 tests, 391 assertions, OK.
- `php artisan list --raw` — confirms `reliability:orchestrate-dispatch`.
- `composer phpstan` — no errors.
- `php vendor/bin/phpunit tests/Unit --no-coverage` — 10,657 tests, 20,779 assertions, OK.

---

## TASK W — Dev-Mode Resilience Readiness Scan ✅ DONE

**Status:** Completed 2026-05-12 by Codex.

Conclusion:

- Generic resilience development is complete for dev mode without real external
  secrets.
- The remaining work is intentionally not more generic scaffolding. It needs
  real provider contracts, credentials, deployment cadence, alert routing, and
  operator runbooks.
- Latest scan note:
  `.tmp/codex/20260512/final-resilience-readiness-scan.md`.

Do not broaden resilience wrappers by default after this point. Future work
should be integration-specific and replace local shells with real adapters one
domain at a time.

---

## READING ORDER FOR NEW AGENTS

1. `where-to-update-and-read.yml` — filesystem map
2. `_inc/laravel/.notes/.llms/.guidelines/constraints.md` — hard rules
3. `_inc/laravel/.notes/.llms/.guidelines/roles/agent-roles.md` — role-specific reading lists
4. `_inc/laravel/.notes/.llms/.guidelines/backend/reliability-outbox-ledger.md` — outbox/inbox + operation ledger policy
5. `.tmp/codex/20260512/final-resilience-readiness-scan.md` — final dev-mode resilience boundary
6. `.tmp/codex/20260512/handsoff.md` — latest Codex domain signal-handler, compensation-executor, external gateway callback, dispatch orchestration, and final resilience scan state
7. `.tmp/codex/20260511/handsoff.md` — Codex warehouse/CRM/planning/heavy-I/O/finance-extended/timesheet-expense reliability continuation state
8. `.tmp/codex/20260510/handsoff.md` — prior Codex finance/HRM reliability continuation state
9. `.tmp/codex/20260509/handsoff.md` — prior Codex Unit-suite continuation state
10. `.tmp/opencode/ds/20260507_handsoff-update.md` — last DS agent final state
11. `.tmp/claude/20260504/handoff.md` — Claude's Bills migration context

---

## NOTES CONVENTION

- Leave Codex session notes in `.tmp/codex/<YYYYMMDD>/`.
- Leave OpenCode session notes in `.tmp/opencode/<agent-slug>/<YYYYMMDD>_<topic>.md`
- Leave JSON status updates in `.tmp/opencode/<agent-slug>/<YYYYMMDD>_pending-issues-update.json`
- Commit messages: `type(scope): description` (conventional commits)
- **Never edit existing** `database/migrations/` files and never touch
  `_inc/.seeders/`; add a new migration only for explicit schema-expansion
  work.
- Before forking to another agent, record the exact command results, dirty
  files, branch, HEAD, and any forbidden paths deliberately left untouched.
