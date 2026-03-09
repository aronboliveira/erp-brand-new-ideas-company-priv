# 07 — KNOWN BUGS AND RECURRING PATTERNS

## Pattern 1: SA type not handled (most common bug)

The original ERPGo code was designed for multi-tenant SaaS where `company` is
the owner. Our deployment has a single `super admin` user. The codebase checks
`$userType === PMC::CPN` or `$userType !== PMC::CPN` in ~50+ places.

**Problem:** SA user falls through to employee/client branches, which use
`find_in_set($userId, assigned_to)` — but since UUID → int = 0, it matches
nothing.

**Fix pattern:**

```php
// Wherever you see:
if ($userType === PMC::CPN) { /* owner logic */ }
// Change to:
if (in_array($userType, [PMC::CPN, PMC::SA], true)) { /* owner logic */ }

// Or negated:
if ($userType !== PMC::CPN && $userType !== PMC::SA) { /* non-owner logic */ }
```

### Files already fixed (Batch 14):

- `ProjectTaskController::taskBoardView()`

### Files fixed in stash (need review + commit):

- `DealController` (2 places)
- `PerformanceTypeController`
- `SupportController`
- `MessagesController`
- `ContractTypeController`
- `IndicatorController`
- `ProjectController` (multiple places)
- `ProjectReportController`
- `ProjectTaskController` (3 more places: allBugList, calendarView, getTaskData)
- `ResignationController`
- `DashboardController`
- `FormBuilderController`
- `TimesheetController`
- `DocumentUploadController`

### Files NOT yet checked:

- `InvoiceController`
- `BillController`
- `RevenueController`
- `PaymentController`
- `GoalController`
- `CustomerController`
- `VenderController`
- Most other controllers in `app/Http/Controllers/`

## Pattern 2: Blade views with @extends used as AJAX partials

Some views use `@extends('layouts.admin')` but are rendered via AJAX into
a `<div>`. This returns the entire HTML page instead of just the content.

**Fix:** Create a `*_partial.blade.php` without `@extends` for AJAX responses.

**Already fixed:**

- `grid.blade.php` → `grid_partial.blade.php` (task board grid)

**May need fixing:**

- Check any AJAX endpoint that renders a view with `@extends`

## Pattern 3: Route pluralization

`RouteServiceProvider::boot()` pluralizes all non-last URI segments:

```
task-board/{view?}  →  task-boards/{view?}
bug-report/{id}     →  bug-reports/{id}
```

This means route URLs in the browser differ from the route definition.
Always test with `route('name')` helper, not hardcoded URLs.

## Pattern 4: Empty $tasks / $projects on page load

Views often start with empty `#project_view` / `#taskboard_view` divs that
get populated by AJAX on `$(document).ready()`. Without spinners, users see
blank pages during loading.

**Fix:** Add Bootstrap spinners inside the target divs:

```html
<div id="taskboard_view">
  <div class="d-flex justify-content-center align-items-center py-5">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <span class="ms-2 text-muted">Loading tasks...</span>
  </div>
</div>
```

## Pattern 5: creatorId() returns own ID for SA

```php
$user->creatorId();
// For SA: returns $user->id (self-referential)
// For company: returns $user->id
// For employees: returns $user->created_by (the company/SA that created them)
```

When filtering by `created_by`, SA sees everything it created (= all entities).
