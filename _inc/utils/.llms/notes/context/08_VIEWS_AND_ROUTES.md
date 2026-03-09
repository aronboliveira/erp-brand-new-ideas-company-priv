# 08 — VIEWS AND ROUTES

## Route definitions

Routes are in `_inc/laravel/routes/web.php`.
Route names follow `module.action` convention.

### Key route names

| Route Name                | URL                    | Controller@Method                            |
| ------------------------- | ---------------------- | -------------------------------------------- |
| `dashboard`               | `/home`                | `DashboardController@index`                  |
| `login`                   | `/login`               | `AuthenticatedSessionController@create`      |
| `projects.index`          | `/projects`            | `ProjectController@index`                    |
| `filter.project.view`     | `/projects-view`       | `ProjectController@filterProjectView` (AJAX) |
| `taskboards.view`         | `/task-boards/{view?}` | `ProjectTaskController@taskBoard`            |
| `projects.taskboard.view` | `/task-board-view`     | `ProjectTaskController@taskBoardView` (AJAX) |
| `goals.index`             | `/goals`               | `GoalController@index`                       |
| `invoices.index`          | `/invoices`            | `InvoiceController@index`                    |
| `bills.index`             | `/bills`               | `BillController@index`                       |
| `customers.index`         | `/customers`           | `CustomerController@index`                   |
| `venders.index`           | `/vendors`             | `VenderController@index`                     |
| `account-statement.index` | `/account-statement`   | `AccountStatementController@index`           |

### Route pluralization (RouteServiceProvider)

The `RouteServiceProvider::boot()` method pluralizes non-last URI segments:

```
task-board/{view?}  →  task-boards/{view?}   (browser URL)
bug-report/{id}     →  bug-reports/{id}
```

Always use `route('name')` helper in PHP/Blade, never hardcode URLs.

## View file locations

```
resources/views/
├── layouts/
│   ├── admin.blade.php           ← main admin layout (@extends target)
│   ├── auth.blade.php            ← login/register layout
│   └── landing.blade.php         ← public landing page layout
├── dashboard/
│   └── index.blade.php           ← dashboard
├── projects/
│   ├── index.blade.php           ← projects list page (has #project_view + AJAX)
│   ├── grid.blade.php            ← AJAX partial for project grid cards
│   ├── list.blade.php            ← AJAX partial for project list table
│   └── show.blade.php            ← single project view
├── project_tasks/
│   ├── taskboard.blade.php       ← shell for task board (list/grid toggle + #taskboard_view)
│   ├── list.blade.php            ← AJAX partial for task list table
│   ├── grid.blade.php            ← FULL PAGE grid view (has @extends — NOT for AJAX)
│   ├── grid_partial.blade.php    ← AJAX partial for grid cards (NEW, created in Batch 14)
│   ├── create.blade.php          ← task creation form
│   └── edit.blade.php            ← task edit form
├── goals/
│   └── index.blade.php
├── invoices/
│   └── index.blade.php
└── ... (many more)
```

## AJAX pattern (projects and taskboard)

Both projects and taskboard use the same AJAX loading pattern:

1. Shell view renders with empty `<div id="xxx_view">` + spinner
2. On `$(document).ready()`, JS calls AJAX endpoint
3. AJAX endpoint renders a partial (no `@extends`) → returns HTML
4. JS replaces div content: `$('#xxx_view').html(res.html ?? res)`

### Projects AJAX:

```
Shell:    projects/index.blade.php → has #project_view div
AJAX:     GET /projects-view?view=grid&status=...
Partial:  projects/grid.blade.php or projects/list.blade.php
```

### Taskboard AJAX:

```
Shell:    project_tasks/taskboard.blade.php → has #taskboard_view div
AJAX:     GET /task-board-view?view=list&sort=...
Partial:  project_tasks/list.blade.php or project_tasks/grid_partial.blade.php
```

## Blade conventions

- Admin views: `@extends(ExtendingLayoutsConstants::ADM)` = `layouts.admin`
- Content section: `@section('content')` or `@section(ADM_CTT)` (alias)
- Translation: `{{ __('key') }}` — files in `resources/lang/{locale}/`
- CSRF: `@csrf` in forms, `$.ajaxSetup({ headers: {'X-CSRF-TOKEN': ...} })`
