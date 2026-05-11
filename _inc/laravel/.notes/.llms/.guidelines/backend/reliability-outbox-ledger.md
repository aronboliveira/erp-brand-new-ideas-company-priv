# Reliability Outbox and Operation Ledger

> Last updated: 2026-05-11. Applies to high-impact business operations across
> finance, HR, planning, products/warehouse, and heavy system workflows.

## Purpose

The project now has a generic reliability foundation for operations that need
more than normal Laravel logs:

- `operation_ledgers` — durable manager record for a high-impact operation.
- `operation_steps` — ordered state-machine/step log before and during commit.
- `outbox_messages` — durable publish-after-commit intent.
- `inbox_messages` — durable receive-side idempotency guard.
- `operational_events` — durable operational event timeline for medium+ work.
- `circuit_breaker_states` — durable state for guarded critical call paths.
- `circuit_breaker_calls` — sliding-window call history for circuit decisions.
- `operation_quarantines` — overlay records for extreme post-write validation
  failures that must block downstream use.
- `operation_quarantine_audits` — append-only quarantine decision timeline.

These tables are not finance-only. Finance postings are the first integration
because they are mission-critical, but employee status decisions, irreversible
project closure/deletion, warehouse commits, payroll, external imports, and
long-running I/O operations should use the same policy when their business
impact justifies it.

## Severity policy

Use `App\Services\Reliability\ReliabilityPolicy`:

| Criticality | Default storage | Operation ledger | Typical usage |
| --- | --- | --- | --- |
| `trivial` | memory | no | minor UI previews, harmless toggles |
| `low` | memory | no | lightweight customization, non-critical CRUD |
| `medium` | database | yes | business workflow with recoverable impact |
| `high` | database | yes | employee/project/warehouse decisions |
| `critical` | database | yes | finance posting, payroll, irreversible commits |

Criticality should rise with the SQL transaction isolation level when the
underlying procedure supports it:

- `critical` → `SERIALIZABLE`
- `high` → `REPEATABLE READ`
- `medium` → `READ COMMITTED`
- `low` / `trivial` → no forced isolation

## Usage pattern

Wrap critical business work in `CriticalOperationService::run()` and pass:

- `domain` such as `finance`, `hrm`, `planning`, `products`, or `system`
- `criticality`
- `subject_type` and `subject_id`
- `summary`
- optional `outbox` with `stream`, `event_type`, and `payload`

For low-impact work, prefer `OperationalEventService` or `OutboxService` with
`criticality => low`; this keeps the event in memory and avoids DB bloat.

## Retry and circuit breaker guards

Use builder APIs when a critical operation calls a fragile local integration,
shell adapter, webhook, or other expensive/externally influenced path:

```php
Retry::builder('finance.outbox.dispatch.payment.created')
    ->maxAttempts(3)
    ->retryOn(RuntimeException::class)
    ->abortOn(CircuitBreakerOpenException::class)
    ->intervalUsing(fn(int $attempt): int => ReliabilityPolicy::retryDelaySeconds($attempt))
    ->criticality(ReliabilityPolicy::CRITICALITY_HIGH)
    ->channel('finance.outbox')
    ->build()
    ->run(fn(): mixed => $guardedWork());
```

```php
CircuitBreaker::builder('finance.outbox.payment.created')
    ->slidingWindowSize(20)
    ->slidingWindowSeconds(300)
    ->failureRateThreshold(50.0)
    ->minimumCalls(5)
    ->openStateDurationSeconds(60)
    ->halfOpenAllowedCalls(3)
    ->halfOpenConservative(true)
    ->criticality(ReliabilityPolicy::CRITICALITY_CRITICAL)
    ->channel('finance.outbox')
    ->build()
    ->call(fn(): mixed => $guardedWork());
```

Retry emits `reliability.retry.success`, `reliability.retry.retrying`, and
`reliability.retry.failed`. Circuit breaker emits
`reliability.circuit.state_changed`, `reliability.circuit.opened`, and
`reliability.circuit.rejected`.

Retry intervals default to capped exponential backoff through
`ReliabilityPolicy::retryDelaySeconds()`: 30s, 60s, 120s, 240s, then 300s.
Use `intervalUsing()` only when a specific guarded call needs a different
cadence; `onInterval()` receives the computed delay so a caller can log,
schedule, or notify without duplicating the policy.

