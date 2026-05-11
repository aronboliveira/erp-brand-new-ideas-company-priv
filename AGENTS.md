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
`eslint.config.mjs`'s ignores (lines 355-358). The actual leak was the
`frontend/` Next.js sub-app (which has its own `eslint.config.js`) plus
gitignored-but-on-disk `.history/` archives — together producing 14,867 errors.
The original "77,576" figure in this doc was stale.

**Fix applied:** added `"frontend/**"` and `".history/**"` to the global
`ignores` array. Did **not** add a blanket `"public/**"` because the IIFE
route layer at `public/assets/js/routes/` is exactly what the codebase wants
linted (matcher block at lines 186-211).

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

## READING ORDER FOR NEW AGENTS

1. `where-to-update-and-read.yml` — filesystem map
2. `_inc/laravel/.notes/.llms/.guidelines/constraints.md` — hard rules
3. `_inc/laravel/.notes/.llms/.guidelines/roles/agent-roles.md` — role-specific reading lists
4. `_inc/laravel/.notes/.llms/.guidelines/backend/reliability-outbox-ledger.md` — outbox/inbox + operation ledger policy
5. `.tmp/codex/20260510/handsoff.md` — latest Codex reliability continuation state
6. `.tmp/codex/20260509/handsoff.md` — prior Codex Unit-suite continuation state
7. `.tmp/opencode/ds/20260507_handsoff-update.md` — last DS agent final state
8. `.tmp/claude/20260504/handoff.md` — Claude's Bills migration context

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
