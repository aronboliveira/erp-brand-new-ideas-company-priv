# Merged Optimisation & Fix Plan — ERP Brand New Ideas Company

# Synthesis date: 2026-05-01

# Source sessions: claude/20260430, codex/20260430, copilot/20260430, gemini/20260430, open-claude/20260430

> **How to read this document.**
> Items are grouped into four priority tiers: **P0 Blocking**, **P1 High**, **P2 Medium**, **P3 Deferred**.
> A finding appears here only when at least one session raised it; cross-agent consensus is noted.
> "Resolved" items that were fixed during session review are marked ✅.

---

## Agent Consensus Summary

| Finding                               | claude | codex | copilot | gemini | open-claude |
| ------------------------------------- | :----: | :---: | :-----: | :----: | :---------: |
| DB rename split/incomplete            |   ✓    |   ✓   |    —    |   ✓    |      ✓      |
| Hardcoded credentials in rename_db.sh |   ✓    |   —   |    —    |   —    |      ✓      |
| BillProduct namespace mismatch        |   ✓    |   —   |    —    |   ✓    |      ✓      |
| .env DB_DATABASE misaligned           |   ✓    |   ✓   |    —    |   —    |      ✓      |
| 399-file brand rename uncommitted     |   ✓    |   ✓   |    —    |   —    |      ✓      |
| Postman collection half-staged        |   ✓    |   —   |    —    |   —    |      ✓      |
| ESLint scope too wide                 |   ✓    |   —   |    —    |   —    |      ✓      |
| Broken symlinks in agent context      |   —    |   —   |    —    |   —    |      ✓      |
| /tmp/ rename litter                   |   ✓    |   —   |    —    |   —    |      ✓      |
| Documentation tree drift              |   ✓    |   —   |    —    |   —    |      ✓      |
| 7 stable PHPUnit unit failures        |   ✓    |   —   |    —    |   —    |      ✓      |

---

## P0 — Blocking (app non-functional until resolved)

### ✅ P0-1 · Database rename: split/inconsistent state

**Resolved/verified** — commit `f0520dd8d` (2026-05-01)

Current verification shows `erp_brand_new_ideas_company_db` is no longer in the split
state described below. The target DB has 229 base tables; all critical tables listed in
this item are present; all current unique migration files are applied; `CHECK TABLE`
reported 0 non-OK checks; and DB text/table-name scans found 0 `prestech` / `erpgo`
matches. No recovery rename was re-run during this verification.

**Historical report retained for context:**

**Agents reporting:** claude, codex, gemini, open-claude (4/5)

The `rename_db.sh` script (PID 768220) terminated before completing all `RENAME TABLE`
operations. Reports diverge on exact counts (claude logged 30 tables moved; open-claude
logged 149/209) — the divergence suggests a later partial `migrate` ran against the new
DB after the script stopped. Regardless, the target `erp_brand_new_ideas_company_db` is
**missing 60–80 tables** from the canonical 209.

**Critical missing tables** (confirmed absent by open-claude): `permissions`, `roles`,
`model_has_permissions`, `model_has_roles`, `role_has_permissions` (Spatie RBAC broken),
`products`, `purchases`, `expenses`, `invoice_products`, `journal_entries`, `tasks`,
`project_tasks`, `project_users`.

**Recovery path:**

1. Confirm the best donor DB — nine DBs carry exactly 209 tables:
   `erp_prestech_db_test_{2,9,10,11,12,13,14,15,16}`.
2. For each missing table, run:
   ```sql
   RENAME TABLE erp_prestech_db_test_10.`tablename`
     TO erp_brand_new_ideas_company_db.`tablename`;
   ```
   A targeted-rename script is safer than rerunning `rename_db.sh` whole, because
   the script's first step `DROP DATABASE … erp_brand_new_ideas_company_db` would
   destroy all 149 already-moved tables.
3. After all 209 tables are verified in the target, update `.env`:
   `DB_DATABASE=erp_brand_new_ideas_company_db`.
4. Verify: `SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='erp_brand_new_ideas_company_db';`
   → should return 209 (or 211 if the two IFRS extra tables are kept).

**Bug in script to fix before reuse:**
Line 82 of `rename_db.sh`:

```bash
# wrong:
rm -f /tmp/rename_errors.sql
# correct:
rm -f /tmp/rename_errors.log
```

### ✅ P0-2 · BillProduct class: wrong namespace (blocks Feature test autoload)

**Resolved** — commit `c28f9474e` (2026-05-01)

