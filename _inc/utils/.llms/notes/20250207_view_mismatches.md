# View Naming Mismatches — 2025-02-07

## Summary

12 cases found where **controller `return view('...')` references** do NOT match
the **actual Blade file names** on disk. All controllers use the
`ViewFacade::exists()` guard, so these mismatches produce silent redirects
with an error flash message instead of rendering the intended page.

## Convention Conflict

- **Controllers** use camelCase or inconsistent naming: `bugCreate`, `bugEdit`, `lastLogin`
- **Blade files** use snake_case: `bug_create`, `bug_edit`, `last_login`

## Full Mismatch List

| #   | Controller Reference         | Actual Blade File                                                           | Controller         |
| --- | ---------------------------- | --------------------------------------------------------------------------- | ------------------ |
| 1   | `employees.lastLogin`        | `employees/last_login.blade.php`                                            | EmployeeController |
| 2   | `projects.milestones`        | `projects/milestone.blade.php`                                              | ProjectController  |
| 3   | `projects.milestones.edit`   | `projects/milestone_edit.blade.php`                                         | ProjectController  |
| 4   | `projects.milestones.show`   | `projects/milestone_show.blade.php`                                         | ProjectController  |
| 5   | `projects.bugCreate`         | `projects/bug_create.blade.php`                                             | ProjectController  |
| 6   | `projects.bugEdit`           | `projects/bug_edit.blade.php`                                               | ProjectController  |
| 7   | `projects.bugKanban`         | `projects/bug_kanban.blade.php`                                             | ProjectController  |
| 8   | `projects.bugShow`           | `projects/bug_show.blade.php`                                               | ProjectController  |
| 9   | `projects.copylink_setting`  | `projects/copy_link_setting.blade.php`                                      | ProjectController  |
| 10  | `projects.copylink_password` | `projects/copy_link_password.blade.php`                                     | ProjectController  |
| 11  | `projects.copylink`          | `projects/copy_link.blade.php`                                              | ProjectController  |
| 12  | `customers.invoice_send`     | **File does not exist** (closest: `emails/invoice/customer_send.blade.php`) | InvoiceController  |

## Impact

These mismatches cause:

- `ViewFacade::exists()` returns false → controller redirects with error flash
- The intended page **never renders** for end users
- HTTP response is 302 (redirect) instead of 200 with rendered view
- No error in logs unless exception handler is watching for it

## Recommended Fix

For mismatches 1-11: Either rename the Blade file or update the controller's
`return view()` call. Renaming the controller reference is usually safer
since other code may already reference the existing Blade filename.

For mismatch 12: The view `customers.invoice_send` needs to be created, or
the controller should reference `emails.invoice.customer_send` instead.

## Test Coverage

All 12 mismatches are documented in PHPUnit tests:

- `tests/Unit/app/Http/Controllers/views/ProjectControllerViewTest.php` — 10 mismatches via `@dataProvider`
- `tests/Unit/app/Http/Controllers/views/EmployeeControllerViewTest.php` — 1 mismatch
- `tests/Unit/app/Http/Controllers/views/InvoiceControllerViewTest.php` — 1 mismatch
