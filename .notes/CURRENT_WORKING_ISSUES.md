# CURRENT WORKING ISSUES

> Last updated: 2026-03-06
> Branch: `main`
> Resolved items archived to `.notes/.llms/.history/`. Guidelines in `.notes/.llms/.guidelines/`.

## ACTIVE ISSUES

_None — all issues resolved._

## RESOLVED (this session)

### 5. Intelephense / VS Code Problems Panel Errors (BATCH FIX)

**Root cause:** ~70+ Intelephense errors from missing import aliases, undefined static properties, case-sensitive property mismatches, wrong namespace imports, incorrect return types, deprecated nullable syntax, and test bugs.

**Fix (2026-03-06) — 14 fixes across 12 files:**

- **BillController.php:** Added `as UC` alias to `UsersConstants` import — resolved 37 `UC::` reference errors.
- **ProjectTaskController.php:** Added `as PJC` alias to `ProjectsConstants` import — resolved `PJC::COL_STAGE_ID` error.
- **ExpenseController.php L759:** Changed `BillsConstants::COL_BIL_TMP` → `BC::COL_BIL_TMP` (full name wasn’t imported, alias was).
- **CommissionController.php:** Fixed `Commission::$commissiontype` → `$commissionType` at 3 locations (PHP static properties are case-sensitive).
- **ComissionControllerTest.php L320:** Same `$commissiontype` → `$commissionType` case fix.
- **NotificationTemplateController.php L30:** Removed `use function App\Http\Controllers\Helpers\{...}` — functions live in `App\Http\Controllers` (same namespace, resolve automatically).
- **Bill.php (model):** Added `public static array $statuses` property matching BillStatus enum values.
- **Job.php (model):** Added `public static array $status` property matching EvaluationStatus subset used by JobController.
- **DashboardController.php:** Fixed `handleLandingOrInstall` return type → `RedirectResponse|View`; `buildPipelineStats` → `array|RedirectResponse`.
- **XSS.php L131:** Fixed `ConsoleOutput` → `SafeConsoleOutput` type hint (ConsoleOutput was never imported).
- **AuthenticatedSessionController.php:** Fixed 2 deprecated implicit nullable params (`string $x = null` → `?string $x = null`).
- **ProjectStagesTest.php:** Added missing `$expectedCollection` variable; fixed `ProjectStages::class` → `ProjectStage::class`.

**False positives identified (not fixed):**

- BankTransferPaymentController L380: Collection vs array — Collection implements ArrayAccess.
- ComissionControllerTest L254/282/310: `commissionCreate` on Mockery mock — Intelephense can’t resolve.
- jest.config.cjs L17/28: `__dirname` — valid in .cjs (CommonJS) files.
- web.php L1783: `PaytabsLaravelListenerApi` — runtime container resolution via `app()`.

### 4. Playwright Firefox Render-Timing (was COSMETIC → FIXED)

**Root cause:** `playwright-frontend.config.cjs` had a 30s test timeout and 5s expect timeout — far too tight for the render-timing benchmark which measures each view _twice_ plus a nav click. Firefox/WebKit render chart-heavy pages 20–40% slower than Chromium, causing timeouts that were silently swallowed by a `catch → test.skip()` pattern.

**Fix (2026-03-05):**

- `render-timing.spec.ts`: Added browser-aware constants (`isSlowBrowser()`) — Firefox/WebKit get 15s load threshold (vs 8s for Chromium), 600ms LCP grace (vs 200ms), and `test.slow()` triples the per-test timeout.
- `render-timing.spec.ts`: Increased goto/selector/idle timeouts to 45s/15s/15s.
- `render-timing.spec.ts`: Added `waitForLoadState('load')` before `networkidle` so `loadEventEnd` is populated.
- `render-timing.spec.ts`: Added `requestAnimationFrame` wait for canvas-heavy views (ApexCharts).
- `render-timing.spec.ts`: Separated connectivity errors (→ `test.skip`) from assertion failures (→ `throw`) so perf regressions fail visibly instead of being silently skipped.
- `playwright-frontend.config.cjs`: Bumped base timeout from 30s → 45s and expect timeout from 5s → 10s.

---

## REMINDERS

```
⛔ NEVER run `php artisan test`            — wipes production DB
⛔ NEVER run `php artisan migrate:fresh`   — same
⛔ NEVER cast $user->id to (int)           — UUID always returns 0
⛔ NEVER push to comp remote               — push only to origin
⛔ Always use MWC::, VW::, PMC:: constants — no raw strings in routes
```