`BillProduct.php` namespace corrected to `App\Models\Bills`. Callers in `Bill.php` and
`Utility.php` updated. All three files pass `php -l`. See "Already Resolved" table.

---

## P1 — High (significant risk or workflow blocker)

### ✅ P1-1 · Commit the brand rename working-tree changeset

**Resolved** — commit `c28f9474e` (2026-05-01, 405 files)

405 files committed: all tracked modifications + deletions, logo renames, Postman
collection rename, BillProduct namespace fix. `rename_db.sh` intentionally excluded
(inline credentials).

> **Push pending** — `origin` remote points to
> `https://github.com/aronboliveira/erp-brand-new-ideas-company-priv` which does not
> yet exist on GitHub. Create the repo then `git push origin main`.

### ✅ P1-2 · Remove hardcoded credentials from rename_db.sh before any commit

**Resolved** — commit `107567fb5` (2026-05-01)

`_inc/laravel/scripts/rename_db.sh` was rewritten as a guarded recovery helper:
no inline credentials, no old-brand donor default, explicit destructive confirmation
required, database-name validation added, and the cleanup typo now targets
`rename_errors.log`. The `/tmp/rename*` and `/tmp/clean_rename.sh` artifacts listed
below were removed after P0-1 verification.

**Agents reporting:** claude, open-claude

The previous helper had inline sudo/database passwords. Those values were also present
in `/tmp/rename_tables.sh` and `/tmp/clean_rename.sh` (world-readable).

**Fix before committing the script:**

```bash
# Replace inline creds with reads from canonical sources:
SUDO_PASS=$(grep '^password:' "$(git rev-parse --show-toplevel)/.gh/sudo.yml" | awk '{print $2}')
DB_PASS=$(grep '^DB_PASSWORD=' "$(git rev-parse --show-toplevel)/_inc/laravel/.env" | cut -d= -f2-)
```

**Immediate cleanup of /tmp litter:**

```bash
rm -f /tmp/rename_batch.sql /tmp/rename_current_batch.sql /tmp/rename_errors.log \
      /tmp/renames.sql /tmp/renames2.sql /tmp/rename_tables.sql /tmp/rename_sql.txt \
      /tmp/rename_tables.sh /tmp/clean_rename.sh /tmp/rename_db.log
```

Do this only after the DB recovery (P0-1) is verified complete.

### ✅ P1-3 · Fix 7 broken symlinks in agent context directory [SOLVED 2026-05-01]

**Agents reporting:** open-claude (detailed list)

All under `_inc/laravel/utils/.llms/ctx/agents/`:

```
NOTE_chatify_bugs.md          → 20260207_chatify_bugs.md
NOTE_accounting_mock_data.md  → 20260207_accounting_mock_data.md
NOTE_stripe_config.md         → 20260207_stripe_config.md
NOTE_dashboard_fix_write_crawl.md → 20260208_dashboard_fix_write_crawl.md
NOTE_2fa_skip.md              → 20260207_2fa_skip.md
NOTE_encrypted_payload.md     → 20260207_encrypted_payload.md
NOTE_infra_i18n_migrations_auth.md → 20260208_infra_i18n_migrations_auth.md
```

The targets were reorganised into dated subdirectories. Either update each symlink to the
new path or replace them with relative `../notes/YYYYMMDD/filename.md` symlinks.

### ✅ P1-4 · MessagesController namespace — verify or remove stale KNOWN_ISSUES flag [SOLVED 2026-05-01]

**Agents reporting:** codex (stale flag noted), gemini, open-claude

`MessagesController.php` exists at `app/Http/Controllers/Contact/MessagesController.php`
under classmap autoloading (`App\Http\Controllers` — PSR-4 violation but loaded). Run:

```bash
php artisan route:list 2>&1 | grep -c ERROR
```

If it returns 0, the KNOWN_ISSUES entry is stale and should be marked resolved.

---

## P2 — Medium (quality / correctness, does not block CI)

### ✅ P2-1 · ESLint scope too wide

**Resolved** – commit `787b0e403` (2026-05-01)

**Agents reporting:** claude, open-claude

`eslint.config.mjs` currently scans `ts/`, `.backup/`, `public/`, `Modules/` → ~77K
false-positive errors on legacy files.

**Fix:** Add ignore globs to `eslint.config.mjs`:

```js
ignores: [
  "ts/**",
  ".backup/**",
  "public/**",
  "Modules/**",
  "node_modules/**",
  "vendor/**",
];
```