Circuit breakers are disabled by default for `trivial` and `low` criticality
so lightweight UI/customization work does not pay durable tracking overhead.
`medium` and above persist state/calls. High and critical builders default to
conservative half-open behavior; percentage half-open mode is available for
lower-severity cases, with the threshold clamped to at least 25%.

## Finance dispatcher slice

The first functional outbox dispatch slice is finance-only and monolith-local.
There is no broker requirement yet.

- `FinanceOperationService` wraps finance mutations and derives the outbox
  message key/payload after the DB callback has created the payment row.
- `FinanceOutboxDispatcher` processes `finance.ledger` outbox rows after
  commit and records accepted internal signals for journal control, banking
  API shells, communication API shells, ledger reversal review, and webhook
  shells.
- Each finance outbox signal is guarded by `Retry` and `CircuitBreaker` before
  the durable outbox row is marked processed, failed, or dead-lettered.
- Every finance transaction is retry-eligible. Retry attempts scale through
  `FinanceReliabilityPolicy`: basic amounts start at 2 attempts, `>= 3,200`
  starts at 3, `>= 25,000` at 4, `>= 100,000` at 5, `>= 250,000` at 6, and
  `>= 1,000,000` at 8, with small increases for reversals and persistent
  instability signals. High-risk metadata such as gateway/external origin,
  approval requirement, privileged actor, high user risk score, or payroll/tax/
  transfer/reversal direction can also increase attempts. Do not copy this rule
  to non-finance CRUD; those modules may still skip retries when the business
  impact is low.
- `DispatchFinanceOutboxCommand` exposes the same flow through
  `php artisan reliability:dispatch-finance-outbox`.
- Invoice and bill payment create/delete controller actions now use the
  finance operation wrapper, dispatch their local finance outbox immediately
  after commit, and flash a `reliability_operation` payload for client-side
  progress feedback.
- `OperationStatusController` serves the status payload through the named route
  `reliability.operations.show`; the app route pluralizer renders the URI as
  `reliabilities/operations/{operation}`.

This is still a monolithic callback/signal flow. Future queue, Redis, database
queue, or stream/broker adoption should keep the same outbox table as the
commit boundary and only replace how pending rows are drained.

## HRM dispatcher slice

The first HRM adoption now covers the highest-impact employee administration
paths without treating every HR screen as quarantine-worthy:

- `HrmOperationService` wraps HRM mutations and records durable operation
  ledgers, post-write validation steps, and `hrm.operations` outbox messages.
- `SetSalaryController::employeeSalaryUpdate()` uses the HRM wrapper for
  salary/payroll mutations. The policy classifies this as `payroll`, keeps it
  critical, validates the persisted employee salary/salary type, and emits
  payroll/finance-bridge/communication/webhook signals through the dispatcher.
- `TerminationController::store/update/destroy()` uses the HRM wrapper for
  employee lifecycle decisions. The validator checks the persisted termination,
  employee link, dates, and delete result before outbox creation.
- `LeaveController::changeAction()` uses the HRM wrapper for leave decisions.
  The validator checks the leave record, employee link, dates, total days, and
  non-empty status, but leave decisions do not reach quarantine unless a future
  policy explicitly promotes a specific leave invariant to lifecycle severity.
- `HrmOutboxDispatcher` drains `hrm.operations` rows through monolith-local
  callbacks for employee record projections, payroll shells, finance payroll
  bridge shells, access-control/RBAC reconciliation shells, calendar shells,
  communication APIs, and webhooks.
- `php artisan reliability:dispatch-hrm-outbox` exposes the same dispatcher
  without requiring Redis, database queues, Kafka, or another broker.

HRM post-write validation deliberately accepts an employee with no linked user:
`employees.user_id` is nullable and an employee can exist without login access.
Validation only fails the identity/access invariant when a non-null linked user
is missing or mismatched, such as wrong user type, creator mismatch, email
mismatch, or missing `Employee` role when that role exists in the RBAC tables.

HRM retry/circuit behavior follows the business cluster:

- `payroll` and `employee_lifecycle` are critical and get durable retry plus
  circuit breaker protection.
- `identity_access` is high by default and becomes critical for delete/final
  access-affecting operations or persistent instability.
- `hr_decision` is medium/high depending on the action and gets ledgers/outbox
  when state-changing.
- `configuration` avoids retry/circuit overhead unless explicitly forced.

## Warehouse/products dispatcher slice

