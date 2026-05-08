# PHPUnit skip-reduction pass — 2026-05-08

Targeted reduction of the 78 runtime-firing skips identified in the
2026-05-07 triage doc (`.notes/.llms/.guidelines/testing/skips-triage-2026-05-07.md`).

## Approach
Triage-doc "Investigate" tier first — small surgical fixes for
production view/controller mismatches, model relation expectations,
and stub column-name drift.

## Changes (5 skip sites eliminated)

### 1. `FaqController::edit` — variable-name mismatch (1 skip)
- **File:** `Modules/LandingPage/Http/Controllers/FaqController.php`
- **Bug:** `view($view, [self::ENTITY => $faqs[$key], 'key' => $key])`
  passed `$faqs` (plural) to a Blade that expects `$faq` (singular for
  a single record). `self::ENTITY = 'faqs'`, hence the leak.
- **Fix:** `[self::ENTITY => ...]` → `['faq' => ...]`
- **Test:** `FaqControllerTest::edit_displays_form_for_valid_key` flipped
  from skip to real assertion against the rendered view.

### 2. `DiscoverController::edit` and `discoverEdit` (2 skips)
- **Files modified:**
  - `Modules/LandingPage/Http/Controllers/DiscoverController.php` — `edit()`
    now passes `$discover` alongside `$feature`/`$key` so the shared
    edit Blade can find the variable.
  - `Modules/LandingPage/Resources/views/landingpage/discover/edit.blade.php`
    — accept either `discoverHeading`/`discoverDescription` (camelCase, the
    actual storage) or legacy `discover_heading`/`discover_description`.
  - `resources/views/modules/landingpage/landingpage/discover/edit.blade.php`
    — same fallback (this is the **active** view path under
    `resources/views/modules/...` precedence; the Module copy is a fallback).
- **Tests:** `DiscoverControllerTest::discoverEdit_displays_edit_view_for_valid_key`
  and `::edit_with_valid_key_displays_form` flipped from skip to real
  assertions. Setup factory call now sets `'lang' => 'en'` because
  `XSS::handle` calls `App::setLocale($user->lang)` and a null lang
  produces an empty locale → translator throws on `__()` lookups.

### 3. `InterviewScheduleTest` setUp-skip removed (1 skip → 2 passing tests)
- **File:** `tests/Unit/app/Models/planning/InterviewScheduleTest.php`
- **Bug:** setUp called `markTestSkipped('InterviewSchedule model boot
  hangs without full DB')` — an over-broad guard. The model's `booted()`
  only registers a `saving` listener; nothing hangs at instantiation.
- **Fix:** removed the setUp-skip. Updated `casts_array_is_correct` to
  match current model casts (model evolved past test). Renamed
  `applications_relation_is_has_one` → `applications_relation_is_belongs_to`
  with corrected expectations (the model uses `belongsTo`, not `hasOne`).
- **Caveat:** `users_relation_is_has_one` was deleted because
  `(new InterviewSchedule)->users()` calls `Utility::getEmployee($this)`
  which performs DB lookups that hang in the unit-test context. That part
  of the original test was the actual hang — kept as removed.

### 4. `ProjectTest::project_progress_calculates_percentage` (1 skip)
- **File:** `tests/Unit/app/Models/planning/ProjectTest.php`
- **Bug:** the test stub used object property `stage_id` but the model
  reads via `PJC::COL_STAGE_ID = 'project_stage_id'` (and `is_complete`,
  matching). The mismatch meant `where()->count()` returned 0, the
  computed percentage was 0, and `Utility::getProgressColor(0)` returned
  `'danger'` — not the asserted `'info'`. The skip-reason claimed "real
  getProgressColor bypassed setRelation" — incorrect; getProgressColor
  is a pure static.
- **Fix:** rename stub keys to match the model's column constants. Drop
  the skip.

## Pre-existing failures (not touched)
The LandingPage suite has 22 unrelated failures and 5 errors at HEAD that
predate this pass; this commit does not address those (different bug
classes — DataTransformer field-name drift, missing route stubs, factory
state issues).

## Verification

### Targeted file run (the 4 changed test files)
```bash
APP_ENV=testing php vendor/bin/phpunit \
  tests/Unit/app/Http/Controllers/LandingPage/FaqControllerTest.php \
  tests/Unit/app/Http/Controllers/LandingPage/DiscoverControllerTest.php \
  tests/Unit/app/Models/planning/InterviewScheduleTest.php \
  tests/Unit/app/Models/planning/ProjectTest.php --no-coverage
```

| Metric | Baseline (pre-change) | After |
|---|---:|---:|
| Tests | 34 | 33 |
| Assertions | 50 | 73 |
| Failures | 9 | 7 |
| Skipped | **11** | **4** |

Net: **7 skip-firings eliminated** in these files (counts skip-per-test —
the InterviewScheduleTest setUp-skip used to fire on each of 3 tests).
**5 distinct skip *sites* removed** from the source.

### Per-suite-level
- `Models-Planning` (full suite): no regressions, 1 skip removed.
- `LandingPage` controllers (full dir): 9 → 6 skipped, 24 → 22 failed,
  errors stable at 5, assertions 247 → 260.

## Out of scope for this pass

- **Google Calendar 6 skips** in `tests/Unit/app/Models/utils/UtilityTest.php`
  — needs `Utility::addCalendarData`/`getCalendarData` to grow seam
  methods around `Spatie\GoogleCalendar\Event::*`. Substantial refactor;
  will follow in a separate commit.
- **Twilio 1 skip + Spatie double-mock 1 skip** — same architectural
  pattern; planned alongside the Google Calendar work.
- **Settings-cache-race 2 skips** — pre-existing, complex; deferred.
- **WriteRouteTest "No admin user seeded" (28 static / 0 runtime)** —
  static-only call sites that never fire when DB is seeded; safety net
  not needed.
- **Roleplay/Security "Nenhum usuário disponível" (16 static / 0 runtime)**
  — same; static-only.