### ✅ P2-2 · Documentation tree drift (dual `.notes/` hierarchy) [SOLVED 2026-05-01]

**Resolved** — commits `7e0bf8a04`, `940b03a37` (2026-05-01)

**Agents reporting:** claude, open-claude

After the Apr 28 reorg, root `/.notes/` still contains live files (`KNOWN_ISSUES.md`,
`CURRENT_WORKING_ISSUES_WORK.md`, `NEXT_STEPS.md`, `TODO_LATER.MD`) that have NOT been
migrated to `_inc/laravel/.notes/`. Meanwhile `_inc/laravel/.notes/.llms/.guidelines/`
is the canonical tree.

**Plan:**

1. Copy/move root `.notes/KNOWN_ISSUES.md` → `_inc/laravel/.notes/KNOWN_ISSUES.md`.
2. Same for `CURRENT_WORKING_ISSUES_WORK.md`, `NEXT_STEPS.md`, `TODO_LATER.MD`.
3. Leave root symlinks or MOVED_README.md so other agents can find the new location.
4. Update `where-to-update-and-read.yml` to point exclusively to `_inc/laravel/.notes/`.

### ✅ P2-3 · phpunit.xml / .env.testing DB name inconsistency

**Resolved** — commit `c3e4088f2` (2026-05-01)

**Agents reporting:** codex

`phpunit.xml` now forces `DB_DATABASE=erp_brand_new_ideas_company_test` instead of the
live app DB and no longer forces obsolete `test` credentials. The isolated test schema
exists and is visible to the configured app DB user. `APP_ENV=testing` resolves to
`erp_brand_new_ideas_company_test`.

### ✅ P2-4 · KNOWN_ISSUES.md cosmetic fixes

**Resolved** — commit `3f78574d3` (2026-05-01)

- Date typo fixed: `2026-07-24` → `2026-04-24`.
- `RESOLVED_ISSUES.md` created at `.notes/RESOLVED_ISSUES.md` (moved from gitignored
  `.notes/.llms/.history/` path; both references in KNOWN_ISSUES.md updated).

### ✅ P2-5 · Stale README examples using forbidden `php artisan test`

**Resolved** — commit `3f78574d3` (2026-05-01)

All three language blocks (EN/ES/PT-BR) replaced with safe `composer run test:*` /
`vendor/bin/phpunit` entrypoints plus an explicit `⚠️ Never run php artisan test` warning.

### ✅ P2-6 · CI uses `|| true` masking real failures

**Resolved** — commit `3f78574d3` (2026-05-01)

- **Hard-fail** (removed `|| true`): `tsc`, Jest CJS, Jest TS, pytest — known-clean or
  primary test gates.
- **Advisory** (`continue-on-error: true` + comment): PHPStan, PHPCS, ESLint (scope fixed
  in P2-1), Flake8, mypy, `npm audit fix`, `pip install -r requirements.txt`.

---

## P3 — Deferred (strategic, low urgency)

### ✅ P3-1 · 1,097 route TS → IIFE production swap [SOLVED 2026-05-01]

TypeScript compilation artifacts now live at the public-asset path that Blade
`<script>` tags actually load (`public/assets/js/…`), per the design clarification:
client-side scripts can only resolve URLs under `public/`, so the working scenario
is "compiled artifacts end up in some subpath of `public/`". The earlier `ts/dist*/`
tree was a holding area while the conversion was in test phase.

Two-step deploy:

1. `bash ts/scripts/deploy-ts.sh` — `tsc --build` (incremental, clean) → 1,134 files
   stripped of `export {};` / vendor-libs imports → rsync'd from `ts/dist/` to
   `public/assets/js/` (commit `25f3cdabd`).
2. `node ts/scripts/esm-to-iife.cjs` — regenerated 1,101 routes fully IIFE-wrapped
   (`(function(){"use strict";…})()`), then `rsync ts/dist-iife/public/assets/js/
   → public/assets/js/` to fix 21+ files where the deploy script's narrow regex
   missed named exports (e.g. `journalEntries/shared/repeater-utils.js` had
   `export { JournalEntryRepeater };` which throws SyntaxError as a classic script)
   (commit `94eae2b5b`).

Final state of `public/assets/js/{routes,generic,pages}`: ZERO ESM markers.
Blade templates were already using `asset('assets/js/routes/…')` — no template
edits needed. The 4 OOP singletons (`erp-guard.js`, `erp-utils.js`,
`erp-bootstrap.js`, `index.js`) are deliberately preserved per the deploy
contract; they have a pre-existing ESM-leftover bug flagged for a separate
follow-up. The user pre-staged a backup at `.backup/{public,resources}/` before
the swap.

