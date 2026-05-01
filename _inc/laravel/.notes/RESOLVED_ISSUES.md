# Resolved Issues Archive

> Issues that have been fully fixed and verified. Append new entries at the top.
> Format: `## [DATE] Issue title` + brief description + commit reference.

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