Warehouse/products reliability is broader than quarantine but still follows the
overhead discipline: routine metadata screens should not pay durable guard costs
unless the business effect is material. The current slice covers stock-changing
or stock-defining paths:

- `WarehouseOperationService` wraps warehouse mutations and records durable
  ledgers, post-write validation steps, and `warehouse.operations` outbox
  messages.
- `ProductStockController::store/update/destroy()` covers manual stock
  adjustments and product stock deletion.
- `ProductServiceController::store/update/destroy/import()` covers decisive
  product/service catalog facts such as SKU, quantity, sale/purchase price, tax,
  unit, category, product type, bulk imports, and product deletion.
- `WarehouseTransferController::store/destroy/update()` covers transfer commit,
  reversal, and metadata/status updates. Direct edits to source/destination
  warehouse, product, or quantity are blocked because they would require a new
  controlled stock movement rather than a metadata update.
- `WarehouseController::destroy()` blocks direct warehouse deletion when stock
  rows or transfer references still exist, then records the warehouse lifecycle
  operation when deletion is valid.
- `PurchaseController::store/update/destroy/productDestroy()` covers purchase
  stock commits, full purchase reversal, and individual purchase-line deletion.
- `PosController::store()` covers final POS stock consumption.
- `WarehouseOutboxDispatcher` drains `warehouse.operations` rows through
  monolith-local inventory signals: stock projection refresh, stock
  reconciliation, inventory replica-sync, warehouse transfer projection,
  logistics callback shells, valuation refresh, catalog replica-sync,
  finance purchase/POS bridges, supplier/customer stock projections, and
  webhooks.
- `php artisan reliability:dispatch-warehouse-outbox` exposes the same
  dispatcher without requiring Redis, database queues, Kafka, or another broker.

Warehouse policy clusters:

- `stock_mutation`, `warehouse_transfer`, `pos_commit`, `purchase_commit`, and
  `warehouse_lifecycle` are high by default, validate after write, and get
  retry/circuit protection.
- `bulk_import` and `catalog_value` become high when quantities, prices, SKU,
  tax, unit, category, type, chart accounts, or other decisive fields are
  touched.
- `routine_metadata` avoids retry/circuit overhead unless explicitly forced.

The policy explicitly tracks `replica_sync_sensitive` and
`eventual_consistency_sensitive` payload flags. They do not make a row
quarantine-worthy alone, but they keep committed stock changes visible to local
projection/replica/reconciliation shells and provide signal context when
persistent retry/circuit/dead-letter instability appears.

## CRM dispatcher slice

CRM reliability is intentionally more selective than finance or inventory:
many CRM rows are communications, notes, labels, files, dashboard payloads, or
other transient activity. The current slice covers durable business decisions:

- `CrmOperationService` wraps CRM mutations and records durable ledgers,
  post-write validation steps, and `crm.operations` outbox messages.
- `LeadController::store/update/destroy/order/convertToDeal()` now covers lead
  lifecycle, lead stage movement, and lead-to-deal conversion.
- `DealController::store/update/destroy/order/changeStatus()` now covers deal
  lifecycle, deal stage movement, and final/status-changing deal decisions.
- `CustomerController::store/update/destroy()`,
  `VendorController::store/update/destroy()`, and
  `ClientController::store/update/destroy()` now cover durable CRM-adjacent
  relationship identity records.
- `DealController::userUpdate/userDestroy/clientUpdate/clientDestroy()` and
  `permissionStore()` now cover deal user/client relationship and permission
  sub-actions.
- `CrmPostWriteValidator` validates lead/deal persistence, conversion,
  stage/status movement, customer/vendor/client identity rows, deal
  client/user links, client permissions, and product/source context when those
  event types are used.
- `CrmOutboxDispatcher` drains `crm.operations` rows through monolith-local
  projection, client projection, pipeline reconciliation, forecasting,
  access-projection, relationship projection, project bridge, finance
  opportunity/relationship bridge, communication, catalog-context, and webhook
  signal shells.
- `php artisan reliability:dispatch-crm-outbox` exposes the same dispatcher
  without requiring Redis, database queues, Kafka, or another broker.

CRM policy clusters:

- `lead_conversion`, `deal_status`, and `crm_access_assignment` validate after
  write and get retry/circuit protection because they affect downstream
  customer, finance, project, and access decisions.
- `deal_lifecycle` is medium/high by value and operation type; delete/final
  paths and high-value deals get stronger validation and retry settings.