### ⚠️ P3-2 · 531-file 3-way merge (PHPStan annotations vs agent crash-prevention guards)

**Status: blocked / re-scoped** — 2026-05-01

Re-investigation found the original framing was stale: the four "guard" commits
(`cff71b6e7`, `85ea61c9d`, `69e3522c5`, `f31bf7c17`) are **already on `main`**, so
the 531-file PHPStan-vs-guards conflict no longer exists. The remaining
divergence between `main` and `agent-prestech` is 20 commits / ~10,110 files —
overwhelmingly compiled `ts/dist*/` output and chore/refactor noise.

The bug-fix commits worth potentially cherry-picking are:

| Commit       | Subject                                                                 |
| ------------ | ----------------------------------------------------------------------- |
| `810fbbf29`  | i18n locale bugs + 204 translation tests (Playwright 69 + Jest 135)     |
| `fbe353d65`  | resolve expense create page failures (3 bugs)                           |
| `3c8d79846`  | missing public consts + snake_case method renames                       |
| `11b9690b3`  | DealController `ModelNotFoundException → 500` fixed                     |
| `2af327294`  | resolve all 9 PHPStan level-5 errors                                    |
| `66cafc92b`  | 3 orphan ProjectController routes; 35 missing consts; 12× 404 fixes     |

Attempted `git cherry-pick 11b9690b3` produced two conflicts:

1. `_inc/laravel/app/Http/Controllers/Activity/DealController.php` — content
   conflict against `main`'s PHPStan annotations.
2. `notes/KNOWN_ISSUES.md` — `modify/delete`; the file was moved to
   `_inc/laravel/.notes/` in P2-2, so the right resolution is `git rm`.

The leftover unresolved-merge state was cleaned up in commit `94eae2b5b`. A full
sweep through the 6 commits above will need a dedicated merge session with
PHPStan + PHPUnit running between cherry-picks; not attempted in this run.

### P3-3 · 2,832 TS-rollback file deletions — **dropped**

`agent-prestech` is treated as a read-only reference branch for old versions.
No salvage value beyond the cherry-pick candidates already enumerated under
P3-2, so no further review is planned.

### P3-4 · Dashboard N+1 query optimisation — **rejected**

User feedback: too development-oriented and likely to add noise vs. perceived
benefit on this MVP prototype. Not pursuing.

### ✅ P3-5 · Security deferrals (D1–D3) [SOLVED 2026-05-01]

| ID  | Issue                                                                       | Resolution                                                                                  | Commit       |
| --- | --------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------- | ------------ |
| D1  | Cross-controller IDOR / tenant scoping                                      | `app/Models/Scopes/CreatedByScope.php` helper created — admin/SA bypass built-in            | `3be5ca5a5`  |
| D2  | Shared-link passwords stored as base64                                      | Already green — `ProjectController::1818` uses `Hash::make`; `Project` model has `'hashed'` | n/a          |
| D3  | `JobController::jobApplyData()` login ambiguity on potential guest endpoint | `throttle:10,1` on POST route; dead `Auth::user()` removed; mime+size validation added      | `3be5ca5a5`  |

D1 helper applies as `static::addGlobalScope(new CreatedByScope())` in a
model's `booted()`, with `withoutGlobalScope(CreatedByScope::class)` as the
admin-context bypass. D2 was a pre-emptive fix from a prior session; verified
no base64 password storage remains on shared-link paths. D3 narrows the
public job-application form to 10 req/min/IP with file-type whitelist:
profile (jpeg/jpg/png/webp ≤5 MB) and resume (pdf/doc/docx ≤10 MB).

### ✅ P3-6 · Drop stale `erp_prestech_db_test_*` snapshots [SOLVED 2026-05-01]

User granted explicit approval. All 19 snapshots dropped via `sudo mysql`:

| Database                       | Tables (pre-drop) | Status   |
| ------------------------------ | ----------------: | -------- |
| `erp_prestech`                 |                21 | dropped  |
| `erp_prestech_test`            |                94 | dropped  |
| `erp_prestech_db`              |                 0 | dropped  |
| `erp_prestech_db_test_1`       |                 0 | dropped  |
| `erp_prestech_db_test_2`       |               209 | dropped  |
| `erp_prestech_db_test_3`       |                91 | dropped  |
| `erp_prestech_db_test_4`       |                42 | dropped  |
| `erp_prestech_db_test_6`       |               120 | dropped  |
| `erp_prestech_db_test_7`       |               144 | dropped  |
| `erp_prestech_db_test_8`       |                 0 | dropped  |
| `erp_prestech_db_test_9`       |               209 | dropped  |
| `erp_prestech_db_test_{10–16}` |       209 each ×7 | dropped  |
| `erp_prestech_db_test_99`      |                 0 | dropped  |