- `stage_pipeline_movement` validates after write because stage movement is a
  durable business signal, especially for deals.
- `relationship_record` covers customer/vendor/client lifecycle rows. It gets
  retry/circuit/outbox coverage and post-write validation because those records
  feed finance, purchases, invoices, projects, stock reports, and access
  decisions. Routine contact/address edits still stay below quarantine unless
  persistent instability plus core identity/link corruption appears.
- `lead_lifecycle` is medium by default; critical leads and deletes validate,
  while routine lead edits stay lower overhead.
- `catalog_context`, `configuration`, and `transient_activity` avoid heavy
  guard costs unless a caller explicitly promotes the operation.

CRM quarantine remains rare. It requires persistent retry/circuit/dead-letter
or repeated failed-operation instability plus core corruption in conversion,
final deal status, deal lifecycle, CRM access state, or durable relationship
identity/link state. Simple notes, files, calls, emails, discussions, labels,
dashboards, and routine contact metadata should not route to quarantine by
default.

## Project planning dispatcher slice

Project planning is intentionally the lightest reliability slice so far. Most
planning activity is coordination metadata, not a financial/HR/stock commit, so
routine project edits, comments, files, checklist toggles, board ordering,
filters, and reporting views should not pay durable retry/circuit/outbox
overhead by default.

Current adoption:

- `PlanningOperationService` wraps selected project-planning mutations and
  records durable ledgers, post-write validation steps, and
  `planning.operations` outbox messages.
- `ProjectController::update()` uses the planning wrapper only when the project
  status moves into a final state such as `complete` or `canceled`.
- `ProjectController::destroy()` always uses the planning wrapper because
  project deletion cascades durable project state.
- `ProjectController::milestoneUpdate()` uses the planning wrapper for final
  milestone status/progress or elevated milestone cost.
- `ProjectController::milestoneDestroy()` uses the planning wrapper for
  irreversible milestone deletion.
- `ProjectTaskController::changeCom()` and final `changeProg()` paths use the
  planning wrapper for task completion/final progress decisions.
- `ProjectTaskController::destroy()` uses the wrapper only for completed/final
  tasks; routine non-final task deletion stays on the legacy lightweight path.
- `PlanningOutboxDispatcher` drains `planning.operations` rows through
  monolith-local projection, progress, schedule/calendar, archive/access,
  CRM/client bridge, finance project-context bridge, reporting,
  communication, and webhook signal shells.
- `php artisan reliability:dispatch-planning-outbox` exposes the same
  dispatcher without requiring Redis, database queues, Kafka, or another
  broker.

Planning policy clusters:

- `project_deletion` is critical because it is irreversible and can cascade
  tasks, milestones, users, files, timesheets, and reports.
- `project_final_status` is high by default and becomes critical for high
  budgets or persistent instability.
- `milestone_final_state` and `task_final_state` are medium/high depending on
  cost, final progress/status, and priority.
- `approval_finalization` is reserved for future timesheet/expense/final
  approval paths after those controllers are scanned separately.
- `routine_planning` avoids retry/circuit/outbox overhead unless explicitly
  promoted by a future business rule.

Planning quarantine is manual-review only and remains rare. It requires
persistent retry/circuit/dead-letter or repeated failed-ledger instability plus
core corruption in final project, milestone, or task state. A single bad
planning write should roll back or fail validation without quarantine.

## Post-write quarantine

Quarantine is intentionally narrower than the general reliability layer. It is
for extreme critical procedures where the database can accept a schema-valid but
business-invalid row, and where allowing the row to continue would be more
dangerous than the overhead of extra validation/audit writes.

Current production scope:

- invoice payment create/delete
- bill payment create/delete
- HRM salary/payroll, employee lifecycle, and identity/access post-write
  validation, with quarantine only for persistent instability in critical
  payroll/lifecycle/identity cases
- warehouse/product stock quantities, warehouse transfers, purchase/POS stock
  commits, bulk imports, and warehouse lifecycle rows, with quarantine only after
  persistent retry/circuit/dead-letter/failed-ledger or long-running instability
- CRM lead/deal lifecycle, deal access/permission changes, and durable
  customer/vendor/client relationship identity rows, with quarantine only after
  persistent instability plus core conversion/status/access/relationship
  corruption
- project planning finalization/deletion rows, with quarantine only after
  persistent instability plus core final project/milestone/task corruption

Finance paths opt into `FinanceOperationService` post-write validation through
`post_write_validation => true`, but that flag now delegates to
`FinanceReliabilityPolicy`; it does not mean "validate every payment." Post-write
validation starts at amount `3,200` or an explicit force flag. The validator
fetches the persisted payment and linked finance record after the write callback,
before outbox creation. It checks critical payment fields, bank account
references, payment ownership links, overpayment/imbalance, and payment-status
consistency.

If validation fails without persistence signals, the service throws
`FinancePostWriteValidationFailedException`, the domain transaction rolls back,
no outbox row is created, and no quarantine overlay is written. Quarantine is
reserved for repeated/stuck corrupted-data scenarios: multiple recent failed
operation ledgers, retry failures, dead letters, several finance circuit-breaker
state/rejection events, or a long-running unresolved operation. Only then can
`QuarantineRollbackRequiredException` route the failed validation to
`QuarantineService`, which writes the overlay quarantine record, audit entries,
and a critical operational event against the existing operation ledger. Source
finance rows are not marked directly; the overlay table is the canonical
quarantine signal for this slice.

HRM quarantine uses the same overlay tables and persistence rules but a distinct
domain/event channel (`hrm.quarantine.*`). A single HRM validation failure is not
enough: retry sets, circuit-breaker instability, dead letters, prior failed
operation ledgers, or long unresolved processing must show persistent
instability first. When HRM quarantine is triggered, the remediation judge sends
the source signal to `manual_review`, because employee/payroll/lifecycle state
usually needs a human decision before additional access or payroll actions are
allowed.

Warehouse quarantine also uses the shared overlay tables with domain-specific
manual-review routing. A single stock mismatch or validation failure is not
enough. The row must be in a high-impact warehouse cluster and show persistence
signals first, such as repeated failed ledgers, retry failures, dead letters,
several warehouse circuit-breaker events, or extreme unresolved processing time.

Do not enable quarantine for routine CRUD, lightweight customization, ordinary
imports, or non-critical stage movement. Use operation ledgers, outbox, retry, or
normal validation first. Add quarantine only when a specific business invariant
justifies the DB reads, audit writes, and downstream blocking.

## Rollback and compensation

Two rollback surfaces are now defined:

- Failures inside `CriticalOperationService::run()` happen before the DB commit
  completes, so Laravel rolls back the mutation and the operation ledger is
  marked `failed`.
- Failures after commit, during finance/HRM/warehouse outbox dispatch, cannot
  undo the original SQL commit. The dispatcher first runs in-process
  retry/circuit guards, then applies durable outbox retry scheduling; once
  attempts are exhausted it marks the outbox `dead_letter`, records a
  `compensation.required:*` step, emits a domain-specific compensation-required
  event, and moves the operation ledger to `compensating`.
- HRM post-commit dispatch follows the same durable pattern and emits
  `hrm.compensation.required` when HRM outbox retries are exhausted.
- Warehouse post-commit dispatch follows the same durable pattern and emits
  `warehouse.compensation.required` when warehouse outbox retries are exhausted.
- CRM post-commit dispatch follows the same durable pattern and emits
  `crm.compensation.required` when CRM outbox retries are exhausted.
- Planning post-commit dispatch follows the same durable pattern and emits
  `planning.compensation.required` when planning outbox retries are exhausted.

Actual domain reversal remains a later, domain-specific implementation. The
important current guarantee is that post-commit signal failure becomes durable,
visible, and queryable instead of disappearing into normal Laravel logs.

## Retention

Every durable row should have an `expires_at`. `ReliabilityRetentionService`
currently prunes expired finished rows, circuit breaker calls, closed/disabled
circuit breaker states, resolved quarantine overlays, and can compress verbose
operational events for a single operation. Do not introduce unbounded
outbox/ledger/circuit/quarantine writes without a retention plan.

## Tests

Use focused PHPUnit tests under `tests/Unit/app/Services/Reliability/`.
Current baseline:

```bash
php vendor/bin/phpunit tests/Unit/app/Services/Reliability --no-coverage
```

Latest local reliability check after the project planning reliability slice:

```text
tests/Unit/app/Services/Reliability --no-coverage:
50 tests, 275 assertions, 0 errors, 0 failures.

Planning touched controller tests:
354 tests, 450 assertions, 0 errors, 0 failures.

composer phpstan:
No errors.
```

Do not run `php artisan test`; this project uses `vendor/bin/phpunit` directly.