Final verification: `SHOW DATABASES LIKE '%prestech%'` → empty. Live
`erp_brand_new_ideas_company_db` unchanged at 229 tables. `/tmp/rename_*`
artifacts also cleaned up.

---

## Already Resolved (✅)

| Item                                                                                            | Commit                   | Date       |
| ----------------------------------------------------------------------------------------------- | ------------------------ | ---------- |
| P0-1 · DB rename target verified healthy: 229 tables, critical tables present, no DB brand hits | `f0520dd8d`              | 2026-05-01 |
| P0-2 · BillProduct namespace `App\Models` → `App\Models\Bills`; callers updated                 | `c28f9474e`              | 2026-05-01 |
| P0-2 follow-up · stale BillProduct test references and tracked backup references updated         | `7f206bff3`              | 2026-05-01 |
| P1-1 · Brand rename committed (405 files); push pending — remote repo not yet on GitHub         | `c28f9474e`              | 2026-05-01 |
| P1-1 follow-up · active frontend ERPGo fallbacks and tracked storage pointer cleaned             | `da1cc6361`              | 2026-05-01 |
| P1-2 · rename_db.sh secrets removed, destructive guard added, `/tmp` rename litter cleaned       | `107567fb5`              | 2026-05-01 |
| P2-1 · ESLint scope narrowed through ignore globs                                                | `787b0e403`              | 2026-05-01 |
| P2-2 · Documentation tree moved to `_inc/laravel/.notes/`; scope map updated                     | `7e0bf8a04`, `940b03a37` | 2026-05-01 |
| P2-3 · PHPUnit forced DB aligned to isolated `erp_brand_new_ideas_company_test` schema           | `c3e4088f2`              | 2026-05-01 |
| P2-4 · KNOWN_ISSUES.md date typo fixed; RESOLVED_ISSUES.md created at `.notes/`                 | `3f78574d3`              | 2026-05-01 |
| P2-5 · README `php artisan test` examples replaced in all 3 language blocks                     | `3f78574d3`              | 2026-05-01 |
| P2-6 · CI `\|\| true` replaced: hard-fail on tsc/jest/pytest; advisory on static-analysis steps | `3f78574d3`              | 2026-05-01 |
| P3-1 · TS-compiled IIFE swap into `public/assets/js/` (1,134 deploy + 1,101 IIFE refresh)        | `25f3cdabd`, `94eae2b5b` | 2026-05-01 |
| P3-5 · D1 `CreatedByScope` helper; D3 `throttle:10,1` + dead-code removal + mime validation     | `3be5ca5a5`              | 2026-05-01 |
| P3-6 · 19 stale `erp_prestech*` snapshot DBs dropped after explicit user approval                | n/a (DDL only)           | 2026-05-01 |
| C5 · Postman collection rename staged as `renamed:`                                             | pre-commit               | 2026-05-01 |
| CompetenciesTest fillable assertion stale                                                       | `ac0da3f03`              | 2026-04-26 |
| CI Node 24 action version warnings                                                              | `99c867344`, `3957967e0` | 2026-04-26 |
| k8s-deploy.sh interactive prompt flow                                                           | `33f1b0bd3`              | 2026-04-26 |
| PHPUnit skip count 188→187                                                                      | `b8a252669`              | 2026-04-26 |

---

## Recommended Execution Order

```
✅ P0-1 (DB recovery verification) — done; stale snapshot drops still need explicit approval
✅ P0-2 (BillProduct namespace) — done
✅ P1-1 (brand rename committed) — done; push pending GitHub repo creation
✅ P1-2 (clean rename_db.sh creds + /tmp litter) — done
✅ P2-1 (ESLint scope) — done
✅ P2-2 (docs tree drift) — done
✅ P2-3 (phpunit.xml/.env.testing) — done
✅ P2-4/5/6 (doc + CI quality) — done

✅ P1-3 (broken symlinks) — done (commit f31bf7c1)
✅ P1-4 (MessagesController stale flag) — done (commit bea84b24)
P3-6 (drop stale DB snapshots) — approval pending; do not run without explicit sign-off

P3 — deferred, schedule when P0/P1/P2 are clear
```
